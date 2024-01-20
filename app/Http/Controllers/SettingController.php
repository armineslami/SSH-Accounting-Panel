<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInboundRequest;
use App\Http\Requests\DropboxCallbackRequest;
use App\Http\Requests\DropboxLinkRequest;
use App\Http\Requests\ImportBackupRequest;
use App\Http\Requests\UpdateInboundSettingsRequest;
use App\Http\Requests\UpdateTelegramSettingsRequest;
use App\Repositories\InboundRepository;
use App\Repositories\SettingRepository;
use App\Utils\Utils;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Telegram\Bot\Laravel\Facades\Telegram;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = SettingRepository::first();
        return view('settings.edit', ['settings' => $settings]);
    }

    public function updateInbound(UpdateInboundSettingsRequest $request): RedirectResponse
    {
        SettingRepository::update(SettingRepository::first(), $request->validated());

        return Redirect::route('settings.edit')->with('status', 'settings-updated');
    }

    public function updateTelegram(UpdateTelegramSettingsRequest $request): RedirectResponse
    {
        $request->validated();

        $host = "https://" . $request->getHost();
        $token = $request->bot_token;
        $port = $request->bot_port;

        config(['telegram.bots.sap.token' => $token]);

        $url = env("APP_ENV", "") === "local" ?
            env("TELEGRAM_WEBHOOK_ADDRESS") . "/api/<token>/webhook" :
            $host . ":" . $port . "/api/<token>/webhook";

        $isWebhookSet = false;
        $error = null;

        if ($token && $port) {
            try {
                $isWebhookSet = Telegram::setWebhook([
                    "url" => $url
//           "certificate" => "/etc/letsencrypt/live/sap/fullchain.pem"
                ]);
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), "Timeout was reached")) {
                    $error = "Timeout was reached. Make sure telegram is not banned in your server region.";
                }
            }

            if (!$isWebhookSet) {
                return Redirect::route('settings.edit')->with([
                    'status' => 'telegram-not-updated',
                    'message' => $error
                ]);
            }
        }

        SettingRepository::update(SettingRepository::first(), $request->validated());

        return Redirect::route('settings.edit')->with('status', 'telegram-updated');
    }

    public function linkDropbox(DropboxLinkRequest $request): RedirectResponse
    {
        $request->validated();

        //Configure Dropbox Application
        $app = new DropboxApp($request->dropbox_client_id, $request->dropbox_client_secret);

        //Configure Dropbox service
        try {
            $dropbox = new Dropbox($app);
        } catch (DropboxClientException $e) {
            $message = json_decode($e->getMessage(), true);
            $error = $message['error_description'] ?? ($message["error"] ?? $e->getMessage());
            return Redirect::route('settings.edit')->with([
                "status" => "dropbox-not-linked",
                "message" => $error
            ]);
        }

        $authHelper = $dropbox->getAuthHelper();

        //Callback URL
        $callbackUrl = route("settings.dropbox.callback");

        // Additional user provided parameters to pass in the request
        $params = [];

        // Url State - Additional User provided state data
        $urlState = $request->dropbox_client_id . "|" . $request->dropbox_client_secret;

        // Token Access Type
        $tokenAccessType = "offline";

        //Fetch the Authorization/Login URL
        $authUrl = $authHelper->getAuthUrl($callbackUrl, $params, $urlState, $tokenAccessType);

        return redirect($authUrl);
    }

    public function callbackDropbox(DropboxCallbackRequest $request): RedirectResponse
    {
        $request->validated();

        $code   = $request->code;
        $state  = $request->state;

        $states = $this->decodeDropboxState($state);
        $csrfToken = $states['csrf_token'];
        $clientId = $states['client_id'];
        $clientSecret = $states['client_secret'];

        // Configure Dropbox Application
        $app = new DropboxApp(clientId: $clientId, clientSecret: $clientSecret);

        // Configure Dropbox service
        try {
            $dropbox = new Dropbox($app);
        } catch (DropboxClientException $e) {
            $message = json_decode($e->getMessage(), true);
            $error = $message['error_description'] ?? ($message["error"] ?? $e->getMessage());
            return Redirect::route('settings.edit')->with([
                "status" => "dropbox-not-linked",
                "message" => $error
            ]);
        }

        $authHelper = $dropbox->getAuthHelper();
        $authHelper->getPersistentDataStore()->set('state', $csrfToken);
        $callbackUrl = route("settings.dropbox.callback");

        // Fetch the AccessToken
        try {
            $accessToken = $authHelper->getAccessToken($code, $state, $callbackUrl);
        } catch (DropboxClientException $e) {
            $message = json_decode($e->getMessage(), true);
            $error = $message['error_description'] ?? ($message["error"] ?? $e->getMessage());
            return Redirect::route('settings.edit')->with([
                "status" => "dropbox-not-linked",
                "message" => $error
            ]);
        }

        SettingRepository::update(SettingRepository::first(), [
            "dropbox_client_id" => $clientId,
            "dropbox_client_secret" => $clientSecret,
            "dropbox_token" => $accessToken->getToken(),
            "dropbox_refresh_token" => $accessToken->getRefreshToken(),
            "dropbox_token_expire_date" => Carbon::now()->toDateTimeString()
        ]);

        return Redirect::route('settings.edit')->with('status', 'dropbox-linked');
    }

    public function unlinkDropbox(): RedirectResponse
    {
        $settings = SettingRepository::first();
        $clientId = $settings->dropbox_client_id;
        $clientSecret = $settings->dropbox_client_secret;
        $dropboxToken = $settings->dropbox_token;
        $tokenExpireTime = Carbon::parse($settings->dropbox_token_expire_date);

        // Dropbox expires tokens every 4 hours. To be sure nothing goes wrong and since
        //expire time is not accurate, refresh the token every 3 hours.
        if (Carbon::now()->diffInHours($tokenExpireTime) >= 3) {
            $newAccessToken = Utils::refreshDropboxToken(
                clientId: $settings->dropbox_client_id,
                clientSecret: $settings->dropbox_client_secret,
                refreshToken: $settings->dropbox_refresh_token
            );

            if (!$newAccessToken) {
                return Redirect::route('settings.edit')->with('status','dropbox-not-unlinked');
            }

            SettingRepository::update(SettingRepository::first(), [
                "dropbox_token" => $newAccessToken->getToken(),
                "dropbox_refresh_token" => $newAccessToken->getRefreshToken(),
                "dropbox_token_expire_date" => Carbon::now()->toDateTimeString()
            ]);

            $dropboxToken = $newAccessToken->getToken();
        }

        // Configure Dropbox Application
        $app = new DropboxApp($clientId, $clientSecret, $dropboxToken);

        // Configure Dropbox service
        try {
            $dropbox = new Dropbox($app);

        } catch (DropboxClientException $e) {
            $message = json_decode($e->getMessage(), true);
            $error = $message['error']['.tag'] ?: ($message['error'] ?? $e->getMessage());
            return Redirect::route('settings.edit')->with([
                'status' => 'dropbox-not-unlinked',
                'message' => $error
            ]);
        }

        // DropboxAuthHelper
        $authHelper = $dropbox->getAuthHelper();

        // Revoke the access
        try {
            $authHelper->revokeAccessToken();
        } catch (DropboxClientException $e) {
            $message = json_decode($e->getMessage(), true);
            $error = $message['error']['.tag'] ?: $message['error'];
            return Redirect::route('settings.edit')->with([
                'status' => 'dropbox-not-unlinked',
                'message' => $error
            ]);
        }

        SettingRepository::update(SettingRepository::first(), [
            "dropbox_client_id" => null,
            "dropbox_client_secret" => null,
            "dropbox_token" => null,
            "dropbox_refresh_token" => null,
            "dropbox_token_expire_date" => null
        ]);

        return Redirect::route('settings.edit')->with('status', 'dropbox-unlinked');
    }

    private function decodeDropboxState(string $state): array
    {
        $csrfToken = null;
        $client_id = null;
        $client_secret = null;

        $splitPos = strpos($state, "|");

        if ($splitPos !== false) {
            $csrfToken = substr($state, 0, $splitPos);
            $urlState = substr($state, $splitPos + 1);

            $splitPos = strpos($urlState, "|");

            $client_id = substr($urlState, 0, $splitPos);
            $client_secret = substr($urlState, $splitPos + 1);
        }

        return [
            "csrf_token" => $csrfToken,
            "client_id" => $client_id,
            "client_secret" => $client_secret
        ];
    }

    public function downloadBackup(): BinaryFileResponse
    {
        $backup = Utils::createBackup();

        return response()->download(
            $backup->get("path"),
            $backup->get("name")
        )->deleteFileAfterSend(true);
    }

    public function importBackup(ImportBackupRequest $request): RedirectResponse
    {
        $backup = $request->file('backup_file');

        $files = Utils::extractBackup($backup);

        if (is_null($files)) {
            return Redirect::route('settings.edit')->with([
                'status' => 'backup-not-imported',
                'message' => 'Backup file is corrupted'
            ]);
        }

        $settings   = $files["settings"];
        $inbounds   = $files["inbounds"];
//        $servers    = $files["servers"];

        $createInboundRequest = new CreateInboundRequest();

        foreach ($inbounds as $inbound) {
            $inbound["user_password"] = $inbound["password"];
            $validator = Validator::make($inbound, $createInboundRequest->rules());
            if ($validator->passes()) {
                try {
                    InboundRepository::create(
                        username: $inbound["username"],password: $inbound["password"],
                        is_active: $inbound["is_active"],traffic_limit: $inbound["traffic_limit"],
                        remaining_traffic: $inbound["remaining_traffic"], max_login: $inbound["max_login"],
                        server_ip: $inbound["server_ip"] ,expires_at: $inbound["expires_at"]
                    );
                } catch (QueryException) {
                    // SQLSTATE[23000]: Integrity constraint violation: Duplicate entry for key 'inbounds.inbounds_username_unique'
                }
            }
        }

        $updateInboundsSettingsRequest = new UpdateInboundSettingsRequest();
        $inboundsDefaultsSettings = [
            "inbound_traffic_limit" => $settings["inbound_traffic_limit"],
            "inbound_active_days" => $settings["inbound_active_days"],
            "inbound_max_login" => $settings["inbound_max_login"]
        ];
        $validator = Validator::make($inboundsDefaultsSettings, $updateInboundsSettingsRequest->rules());
        SettingRepository::update(SettingRepository::first(), $validator->validated());

        return Redirect::route('settings.edit')->with('status', 'backup-imported');
    }
}
