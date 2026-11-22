<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('screen_id')
                ->constrained('screens')
                ->restrictOnDelete();

            $table->string('row_label', 5);
            $table->unsignedSmallInteger('seat_number');

            // Gold, Platinum, Box
            $table->string('seat_type', 30)->default('Gold');

            $table->string('status', 20)->default('active');

            $table->timestamps();

            $table->unique([
                'screen_id',
                'row_label',
                'seat_number'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
