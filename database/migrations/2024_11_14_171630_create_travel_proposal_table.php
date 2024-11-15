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
        Schema::create('travel_proposal', function (Blueprint $table) {
            $table->uuid('travel_id');
            $table->foreign('travel_id')->references('id_travel')->on('travel')->onDelete('cascade');

            $table->uuid('proposal_id');
            $table->foreign('proposal_id')->references('id_proposal')->on('proposal')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_proposal');
    }
};
