<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255);
            $table->string('slug')->unique();

            $table->text('description')->nullable();

            $table->unsignedSmallInteger('duration_minutes');
            $table->string('genre', 100)->nullable();
            $table->string('language', 50)->nullable();

            $table->string('director')->nullable();
            $table->json('cast_members')->nullable();

            $table->string('poster_path')->nullable();
            $table->string('trailer_url')->nullable();

            $table->date('release_date')->nullable();
            $table->string('age_rating', 20)->nullable();

            // coming_soon, showing, ended
            $table->string('status', 30)->default('coming_soon');

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('release_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
