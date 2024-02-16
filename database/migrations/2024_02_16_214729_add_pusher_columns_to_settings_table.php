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
            $table->string("pusher_id")->nullable();
            $table->string("pusher_key")->nullable();
            $table->string("pusher_secret")->nullable();
            $table->string("pusher_cluster")->nullable()->default("ap2");
            $table->integer("pusher_port")->nullable()->default(443);
//            $table->string("pusher_scheme")->nullable()->default("https");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                "pusher_id", "pusher_key", "pusher_secret",
                "pusher_port", "pusher_cluster" //"pusher_scheme",
            ]);
        });
    }
};
