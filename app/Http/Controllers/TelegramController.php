<?php

namespace App\Http\Controllers;

use App\Repositories\SettingRepository;
use App\Services\Telegram\Buttons\Buttons;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function __invoke(): void
    {
        $settings = SettingRepository::first();

        if (is_null($settings->bot_token)) {
            return;
        }

        config(['telegram.bots.sap.token' => $settings->bot_token]);

//    $updates = Telegram::commandsHandler(true);
        $updates = Telegram::getWebhookUpdate();

        $command = $updates->message->text;

        if ($command === "/start") {
            Telegram::triggerCommand("start", $updates);
        }
        elseif (
            $command === Buttons::LOGIN ||
            $command === "/login" ||
            preg_match('/^([a-zA-Z0-9]+):([a-zA-Z0-9!@#$%^&*()\-=_+{}|:"<>?\[\]\\\;\',.\/]+)$/', $command)) {
            Telegram::triggerCommand("login", $updates);
        }
        else {
            Telegram::triggerCommand("help", $updates);
        }
    }
}
