<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\FuelLog;
use App\Models\Part;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard overview data
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get metrics
        $metrics = $this->getMetrics($user);

        // Get charts data
        $charts = $this->getChartsData($user);

        // Get alerts
        $alerts = $this->getAlerts($user);

        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $metrics,
                'charts' => $charts,
                'alerts' => $alerts,
                'recent_activities' => $recentActivities,
            ],
        ]);
    }

    /**
     * Get key metrics for dashboard
     */
    private function getMetrics($user)
    {
        $vehiclesQuery = Vehicle::query();
        $workOrdersQuery = WorkOrder::query();
        $fuelLogsQuery = FuelLog::query();

        // Filter based on user role
        if ($user->isDriver()) {
            $vehicleIds = $user->vehicleAssignments()
                ->where('status', 'Active')
                ->pluck('vehicle_id');
            $vehiclesQuery->whereIn('id', $vehicleIds);
            $workOrdersQuery->whereIn('vehicle_id', $vehicleIds);
            $fuelLogsQuery->whereIn('vehicle_id', $vehicleIds);
        }

        return [
            'total_vehicles' => $vehiclesQuery->count(),
            'active_vehicles' => $vehiclesQuery->where('status', 'Active')->count(),
            'vehicles_in_service' => $vehiclesQuery->where('status', 'In Service')->count(),
            'total_work_orders' => $workOrdersQuery->count(),
            'pending_work_orders' => $workOrdersQuery->where('status', 'Pending')->count(),
            'in_progress_work_orders' => $workOrdersQuery->where('status', 'In Progress')->count(),
            'monthly_fuel_cost' => (float) $fuelLogsQuery->whereMonth('date', now()->month)->sum('total_cost'),
            'monthly_maintenance_cost' => (float) $workOrdersQuery->whereMonth('created_at', now()->month)->sum('total_cost'),
        ];
    }

    /**
     * Get charts data
     */
    private function getChartsData($user)
    {
        // Maintenance cost by vehicle(last 6 months)
        $maintenanceCostByVehicle = WorkOrder::select('vehicle_id', DB::raw('SUM(total_cost) as total'))
            ->with('vehicle:id,plate_number')
            ->where('status', 'Completed')
            ->whereDate('completed_at', '>=', now()->subMonths(6))
            ->groupBy('vehicle_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'vehicle' => $item->vehicle->plate_number,
                    'cost' => (float) $item->total,
                ];
            });

        // Monthly fuel consumption
        $fuelConsumption = FuelLog::select(
                DB::raw('DATE_FORMAT(date, "%Y-%m") as month'),
                DB::raw('SUM(total_cost) as total')
            )
            ->whereDate('date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'cost' => (float) $item->total,
                ];
            });

        return [
            'maintenance_cost_by_vehicle' => $maintenanceCostByVehicle,
            'fuel_consumption' => $fuelConsumption,
        ];
    }

    /**
     * Get alerts (expiring documents, upcoming services, etc.)
     */
    private function getAlerts($user)
    {
        $alerts = [];

        // Expiring documents
        $expiringDocs = Document::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->with('documentable')
            ->get();

        foreach ($expiringDocs as $doc) {
            $alerts[] = [
                'type' => 'expiring_document',
                'severity' => 'warning',
                'message' => "{$doc->type} for {$doc->documentable_type} expires on {$doc->expiry_date->format('Y-m-d')}",
                'date' => $doc->expiry_date,
            ];
        }

        // Low stock parts
        $lowStockParts = Part::whereRaw('quantity_in_stock <= minimum_stock_level')->get();

        foreach ($lowStockParts as $part) {
            $alerts[] = [
                'type' => 'low_stock',
                'severity' => 'warning',
                'message' => "Part {$part->name} is low on stock ({$part->quantity_in_stock} remaining)",
                'part_id' => $part->id,
            ];
        }

        // Pending high priority work orders
        $highPriorityOrders = WorkOrder::whereIn('priority', ['High', 'Critical'])
            ->where('status', 'Pending')
            ->with('vehicle')
            ->get();

        foreach ($highPriorityOrders as $order) {
            $alerts[] = [
                'type' => 'high_priority_work_order',
                'severity' => 'urgent',
                'message' => "{$order->priority} priority work order for {$order->vehicle->plate_number}",
                'work_order_id' => $order->id,
            ];
        }

        return $alerts;
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($user)
    {
        $activities = [];

        // Recent work orders
        $recentWorkOrders = WorkOrder::with(['vehicle', 'technician'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentWorkOrders as $order) {
            $activities[] = [
                'type' => 'work_order',
                'message' => "Work order {$order->work_order_number} created for {$order->vehicle->plate_number}",
                'timestamp' => $order->created_at,
                'status' => $order->status,
            ];
        }

        return collect($activities)->sortByDesc('timestamp')->values();
    }

    /**
     * Get vehicle locations for map
     */
    public function getVehicleLocations(Request $request)
    {
        $vehicles = Vehicle::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('currentAssignment.driver')
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'plate_number' => $vehicle->plate_number,
                    'latitude' => (float) $vehicle->latitude,
                    'longitude' => (float) $vehicle->longitude,
                    'status' => $vehicle->status,
                    'driver' => $vehicle->currentAssignment?->driver->name ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'vehicles' => $vehicles,
        ]);
    }
}
