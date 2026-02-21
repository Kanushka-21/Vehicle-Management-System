<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'service_name',
        'description',
        'frequency_type',
        'frequency_value',
        'frequency_unit',
        'last_service_odometer',
        'last_service_date',
        'next_service_odometer',
        'next_service_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'last_service_odometer' => 'decimal:2',
        'last_service_date' => 'date',
        'next_service_odometer' => 'decimal:2',
        'next_service_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Check if service is due
    public function isDue(): bool
    {
        if ($this->frequency_type === 'Mileage' && $this->next_service_odometer) {
            return $this->vehicle->odometer >= $this->next_service_odometer;
        }
        
        if ($this->frequency_type === 'Time' && $this->next_service_date) {
            return now()->gte($this->next_service_date);
        }
        
        if ($this->frequency_type === 'Both') {
            return ($this->vehicle->odometer >= $this->next_service_odometer) || 
                   now()->gte($this->next_service_date);
        }
        
        return false;
    }
}
