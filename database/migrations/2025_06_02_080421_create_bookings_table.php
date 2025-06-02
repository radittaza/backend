<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->integer('rental_period');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->foreignId('delivery_location')->constrained('addresses')->onDelete('cascade');
            $table->enum('rental_status', ['active', 'cancelled', 'pending'])->default('pending');
            $table->integer('total_price');
            $table->string('secure_url_image')->nullable();
            $table->string('public_url_image')->nullable();
            $table->enum('payment_proof', ['paid', 'unpaid', 'pending'])->default('unpaid');
            $table->foreignId('bank_transfer')->constrained('bank_transfers')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
