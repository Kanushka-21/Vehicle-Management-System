<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'date',
        'odometer',
        'quantity',
        'unit',
        'cost_per_unit',
        'total_cost',
        'fuel_card_number',
        'station',
        'tank_filled',
        'fuel_economy',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'odometer' => 'decimal:2',
        'quantity' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'fuel_economy' => 'decimal:2',
        'tank_filled' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($fuelLog) {
            $fuelLog->total_cost = $fuelLog->quantity * $fuelLog->cost_per_unit;
            
            // Calculate fuel economy if tank was filled
            if ($fuelLog->tank_filled) {
                $lastFillup = static::where('vehicle_id', $fuelLog->vehicle_id)
                    ->where('tank_filled', true)
                    ->where('date', '<', $fuelLog->date)
                    ->orderBy('date', 'desc')
                    ->first();

                if ($lastFillup) {
                    $distance = $fuelLog->odometer - $lastFillup->odometer;
                    if ($distance > 0 && $fuelLog->quantity > 0) {
                        $fuelLog->fuel_economy = $distance / $fuelLog->quantity;
                    }
                }
            }
        });
    }

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
