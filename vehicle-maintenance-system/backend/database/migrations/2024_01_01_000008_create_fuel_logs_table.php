<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('date');
            $table->decimal('odometer', 10, 2);
            $table->decimal('quantity', 8, 2);
            $table->enum('unit', ['Liters', 'Gallons'])->default('Liters');
            $table->decimal('cost_per_unit', 8, 2);
            $table->decimal('total_cost', 10, 2);
            $table->string('fuel_card_number')->nullable();
            $table->string('station')->nullable();
            $table->boolean('tank_filled')->default(false);
            $table->decimal('fuel_economy', 8, 2)->nullable(); // MPG or KPL
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
    }
};
