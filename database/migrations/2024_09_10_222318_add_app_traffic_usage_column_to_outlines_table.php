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
        Schema::table('outlines', function (Blueprint $table) {
            $table->float('traffic_usage')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlines', function (Blueprint $table) {
            $table->dropColumn([
                "traffic_usage",
            ]);
        });
    }
};
