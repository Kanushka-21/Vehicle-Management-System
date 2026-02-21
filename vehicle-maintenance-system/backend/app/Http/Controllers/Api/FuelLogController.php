<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuelLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuelLogController extends Controller
{
    /**
     * Display a listing of fuel logs
     */
    public function index(Request $request)
    {
        $query = FuelLog::with(['vehicle', 'driver']);

        // Filter based on user role
        if ($request->user()->isDriver()) {
            $query->where('driver_id', $request->user()->id);
        }

        // Filters
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->has('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Sort
        $sortField = $request->get('sort_by', 'date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 15);
        $fuelLogs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'fuel_logs' => $fuelLogs,
        ]);
    }

    /**
     * Store a newly created fuel log
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'exists:users,id'],
            'date' => ['required', 'date'],
            'odometer' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'in:Liters,Gallons'],
            'cost_per_unit' => ['required', 'numeric', 'min:0'],
            'fuel_card_number' => ['nullable', 'string'],
            'station' => ['nullable', 'string'],
            'tank_filled' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        // If driver is not specified, use current user if they're a driver
        if (!isset($validated['driver_id']) && $request->user()->isDriver()) {
            $validated['driver_id'] = $request->user()->id;
        }

        $fuelLog = FuelLog::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fuel log created successfully',
            'fuel_log' => $fuelLog->load(['vehicle', 'driver']),
        ], 201);
    }

    /**
     * Display the specified fuel log
     */
    public function show(FuelLog $fuelLog)
    {
        $fuelLog->load(['vehicle', 'driver']);

        return response()->json([
            'success' => true,
            'fuel_log' => $fuelLog,
        ]);
    }

    /**
     * Update the specified fuel log
     */
    public function update(Request $request, FuelLog $fuelLog)
    {
        $validated = $request->validate([
            'vehicle_id' => ['sometimes', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'exists:users,id'],
            'date' => ['sometimes', 'date'],
            'odometer' => ['sometimes', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'in:Liters,Gallons'],
            'cost_per_unit' => ['sometimes', 'numeric', 'min:0'],
            'fuel_card_number' => ['nullable', 'string'],
            'station' => ['nullable', 'string'],
            'tank_filled' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $fuelLog->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fuel log updated successfully',
            'fuel_log' => $fuelLog->load(['vehicle', 'driver']),
        ]);
    }

    /**
     * Remove the specified fuel log
     */
    public function destroy(FuelLog $fuelLog)
    {
        $fuelLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fuel log deleted successfully',
        ]);
    }

    /**
     * Get fuel statistics and reports
     */
    public function getStatistics(Request $request)
    {
        $query = FuelLog::query();

        // Apply filters
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $statistics = [
            'total_fuel_consumed' => $query->sum('quantity'),
            'total_cost' => $query->sum('total_cost'),
            'average_cost_per_unit' => $query->avg('cost_per_unit'),
            'average_fuel_economy' => $query->whereNotNull('fuel_economy')->avg('fuel_economy'),
            'number_of_fillups' => $query->count(),
        ];

        // Fuel consumption by vehicle
        $byVehicle = FuelLog::select('vehicle_id', 
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_cost) as total_cost'),
                DB::raw('AVG(fuel_economy) as avg_economy'))
            ->with('vehicle:id,plate_number')
            ->groupBy('vehicle_id')
            ->get();

        // Monthly trend
        $monthlyTrend = FuelLog::select(
                DB::raw('DATE_FORMAT(date, "%Y-%m") as month'),
                DB::raw('SUM(total_cost) as total_cost'),
                DB::raw('SUM(quantity) as total_quantity'))
            ->whereDate('date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'by_vehicle' => $byVehicle,
            'monthly_trend' => $monthlyTrend,
        ]);
    }

    /**
     * Get fuel economy report
     */
    public function getFuelEconomyReport(Request $request)
    {
        $query = FuelLog::with(['vehicle:id,plate_number,make,model'])
            ->select('vehicle_id', 
                DB::raw('AVG(fuel_economy) as avg_economy'),
                DB::raw('MIN(fuel_economy) as min_economy'),
                DB::raw('MAX(fuel_economy) as max_economy'))
            ->whereNotNull('fuel_economy');

        if ($request->has('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $report = $query->groupBy('vehicle_id')->get();

        return response()->json([
            'success' => true,
            'fuel_economy_report' => $report,
        ]);
    }
}
