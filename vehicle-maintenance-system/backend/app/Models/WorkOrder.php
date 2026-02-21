<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'technician_id',
        'vendor_id',
        'work_order_number',
        'type',
        'status',
        'priority',
        'description',
        'odometer_reading',
        'labor_hours',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'scheduled_date',
        'started_at',
        'completed_at',
        'downtime_hours',
        'notes',
    ];

    protected $casts = [
        'odometer_reading' => 'decimal:2',
        'labor_hours' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'scheduled_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'downtime_hours' => 'integer',
    ];

    // Boot method to auto-generate work order number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($workOrder) {
            if (empty($workOrder->work_order_number)) {
                $workOrder->work_order_number = 'WO-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'work_order_parts')
            ->withPivot('quantity_used', 'unit_price', 'total_price')
            ->withTimestamps();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'In Progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['High', 'Critical']);
    }

    // Methods
    public function calculateTotalCost()
    {
        $this->total_cost = $this->labor_cost + $this->parts_cost;
        $this->save();
    }

    public function calculateDowntime()
    {
        if ($this->started_at && $this->completed_at) {
            $this->downtime_hours = $this->started_at->diffInHours($this->completed_at);
            $this->save();
        }
    }
}
