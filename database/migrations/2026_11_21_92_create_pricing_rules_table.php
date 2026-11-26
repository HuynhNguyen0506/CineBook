<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);

            // Gold, Platinum, Box hoặc null (tất cả)
            $table->string('seat_type', 30)->nullable();

            // all, weekday, weekend, holiday
            $table->string('day_type', 30)->default('all');

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // fixed hoặc percentage
            $table->string('adjustment_type', 20);

            $table->decimal('adjustment_value', 12, 2);

            $table->unsignedInteger('priority')->default(0);

            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
