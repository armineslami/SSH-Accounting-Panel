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

    public static function executeShellCommand(string $command): false|null|string {
        $scriptPath = base_path($command);
        $command = "$scriptPath 2>&1";
        return shell_exec($command);
    }
}
