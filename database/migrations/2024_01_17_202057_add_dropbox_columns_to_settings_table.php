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
            $table->string("dropbox_client_id")->nullable();
            $table->string("dropbox_client_secret")->nullable();
            $table->string("dropbox_token")->nullable();
            $table->string("dropbox_refresh_token")->nullable();
            $table->timestamp("dropbox_token_expire_date")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                "dropbox_client_id", "dropbox_client_secret", "dropbox_token",
                "dropbox_refresh_token", "dropbox_token_expire_date"
            ]);
        });
    }
};
