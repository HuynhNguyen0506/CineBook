<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->restrictOnDelete();

            $table->foreignId('showtime_seat_id')
                ->constrained('showtime_seats')
                ->restrictOnDelete();

            // Lưu thông tin ghế tại thời điểm đặt vé
            $table->string('seat_label', 20);
            $table->string('seat_type', 30);

            // Giá vé tại thời điểm đặt
            $table->decimal('unit_price', 12, 2);

            $table->timestamps();

            $table->unique([
                'booking_id',
                'showtime_seat_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_details');
    }
};
