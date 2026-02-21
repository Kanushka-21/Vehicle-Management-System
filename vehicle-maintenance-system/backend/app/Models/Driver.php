<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'license_number',
        'license_class',
        'license_expiry',
        'emergency_contact_name',
        'emergency_contact_phone',
        'certifications',
        'training_records',
    ];

    protected $casts = [
        'license_expiry' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // Accessors
    public function getLicenseExpiryStatusAttribute()
    {
        $daysUntilExpiry = now()->diffInDays($this->license_expiry, false);
        
        if ($daysUntilExpiry < 0) {
            return 'Expired';
        } elseif ($daysUntilExpiry <= 30) {
            return 'Expiring Soon';
        }
        
        return 'Valid';
    }
}
