<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Part extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'part_number',
        'name',
        'description',
        'category',
        'manufacturer',
        'quantity_in_stock',
        'minimum_stock_level',
        'unit_price',
        'location',
    ];

    protected $casts = [
        'quantity_in_stock' => 'integer',
        'minimum_stock_level' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    // Relationships
    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_parts')
            ->withPivot('quantity_used', 'unit_price', 'total_price')
            ->withTimestamps();
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_in_stock <= minimum_stock_level');
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity_in_stock', '>', 0);
    }

    // Accessors
    public function getStockStatusAttribute()
    {
        if ($this->quantity_in_stock == 0) {
            return 'Out of Stock';
        } elseif ($this->quantity_in_stock <= $this->minimum_stock_level) {
            return 'Low Stock';
        }
        
        return 'In Stock';
    }

    // Methods
    public function decreaseStock($quantity)
    {
        $this->quantity_in_stock -= $quantity;
        $this->save();
    }

    public function increaseStock($quantity)
    {
        $this->quantity_in_stock += $quantity;
        $this->save();
    }
}
