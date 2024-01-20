<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInboundSettingsRequest;
use App\Http\Requests\UpdateTelegramSettingsRequest;
use App\Repositories\SettingRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
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

        $url = env("APP_ENV", "")  === "local" ?
            env("TELEGRAM_WEBHOOK_ADDRESS"). "/api/<token>/webhook" :
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
}
