<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->enum('frequency_type', ['Mileage', 'Time', 'Both'])->default('Mileage');
            $table->integer('frequency_value')->nullable(); // Every X km or months
            $table->string('frequency_unit')->nullable(); // km, miles, months, etc.
            $table->decimal('last_service_odometer', 10, 2)->nullable();
            $table->date('last_service_date')->nullable();
            $table->decimal('next_service_odometer', 10, 2)->nullable();
            $table->date('next_service_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_schedules');
    }
};
