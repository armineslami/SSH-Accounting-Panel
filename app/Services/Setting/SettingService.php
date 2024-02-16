<?php

namespace App\Services\Setting;

use App\Utils\Utils;

class SettingService
{
    public static function updateCookie(string $cookieExpireDate): void
    {
        $cookieName = env("APP_UPDATE_CHECK_COOKIE_NAME", "latest-version");
        if (isset($_COOKIE[$cookieName])) {
            $cookieVersion = $_COOKIE[$cookieName];
            setcookie($cookieName, $cookieVersion, Utils::getCookieExpiryDate($cookieExpireDate), "/");
        }
    }
}
