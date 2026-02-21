<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'service_type',
        'average_rating',
        'notes',
    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
    ];

    // Relationships
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    // Calculate average cost of work orders
    public function getAverageCostAttribute()
    {
        return $this->workOrders()->avg('total_cost');
    }
}
