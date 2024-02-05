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
}
