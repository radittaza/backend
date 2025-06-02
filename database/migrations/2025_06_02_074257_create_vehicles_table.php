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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->enum('vehicle_type', ['motorcycle', 'car']);
            $table->string('vehicle_name');
            $table->integer('rental_price');
            $table->enum('availability_status', ['available', 'rented', 'inactive'])->default('available');
             $table->dateTime('year');
            $table->integer('seats');
            $table->integer('horse_power');
            $table->text('description');
            $table->text('specification_list');
            $table->string('secure_url_image')->nullable();
            $table->string('public_url_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
