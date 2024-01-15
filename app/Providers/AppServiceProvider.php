<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->setTelegramBotToken();
    }

    private function setTelegramBotToken(): void {
        $settings = Setting::first();
        if ($settings) {
            config(["telegram.bots.sap.token" => $settings->bot_token]);
        }
    }
}
