<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Role constants
    const ROLE_ADMINISTRATOR = 'Administrator';
    const ROLE_FLEET_MANAGER = 'Fleet Manager';
    const ROLE_TECHNICIAN = 'Technician';
    const ROLE_DRIVER = 'Driver';

    // Role checking methods
    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMINISTRATOR;
    }

    public function isFleetManager(): bool
    {
        return $this->role === self::ROLE_FLEET_MANAGER;
    }

    public function isTechnician(): bool
    {
        return $this->role === self::ROLE_TECHNICIAN;
    }

    public function isDriver(): bool
    {
        return $this->role === self::ROLE_DRIVER;
    }

    // Relationships
    public function driverProfile()
    {
        return $this->hasOne(Driver::class);
    }

    public function workOrdersAsTechnician()
    {
        return $this->hasMany(WorkOrder::class, 'technician_id');
    }

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class, 'driver_id');
    }

    public function vehicleAssignments()
    {
        return $this->hasMany(VehicleAssignment::class, 'driver_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(VehicleAssignment::class, 'driver_id')
            ->where('status', 'Active')
            ->latest();
    }

    public function fuelCards()
    {
        return $this->hasMany(FuelCard::class, 'driver_id');
    }
}
