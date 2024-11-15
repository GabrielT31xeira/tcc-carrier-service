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
        Schema::create('travel', function (Blueprint $table) {
            $table->uuid('id_travel')->primary();
            $table->uuid('user_id');

            $table->uuid('arrival_id');
            $table->foreign('arrival_id')->references('id_arrival')->on('arrival')->onDelete('cascade');

            $table->uuid('output_id');
            $table->foreign('output_id')->references('id_output')->on('output')->onDelete('cascade');

            $table->uuid('vehicle_id');
            $table->foreign('vehicle_id')->references('id_vehicle')->on('vehicles')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel');
    }
};
