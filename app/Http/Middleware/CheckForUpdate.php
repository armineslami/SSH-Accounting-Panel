<?php

namespace App\Http\Middleware;

use App\Events\AppUpdateAvailable;
use App\Repositories\SettingRepository;
use App\Utils\Utils;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckForUpdate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        self::checkForUpdate();
        return $next($request);
    }

    private static function checkForUpdate(): void
    {
        $settings = SettingRepository::first();

        // Don't continue when pusher is not set
        if (is_null($settings->pusher_id) || is_null($settings->pusher_key) || is_null($settings->pusher_secret)) {
            return;
        }

        $cookieName = env("APP_UPDATE_CHECK_COOKIE_NAME", "latest-version");

        if (isset($_COOKIE[$cookieName]) || $settings->app_update_check_interval === "never") {
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

        $cookieExpireDate = $settings->app_update_check_interval;

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
            setcookie($cookieName, $latestVersion, Utils::getCookieExpiryDate($cookieExpireDate), "/");
        }
    }
}
