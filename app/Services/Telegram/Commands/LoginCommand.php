<?php

namespace App\Services\Telegram\Commands;

use App\Models\Inbound;
use App\Repositories\ServerRepository;
use App\Services\Telegram\Keyboards\Keyboard;
use App\Utils\Utils;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class LoginCommand extends Command
{
    protected string $name = 'login';
    protected string $description = 'Login to your account using username and password';

    public function handle()
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $text = $this->getUpdate()->getMessage()->text;

        if (preg_match('/^([a-zA-Z0-9]+):([a-zA-Z0-9!@#$%^&*()\-=_+{}|:"<>?\[\]\\\;\',.\/]+)$/', $text, $matches)) {
            $username = $matches[1];
            $password = $matches[2];

            if (is_null($username) || is_null($password)) {
                $this->replayWithInstruction();
                return;
            }

            $inbound = $this->getInbound($username, $password);

            if (is_null($inbound)) {
                $this->replayWithNotFound();
                return;
            }

            $server  = ServerRepository::byAddress($inbound->server_ip);
            $inbound = Utils::convertExpireAtDateToActiveDays($inbound);

            $this->replyWithMessage([
                'text' => "❗ *Account Info* ❗️
\n👤 *Username*: $inbound->username
\n🌐 *Server*: $inbound->server_ip
\n🅿️ *Port*: $server->port
\n🅿️ *UDP Port*: $server->udp_port
\n🔋 *Active*: " . ($inbound->is_active == "1" ? "👍🏻" : "👎🏻")
."\n\n🚦 *Traffic*: " . (!isset($inbound->traffic_limit) ? "♾️" : ($inbound->traffic_limit - $inbound->remaining_traffic)."G / " . $inbound->traffic_limit. "G")
."\n\n⏳ *Remaining Days*: " . ($inbound->active_days == "" ? "♾️" : $inbound->active_days)
."\n\n📱 *Max Device*: $inbound->max_login",
                'reply_markup' => Keyboard::simpleMarkupKeyboard(),
                'parse_mode' => 'markdown'
            ]);
        }
        else {
            $this->replayWithInstruction();
        }
    }

    private function replayWithInstruction(): void {
        $this->replyWithMessage([
            "text" => "🔐 Login 🔐
\nTo log in to your account, send your username and password like below 👇🏻
\nUSERNAME:PASSWORD
\n",
            'reply_markup' => Keyboard::simpleMarkupKeyboard()
        ]);
    }

    private function replayWithNotFound(): void {
        $this->replyWithMessage([
            "text" => "🔐 Login 🔐
\nUser not found 😕
\nMake sure given username and password are correct ❗️",
            'reply_markup' => Keyboard::simpleMarkupKeyboard()
        ]);
    }

    private function getInbound($username, $password): Inbound|null  {
        return Inbound::where("username", $username)->where("password", $password)->first();
    }
}
