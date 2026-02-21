<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'card_number',
        'provider',
        'vehicle_id',
        'driver_id',
        'issue_date',
        'expiry_date',
        'credit_limit',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

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
