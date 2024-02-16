<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInboundRequest;
use App\Http\Requests\DropboxCallbackRequest;
use App\Http\Requests\DropboxLinkRequest;
use App\Http\Requests\ImportBackupRequest;
use App\Http\Requests\UpdateAppSettingsRequest;
use App\Http\Requests\UpdateInboundSettingsRequest;
use App\Http\Requests\UpdatePusherSettingsRequest;
use App\Http\Requests\UpdateTelegramSettingsRequest;
use App\Repositories\InboundRepository;
use App\Repositories\SettingRepository;
use App\Services\Backup\BackupService;
use App\Services\Dropbox\DropboxService;
use App\Services\Setting\SettingService;
use App\Services\Telegram\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = SettingRepository::first();
        return view('settings.edit', ['settings' => $settings]);
    }

    public function updateApp(UpdateAppSettingsRequest $request): RedirectResponse
    {
        SettingService::updateCookie($request->app_update_check_interval);

        SettingRepository::update(SettingRepository::first(), $request->validated());

        return Redirect::route('settings.edit')->with('status', 'settings-updated');
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

        $telegramService = new TelegramService();
        $isBotSet = $telegramService->bot($token, $port, $host);

        if (!$isBotSet) {
            return Redirect::route('settings.edit')->with([
                'status' => 'telegram-not-updated',
                'message' => $telegramService->error()
            ]);
        }

        SettingRepository::update(SettingRepository::first(), $request->all());

        return Redirect::route('settings.edit')->with('status', 'telegram-updated');
    }

    public function linkDropbox(DropboxLinkRequest $request): RedirectResponse
    {
        $request->validated();

        $dropboxService = new DropboxService(
            $request->dropbox_client_id, $request->dropbox_client_secret
        );

        $dropbox = $dropboxService->getDropbox();

        if (!$dropbox) {
            return Redirect::route('settings.edit')->with([
                "status" => "dropbox-not-linked",
                "message" => $dropboxService->error()
            ]);
        }

        $authUrl = $dropboxService->authUrl($dropbox);

        if (!$authUrl) {
            return Redirect::route('settings.edit')->with([
                "status" => "dropbox-not-linked",
                "message" => $dropboxService->error()
            ]);
        }

        return redirect($authUrl);
    }

    public function callbackDropbox(DropboxCallbackRequest $request): RedirectResponse
    {
        $request->validated();

        $code   = $request->code;
        $state  = $request->state;

        $dropboxState = DropboxService::decodeState($state);

        $csrfToken      = $dropboxState->getCsrf();
        $clientId       = $dropboxState->getClientId();
        $clientSecret   = $dropboxState->getClientSecret();

        $dropboxService = new DropboxService($clientId, $clientSecret);
        $dropbox        = $dropboxService->getDropbox();

        if (!$dropbox) {
            return Redirect::route('settings.edit')->with([
                "status" => "dropbox-not-linked",
                "message" => $dropboxService->error()
            ]);
        }

        $accessToken = $dropboxService->token(
            dropbox: $dropbox, csrf: $csrfToken, code: $code, state: $state
        );

        if (!$accessToken) {
            return Redirect::route('settings.edit')->with([
                "status" => "dropbox-not-linked",
                "message" => $dropboxService->error()
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
            $newAccessToken = DropboxService::refreshDropboxToken(
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

        $dropboxService = new DropboxService($clientId, $clientSecret, $dropboxToken);
        $dropbox        = $dropboxService->getDropbox();

        if (!$dropbox) {
            return Redirect::route('settings.edit')->with([
                "status" => "dropbox-not-unlinked",
                "message" => $dropboxService->error()
            ]);
        }

        $isUnlinked = $dropboxService->unlink($dropbox);

        if (!$isUnlinked) {
            return Redirect::route('settings.edit')->with([
                "status" => "dropbox-not-unlinked",
                "message" => $dropboxService->error()
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

    public function updatePusher(UpdatePusherSettingsRequest $request): RedirectResponse
    {
        SettingRepository::update(SettingRepository::first(), $request->validated());

        return Redirect::route('settings.edit')->with('status', 'settings-updated');
    }

    public function downloadBackup(): BinaryFileResponse
    {
        $backup = BackupService::createBackup();

        return response()->download(
            $backup->get("path"),
            $backup->get("name")
        )->deleteFileAfterSend(true);
    }

    public function importBackup(ImportBackupRequest $request): RedirectResponse
    {
        $backup = $request->file('backup_file');

        $files = BackupService::extractBackup($backup);

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
