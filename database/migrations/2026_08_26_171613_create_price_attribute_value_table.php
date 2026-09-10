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
        Schema::create('price_attribute_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_value_id')->constrained('attribute_value')->cascadeOnDelete();
            $table->foreignId('price_id')->constrained('prices')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_attribute_value');
    }
};
