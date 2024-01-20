<?php

namespace App\Services\Dropbox;

class DropboxState
{
    private string|null $csrf;
    private string|null $clientId;
    private string|null $clientSecret;

    public function __construct($csrf, $clientId, $clientSecret)
    {
        $this->csrf         = $csrf;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getCsrf(): string|null
    {
        return $this->csrf;
    }

    public function getClientId(): string|null
    {
        return $this->clientId;
    }

    public function getClientSecret(): string|null
    {
        return $this->clientSecret;
    }
}
