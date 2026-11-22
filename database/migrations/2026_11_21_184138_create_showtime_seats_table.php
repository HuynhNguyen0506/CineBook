<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('showtime_seats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('showtime_id')
                ->constrained('showtimes')
                ->restrictOnDelete();

            $table->foreignId('seat_id')
                ->constrained('seats')
                ->restrictOnDelete();

            // available, held, booked
            $table->string('status', 20)->default('available');

            $table->foreignId('held_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->uuid('hold_token')->nullable();
            $table->dateTime('held_until')->nullable();

            $table->timestamps();

            $table->unique(['showtime_id', 'seat_id']);

            $table->index(['showtime_id', 'status']);
            $table->index('held_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('showtime_seats');
    }
};
