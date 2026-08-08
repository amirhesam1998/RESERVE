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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->unsignedInteger('row');
            // $table->unsignedInteger('column_number');
            $table->unsignedInteger('number');
            $table->string('customText');
            $table->unsignedInteger('x');
            $table->unsignedInteger('y');
            $table->enum('type', ['regular', 'VIP', 'wheelchair'])->default('regular');
            $table->enum('status', ['available', 'reserved', 'sold', 'blocked'])->default('available');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
