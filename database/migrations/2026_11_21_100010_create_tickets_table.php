<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_detail_id')
                ->unique()
                ->constrained('booking_details')
                ->restrictOnDelete();

            $table->string('ticket_code', 100)->unique();

            $table->string('qr_code_path')->nullable();

            // valid, used, cancelled
            $table->string('status', 20)->default('valid');

            $table->dateTime('issued_at')->nullable();
            $table->dateTime('used_at')->nullable();

            $table->foreignId('checked_in_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
