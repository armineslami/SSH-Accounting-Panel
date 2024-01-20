<?php

namespace App\Services\Dropbox;

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Kunnu\Dropbox\Models\AccessToken;

class DropboxService
{
    private string $clientId;
    private string $clientSecret;
    private string|null $error;
    private DropboxApp $app;

    public function __construct(string $clientId, string $clientSecret, string $accessToken = null)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->error        = null;
        $this->app          = new DropboxApp($clientId, $clientSecret, $accessToken);
    }

    public function getDropbox(): Dropbox|null
    {
        try {
            return new Dropbox($this->app);
        } catch (DropboxClientException $e) {
            $message = json_decode($e->getMessage(), true);
            $this->error = $message['error_description'] ?? ($message["error"] ?? $e->getMessage());
        }
        return null;
    }

    public function authUrl(Dropbox $dropbox): string|null
    {
        $authHelper = $dropbox->getAuthHelper();

        //Callback URL
        $callbackUrl = route("settings.dropbox.callback");

        // Additional user provided parameters to pass in the request
        $params = [];

        // Url State - Additional User provided state data
        $urlState = $this->clientId . "|" . $this->clientSecret;

        // Token Access Type
        $tokenAccessType = "offline";

        //Fetch the Authorization/Login URL
        return $authHelper->getAuthUrl($callbackUrl, $params, $urlState, $tokenAccessType);
    }

    public function unlink(Dropbox $dropbox): bool
    {
        $authHelper = $dropbox->getAuthHelper();
        try {
            $authHelper->revokeAccessToken();
            return true;
        } catch (DropboxClientException $e) {
            $message = json_decode($e->getMessage(), true);
            $this->error = $message['error']['.tag'] ?: $message['error'];
        }

        return false;
    }

    public function error(): string|null
    {
        return $this->error;
    }

    public static function decodeState(string $state): DropboxState|null
    {
        $csrfToken = null;
        $clientId = null;
        $clientSecret = null;

        $splitPos = strpos($state, "|");

        if ($splitPos !== false) {
            $csrfToken = substr($state, 0, $splitPos);
            $urlState = substr($state, $splitPos + 1);

            $splitPos = strpos($urlState, "|");

            $clientId = substr($urlState, 0, $splitPos);
            $clientSecret = substr($urlState, $splitPos + 1);
        }

        return new DropboxState($csrfToken, $clientId, $clientSecret);
    }

    public static function refreshDropboxToken(string $clientId, string $clientSecret, string $refreshToken): AccessToken|null
    {
        $app = new DropboxApp($clientId, $clientSecret);

        try {
            $dropbox = new Dropbox($app);
            $authHelper = $dropbox->getAuthHelper();
            $accessToken = new AccessToken([
                "refresh_token" => $refreshToken
            ]);
            return $authHelper->getRefreshedAccessToken($accessToken);
        } catch (DropboxClientException) {
            return null;
        }
    }

    public function token(Dropbox $dropbox, string $csrf, string $code, string $state): AccessToken|null
    {
        $authHelper = $dropbox->getAuthHelper();
        $authHelper->getPersistentDataStore()->set('state', $csrf);
        $callbackUrl = route("settings.dropbox.callback");

        try {
            return $authHelper->getAccessToken($code, $state, $callbackUrl);
        } catch (DropboxClientException $e) {
            $message = json_decode($e->getMessage(), true);
            $this->error = $message['error_description'] ?? ($message["error"] ?? $e->getMessage());
        }

        return null;
    }
}
