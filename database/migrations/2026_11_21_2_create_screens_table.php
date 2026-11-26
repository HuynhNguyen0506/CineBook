<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cinema_id')
                ->constrained('cinemas')
                ->restrictOnDelete();

            $table->string('name', 100);

            // 2D, 3D, IMAX...
            $table->string('screen_type', 30)->default('2D');

            $table->string('status', 20)->default('active');

            $table->timestamps();

            $table->unique(['cinema_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screens');
    }
};
