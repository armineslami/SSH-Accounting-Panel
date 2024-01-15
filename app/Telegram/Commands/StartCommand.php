<?php

namespace App\Telegram\Commands;

use App\Telegram\Keyboards\Keyboard;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends Command
{
    protected string $name = "start";
    protected string $description = "Start using the bot 🤖";
    protected string $pattern = '{username}{password}';

    public function handle()
    {
        $this->replyWithChatAction(["action" => Actions::TYPING]);

        $bot = Telegram::getMe();
        $botName = $bot->firstName;
//        $chadId = $this->getUpdate()->getMessage()->from->id;
        $firstName = $this->getUpdate()->getMessage()->from->firstName;
        $username = $this->getUpdate()->getMessage()->from->username;

        $this->replyWithMessage([
            'text' => "Hi ". ($firstName ?? $username ?? "") ." 👋🏻\n\nI'm ". $botName ." bot 🤖
To get start choose one of the buttons below👇🏻",
            'reply_markup' => Keyboard::simpleMarkupKeyboard()
        ]);
    }
}
