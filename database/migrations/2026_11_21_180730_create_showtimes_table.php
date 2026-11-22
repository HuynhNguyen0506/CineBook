<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('showtimes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('movie_id')
                ->constrained('movies')
                ->restrictOnDelete();

            $table->foreignId('screen_id')
                ->constrained('screens')
                ->restrictOnDelete();

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->decimal('base_price', 12, 2);

            // scheduled, ongoing, completed, cancelled
            $table->string('status', 30)->default('scheduled');

            $table->timestamps();

            $table->index(['screen_id', 'start_at']);
            $table->index(['movie_id', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }   
};
