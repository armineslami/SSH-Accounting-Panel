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
        Schema::create('system_info', function (Blueprint $table) {
            $table->id();
            $table->string('cpuUsage');
            $table->string('memory');
            $table->string('memoryUsage');
            $table->string('swap');
            $table->string('swapUsage');
            $table->string('disk');
            $table->string('diskUsage');
            $table->string('upTime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_info');
    }
};
