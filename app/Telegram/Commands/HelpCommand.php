<?php

namespace App\Telegram\Commands;

use App\Telegram\Keyboards\Keyboard;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class HelpCommand extends Command
{
    protected string $name = "help";
    protected string $description = "Get available commands";

    public function handle()
    {
        // Set the chat status to "typing..."
        $this->replyWithChatAction(["action" => Actions::TYPING]);

        # Get all the registered commands.
        $commands = $this->getTelegram()->getCommands();

        $response = "❗️Available commands ❗\n\n";
        foreach ($commands as $name => $command) {
            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
        }

        $this->replyWithMessage([
            "text" => $response,
            "reply_markup" => Keyboard::simpleMarkupKeyboard()
        ]);
    }
}
