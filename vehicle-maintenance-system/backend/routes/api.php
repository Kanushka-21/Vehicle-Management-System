<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\PartController;
use App\Http\Controllers\Api\FuelLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/vehicle-locations', [DashboardController::class, 'getVehicleLocations']);

    // Vehicles (All roles except Driver can perform full CRUD, Driver can only view their assigned)
    Route::middleware(['role:Administrator,Fleet Manager'])->group(function () {
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);
    });
    
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
    Route::get('/vehicle-groups', [VehicleController::class, 'getGroups']);
    Route::put('/vehicles/{vehicle}/location', [VehicleController::class, 'updateLocation'])
        ->middleware(['role:Administrator,Fleet Manager']);

    // Work Orders
    // Admin & Fleet Manager: Full CRUD
    Route::middleware(['role:Administrator,Fleet Manager'])->group(function () {
        Route::post('/work-orders', [WorkOrderController::class, 'store']);
        Route::delete('/work-orders/{workOrder}', [WorkOrderController::class, 'destroy']);
        Route::post('/work-orders/{workOrder}/assign-technician', [WorkOrderController::class, 'assignTechnician']);
    });

    // Technician: Can update and add parts
    Route::middleware(['role:Administrator,Fleet Manager,Technician'])->group(function () {
        Route::put('/work-orders/{workOrder}', [WorkOrderController::class, 'update']);
        Route::patch('/work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus']);
        Route::post('/work-orders/{workOrder}/add-parts', [WorkOrderController::class, 'addParts']);
    });

    // All authenticated users can view and get statistics
    Route::get('/work-orders', [WorkOrderController::class, 'index']);
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show']);
    Route::get('/work-orders-statistics', [WorkOrderController::class, 'getStatistics']);

    // Parts Inventory
    // Admin & Fleet Manager: Full CRUD
    Route::middleware(['role:Administrator,Fleet Manager'])->group(function () {
        Route::post('/parts', [PartController::class, 'store']);
        Route::put('/parts/{part}', [PartController::class, 'update']);
        Route::delete('/parts/{part}', [PartController::class, 'destroy']);
        Route::post('/parts/{part}/adjust-stock', [PartController::class, 'adjustStock']);
    });

    // Technician can view and get low stock
    Route::middleware(['role:Administrator,Fleet Manager,Technician'])->group(function () {
        Route::get('/parts', [PartController::class, 'index']);
        Route::get('/parts/{part}', [PartController::class, 'show']);
        Route::get('/parts-low-stock', [PartController::class, 'getLowStock']);
        Route::get('/parts-categories', [PartController::class, 'getCategories']);
        Route::get('/parts-manufacturers', [PartController::class, 'getManufacturers']);
    });

    // Fuel Management
    // Admin & Fleet Manager: Full access
    Route::middleware(['role:Administrator,Fleet Manager'])->group(function () {
        Route::post('/fuel-logs', [FuelLogController::class, 'store']);
        Route::put('/fuel-logs/{fuelLog}', [FuelLogController::class, 'update']);
        Route::delete('/fuel-logs/{fuelLog}', [FuelLogController::class, 'destroy']);
        Route::get('/fuel-statistics', [FuelLogController::class, 'getStatistics']);
        Route::get('/fuel-economy-report', [FuelLogController::class, 'getFuelEconomyReport']);
    });

    // Driver can log fuel (create only)
    Route::middleware(['role:Driver'])->group(function () {
        Route::post('/fuel-logs', [FuelLogController::class, 'store']);
    });

    // All can view
    Route::get('/fuel-logs', [FuelLogController::class, 'index']);
    Route::get('/fuel-logs/{fuelLog}', [FuelLogController::class, 'show']);

    // User Management (Admin only)
    Route::middleware(['role:Administrator'])->group(function () {
        Route::get('/users', [AuthController::class, 'listUsers']);
        Route::put('/users/{user}', [AuthController::class, 'updateUser']);
        Route::delete('/users/{user}', [AuthController::class, 'deleteUser']);
    });

    // Driver Management (Admin & Fleet Manager)
    Route::middleware(['role:Administrator,Fleet Manager'])->group(function () {
        Route::get('/drivers', function () {
            $drivers = \App\Models\Driver::with(['user', 'currentAssignment.vehicle'])->get();
            return response()->json(['success' => true, 'drivers' => ['data' => $drivers]]);
        });
    });

});
