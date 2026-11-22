<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cinemas', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('slug')->unique();

            $table->string('city', 100);
            $table->string('address', 255);

            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            $table->string('status', 20)->default('active');

            $table->timestamps();

            $table->index(['city', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cinemas');
    }
};
