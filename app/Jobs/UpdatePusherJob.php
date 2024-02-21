<?php

namespace App\Jobs;

use App\Repositories\SettingRepository;
use App\Utils\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class UpdatePusherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $setting = SettingRepository::first();

        Utils::putPermanentEnv("PUSHER_APP_ID", $setting->pusher_id);
        Utils::putPermanentEnv("PUSHER_APP_KEY", $setting->pusher_key);
        Utils::putPermanentEnv("PUSHER_APP_SECRET", $setting->pusher_secret);
        Utils::putPermanentEnv("PUSHER_APP_CLUSTER", $setting->pusher_cluster);
        Utils::putPermanentEnv("PUSHER_PORT", $setting->pusher_port);

        Artisan::call("cache:clear");
        Artisan::call("config:clear");
        Artisan::call("config:cache");
    }
}
