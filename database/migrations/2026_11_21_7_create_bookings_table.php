<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->string('booking_code', 50)->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('showtime_id')
                ->constrained('showtimes')
                ->restrictOnDelete();

            // Lưu thông tin khách tại thời điểm đặt vé
            $table->string('customer_name', 100);
            $table->string('customer_email');
            $table->string('customer_phone', 20)->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            // pending, confirmed, cancelled, failed, expired
            $table->string('status', 30)->default('pending');

            // unpaid, paid, failed, refunded
            $table->string('payment_status', 30)->default('unpaid');

            // mock, cash...
            $table->string('payment_method', 30)->nullable();

            $table->string('transaction_code', 100)->nullable();
            $table->dateTime('paid_at')->nullable();

            $table->dateTime('expires_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
