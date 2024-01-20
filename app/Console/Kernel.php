<?php

namespace App\Console;

use App\Jobs\GetSystemInfoJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new GetSystemInfoJob())->everyTwoSeconds();

        $schedule->command("app:update-bandwidth-usage")
            ->everyThirtyMinutes()
            ->runInBackground();

        $schedule->command("app:backup-to-dropbox")->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
