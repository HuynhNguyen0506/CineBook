<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('movie_id')
                ->constrained('movies')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('rating');

            $table->text('content')->nullable();

            // pending, approved, rejected
            $table->string('status', 20)->default('pending');

            $table->foreignId('moderated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('moderated_at')->nullable();

            $table->timestamps();

            // Mỗi người chỉ có một đánh giá cho một phim
            $table->unique(['user_id', 'movie_id']);

            $table->index(['movie_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
