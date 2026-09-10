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
        Schema::create('salons_showtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('salons')->cascadeOnDelete();
            $table->foreignId('showtime_id')->constrained('showtimes')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salons_showtimes');
    }
};
