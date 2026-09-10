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
        Schema::create('seat_showtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seat_id')->constrained('seats')->cascadeOnDelete();
            $table->foreignId('showtime_id')->constrained('showtimes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['seat_id', 'showtime_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_showtimes');
    }
};
