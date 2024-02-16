<?php

namespace App\Services\App;

use App\Events\AppUpdateAvailable;
use App\Models\Setting;
use App\Repositories\SettingRepository;
use App\Utils\Utils;

class AppService
{
    private static Setting|null $settings;

    public static function boot(): void
    {
        self::$settings = SettingRepository::first();
        AppService::setConfigs();
        AppService::checkForUpdate();
    }

    private static function setConfigs(): void
    {
        if (self::$settings) {
            // Set telegram bot token
            config(["telegram.bots.sap.token" => self::$settings->bot_token]);

            // Set pusher configs
            config(["broadcasting.pusher.key" => self::$settings->pusher_key]);
            config(["broadcasting.pusher.secret" => self::$settings->pusher_secret]);
            config(["broadcasting.pusher.app_id" => self::$settings->pusher_id]);
            config(["broadcasting.pusher.options.cluster" => self::$settings->pusher_cluster]);
            config(["broadcasting.pusher.options.port" => self::$settings->pusher_port]);
        }
    }

    private static function checkForUpdate(): void
    {
        $cookieName = env("APP_UPDATE_CHECK_COOKIE_NAME", "latest-version");

        if (isset($_COOKIE[$cookieName]) || self::$settings->app_update_check_interval === "never") {
            return;
        }

        $latestVersion  = Utils::getAppLatestVersion();
        $currentVersion = config("app.version");

        if (!isset($latestVersion)) {
            return;
        }

        // Remove \n from the end
        $latestVersion = trim($latestVersion);

        // Remove letter 'v'
        $latestVersion = str_replace("v", "", $latestVersion);

        $cookieExpireDate = self::$settings->app_update_check_interval;

        /**
         * If the latest version is newer than the current version, dispatch the event but
         * let the client set the cookie to make sure the cookie is set only after the banner is shown
         */
        if (Utils::compareVersions($latestVersion, $currentVersion) === 1) {
            try {
                AppUpdateAvailable::dispatch($latestVersion, [
                    "name" => $cookieName,
                    "expire_date" => Utils::getCookieExpiryDate($cookieExpireDate)
                ]);
            }
            catch (\Exception $e) {}
        }
        else {
            /** Set the cookie on server, because both latest and current version are the same */
            setcookie($cookieName, $latestVersion, Utils::getCookieExpiryDate($cookieExpireDate));
        }
    }
}
