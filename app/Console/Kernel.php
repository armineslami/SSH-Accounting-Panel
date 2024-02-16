<?php

namespace App\Console;

use App\Jobs\GetSystemInfoJob;
use App\Repositories\SettingRepository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $setting = SettingRepository::first();

        $schedule->job(new GetSystemInfoJob())->everyTwoSeconds();

        switch ($setting->app_inbound_bandwidth_check_interval) {
            case 30:
                $schedule->command('app:update-bandwidth-usage')->everyThirtyMinutes()->runInBackground();
                break;
            case 60:
                $schedule->command('app:update-bandwidth-usage')->hourly()->runInBackground();
                break;
            case 360:
                $schedule->command('app:update-bandwidth-usage')->everySixHours()->runInBackground();
                break;
            default:
                $schedule->command('app:update-bandwidth-usage')->daily()->runInBackground();
        }

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
