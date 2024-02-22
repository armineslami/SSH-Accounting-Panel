<?php

namespace App\Services\Setting;

use App\Utils\Utils;

class SettingService
{
    public static function updateCookie(string $cookieExpireDate): void
    {
        $cookieName = config("app.update_check_cookie");
        if (isset($_COOKIE[$cookieName])) {
            $cookieVersion = $_COOKIE[$cookieName];
            setcookie($cookieName, $cookieVersion, Utils::getCookieExpiryDate($cookieExpireDate), "/");
        }
    }
}
