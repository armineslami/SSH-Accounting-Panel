<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->enum(
                'app_update_check_interval',
                ['day', 'week', 'month', 'never']
            )->default('week');
            $table->integer("app_inbound_bandwidth_check_interval")->default(30);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                "app_update_check_interval", "app_inbound_bandwidth_check_interval"
            ]);
        });
    }
};
