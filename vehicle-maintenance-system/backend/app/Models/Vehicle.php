<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vin',
        'plate_number',
        'year',
        'make',
        'model',
        'color',
        'engine_type',
        'fuel_type',
        'odometer',
        'purchase_price',
        'current_value',
        'purchase_date',
        'group',
        'status',
        'latitude',
        'longitude',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'odometer' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'purchase_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class);
    }

    public function assignments()
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(VehicleAssignment::class)
            ->where('status', 'Active')
            ->latest();
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function serviceSchedules()
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    public function fuelCards()
    {
        return $this->hasMany(FuelCard::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeInGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    // Accessors
    public function getTotalMaintenanceCostAttribute()
    {
        return $this->workOrders()
            ->where('status', 'Completed')
            ->sum('total_cost');
    }

    public function getAverageFuelEconomyAttribute()
    {
        return $this->fuelLogs()
            ->whereNotNull('fuel_economy')
            ->avg('fuel_economy');
    }
}
