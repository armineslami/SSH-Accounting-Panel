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
        Schema::create('outlines', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('outline_id')->unique();
            $table->string('key_name')->nullable();
            $table->string('key');
            $table->unsignedBigInteger('inbound_id')->unique();
            $table->timestamps();

            $table->foreign('inbound_id')->references('id')->on('inbounds')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outline');
    }
};
