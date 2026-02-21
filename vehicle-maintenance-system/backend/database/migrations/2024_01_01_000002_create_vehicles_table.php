<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vin', 17)->unique();
            $table->string('plate_number', 20)->unique();
            $table->integer('year');
            $table->string('make', 100);
            $table->string('model', 100);
            $table->string('color', 50)->nullable();
            $table->string('engine_type', 100)->nullable();
            $table->enum('fuel_type', ['Petrol', 'Diesel', 'Electric', 'Hybrid'])->default('Petrol');
            $table->decimal('odometer', 10, 2)->default(0);
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('current_value', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('group')->nullable();
            $table->enum('status', ['Active', 'In Service', 'Out of Service', 'Sold', 'Retired'])->default('Active');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
