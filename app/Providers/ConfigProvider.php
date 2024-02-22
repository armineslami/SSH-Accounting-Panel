<?php

namespace App\Providers;

use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class ConfigProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!App::environment() == 'testing') {
            $settings = SettingRepository::first();

            config(["telegram.bots.sap.token" => $settings->bot_token]);

            config(["broadcasting.pusher.key" => $settings->pusher_key]);
            config(["broadcasting.pusher.secret" => $settings->pusher_secret]);
            config(["broadcasting.pusher.app_id" => $settings->pusher_id]);
            config(["broadcasting.pusher.options.cluster" => $settings->pusher_cluster]);
            config(["broadcasting.pusher.options.port" => $settings->pusher_port]);
        }
    }
}
