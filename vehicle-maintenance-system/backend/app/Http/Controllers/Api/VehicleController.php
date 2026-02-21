<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    /**
     * Display a listing of vehicles
     */
    public function index(Request $request)
    {
        $query = Vehicle::with(['currentAssignment.driver', 'documents']);

        // Filter based on user role
        if ($request->user()->isDriver()) {
            $vehicleIds = $request->user()->vehicleAssignments()
                ->where('status', 'Active')
                ->pluck('vehicle_id');
            $query->whereIn('id', $vehicleIds);
        }

        // Search and filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        if ($request->has('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        // Sort
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $vehicles = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Store a newly created vehicle
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vin' => ['required', 'string', 'size:17', 'unique:vehicles'],
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'engine_type' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['required', 'in:Petrol,Diesel,Electric,Hybrid'],
            'odometer' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'current_value' => ['nullable', 'numeric', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'group' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:Active,In Service,Out of Service,Sold,Retired'],
            'notes' => ['nullable', 'string'],
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created successfully',
            'vehicle' => $vehicle,
        ], 201);
    }

    /**
     * Display the specified vehicle
     */
    public function show(Request $request, Vehicle $vehicle)
    {
        // Check if driver can only view their assigned vehicles
        if ($request->user()->isDriver()) {
            $hasAccess = $request->user()->vehicleAssignments()
                ->where('vehicle_id', $vehicle->id)
                ->where('status', 'Active')
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this vehicle',
                ], 403);
            }
        }

        $vehicle->load([
            'workOrders' => function ($query) {
                $query->latest()->limit(10);
            },
            'workOrders.technician',
            'fuelLogs' => function ($query) {
                $query->latest()->limit(10);
            },
            'fuelLogs.driver',
            'assignments' => function ($query) {
                $query->latest()->limit(10);
            },
            'assignments.driver',
            'documents',
            'serviceSchedules',
        ]);

        return response()->json([
            'success' => true,
            'vehicle' => $vehicle,
            'total_maintenance_cost' => $vehicle->totalMaintenanceCost,
            'average_fuel_economy' => $vehicle->averageFuelEconomy,
        ]);
    }

    /**
     * Update the specified vehicle
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'vin' => ['sometimes', 'string', 'size:17', 'unique:vehicles,vin,' . $vehicle->id],
            'plate_number' => ['sometimes', 'string', 'max:20', 'unique:vehicles,plate_number,' . $vehicle->id],
            'year' => ['sometimes', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'make' => ['sometimes', 'string', 'max:100'],
            'model' => ['sometimes', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'engine_type' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['sometimes', 'in:Petrol,Diesel,Electric,Hybrid'],
            'odometer' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'current_value' => ['nullable', 'numeric', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'group' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'in:Active,In Service,Out of Service,Sold,Retired'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string'],
        ]);

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * Remove the specified vehicle
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully',
        ]);
    }

    /**
     * Get vehicle groups
     */
    public function getGroups()
    {
        $groups = Vehicle::select('group')
            ->whereNotNull('group')
            ->distinct()
            ->pluck('group');

        return response()->json([
            'success' => true,
            'groups' => $groups,
        ]);
    }

    /**
     * Update vehicle location
     */
    public function updateLocation(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle location updated',
        ]);
    }
}
