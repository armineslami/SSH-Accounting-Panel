<?php

namespace App\Utils;

use App\Models\Inbound;
use Illuminate\Support\Carbon;

class Utils
{
    public static function convertExpireAtDateToActiveDays(Inbound $inbound): Inbound
    {
        if (!$inbound->expires_at) {
            $inbound->active_days = '';
        } else {
            $expires_at = Carbon::parse($inbound->expires_at)->endOfDay();
            $today = Carbon::now()->endOfDay();
            $diff = $expires_at->diffInDays($today);
            $inbound->active_days = $today->greaterThan($expires_at) ? 0 : $diff;
        }

        return $inbound;
    }

    public static function convertActiveDaysToExpireAtDate(?int $active_days): ?string
    {
        if (!$active_days) {
            return null;
        } else {
            return Carbon::now()->addDays($active_days)->toDateString();
        }
    }

    public static function executeShellCommand(string $command): false|null|string
    {
        $scriptPath = base_path($command);
        $command = "$scriptPath 2>&1";
        return shell_exec($command);
    }

    public static function consoleLog(string $message): void
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $output->writeln("<info>" . $message . "</info>");
    }

    public static function generateRandomString($length = 30): string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function compareVersions($version1, $version2): int
    {
        $parts1 = explode('.', $version1);
        $parts2 = explode('.', $version2);

        // Compare major version
        if ($parts1[0] > $parts2[0]) {
            return 1;
        } elseif ($parts1[0] < $parts2[0]) {
            return -1;
        }

        // Compare minor version
        if ($parts1[1] > $parts2[1]) {
            return 1;
        } elseif ($parts1[1] < $parts2[1]) {
            return -1;
        }

        // Compare patch version
        if ($parts1[2] > $parts2[2]) {
            return 1;
        } elseif ($parts1[2] < $parts2[2]) {
            return -1;
        }

        // Versions are equal
        return 0;
    }

    public static function getCookieExpiryDate(string $interval): int
    {
        return match ($interval) {
            "day"   => \Carbon\Carbon::now()->addDay()->timestamp,
            "week"  => Carbon::now()->addWeek()->timestamp,
            "month" => Carbon::now()->addMonth()->timestamp,
            default => Carbon::now()->addYears(10)->timestamp,
        };
    }

    public static function getAppLatestVersion(): false|null|string
    {
        return shell_exec("curl  'https://api.github.com/repos/armineslami/SSH-Accounting-Panel/tags' | jq -r '.[0].name'");
    }

    public static function putPermanentEnv($key, $value): void
    {
        $path = app()->environmentFilePath();

        file_put_contents(
            $path,
            preg_replace(
                "/^{$key}.*$/m", "{$key}={$value}",
                file_get_contents($path)
            )
        );
    }
}
