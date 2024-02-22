<?php

namespace App\Services\Telegram;

use Exception;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramService
{
    private string|null $error;

    public function __construct() {
        $this->error = null;
    }

    public function bot(string $token = null, int $port= null, string $host = null): bool
    {
        if ($token && $port) {

            $this->putTokenIntoConfig($token);
            $webhookUrl = $this->createWebhookUrl($host, $port);

            try {
                return $this->setWebhook($webhookUrl);
            } catch (Exception $e) {
                if (str_contains($e->getMessage(), "Timeout was reached")) {
                    $this->error = "Timeout was reached. Make sure telegram is not banned in your server region.";
                }
                else {
                    $this->error = "Could not set webhook url to " . $webhookUrl;
                }

                return false;
            }
        }

        // Token, port or both are null, so consider the request as remove webhook demand
        return $this->removeWebhook();
    }

    /**
     * @throws Exception
     */
    public function setWebhook(string $url): bool
    {
        return Telegram::setWebhook([
            "url" => $url
//           "certificate" => "/etc/letsencrypt/live/sap/fullchain.pem"
        ]);
    }

    public function removeWebhook(): bool
    {
        return Telegram::removeWebhook();
    }

    protected function putTokenIntoConfig($token): void
    {
        config(['telegram.bots.sap.token' => $token]);
    }

    protected function createWebhookUrl(string $host, string $port): string
    {
        return config("app.env") === "local" ?
            config("app.telegram_webhook_address") . "/api/<token>/webhook" :
            $host . ":" . $port . "/api/<token>/webhook";
    }

    public function error(): string|null
    {
        return $this->error;
    }
}
