<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Part;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of work orders
     */
    public function index(Request $request)
    {
        $query = WorkOrder::with(['vehicle', 'technician', 'vendor', 'parts']);

        // Filter based on user role
        if ($request->user()->isTechnician()) {
            $query->where('technician_id', $request->user()->id);
        } elseif ($request->user()->isDriver()) {
            $vehicleIds = $request->user()->vehicleAssignments()
                ->where('status', 'Active')
                ->pluck('vehicle_id');
            $query->whereIn('vehicle_id', $vehicleIds);
        }

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        // Date range filter
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sort
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 15);
        $workOrders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'work_orders' => $workOrders,
        ]);
    }

    /**
     * Store a newly created work order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'type' => ['required', 'in:Preventative,Repair,Inspection,Emergency'],
            'priority' => ['required', 'in:Low,Medium,High,Critical'],
            'description' => ['required', 'string'],
            'odometer_reading' => ['nullable', 'numeric'],
            'scheduled_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['status'] = 'Pending';
        $workOrder = WorkOrder::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Work order created successfully',
            'work_order' => $workOrder->load(['vehicle', 'technician', 'vendor']),
        ], 201);
    }

    /**
     * Display the specified work order
     */
    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['vehicle', 'technician', 'vendor', 'parts']);

        return response()->json([
            'success' => true,
            'work_order' => $workOrder,
        ]);
    }

    /**
     * Update the specified work order
     */
    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'vehicle_id' => ['sometimes', 'exists:vehicles,id'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'type' => ['sometimes', 'in:Preventative,Repair,Inspection,Emergency'],
            'status' => ['sometimes', 'in:Pending,In Progress,On Hold,Completed,Cancelled'],
            'priority' => ['sometimes', 'in:Low,Medium,High,Critical'],
            'description' => ['sometimes', 'string'],
            'odometer_reading' => ['nullable', 'numeric'],
            'labor_hours' => ['nullable', 'numeric', 'min:0'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'scheduled_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        // Auto-set timestamps based on status changes
        if (isset($validated['status'])) {
            if ($validated['status'] === 'In Progress' && $workOrder->status !== 'In Progress') {
                $validated['started_at'] = now();
            } elseif ($validated['status'] === 'Completed' && $workOrder->status !== 'Completed') {
                $validated['completed_at'] = now();
                
                // Calculate downtime
                if ($workOrder->started_at) {
                    $validated['downtime_hours'] = $workOrder->started_at->diffInHours(now());
                }
            }
        }

        $workOrder->update($validated);
        $workOrder->calculateTotalCost();

        return response()->json([
            'success' => true,
            'message' => 'Work order updated successfully',
            'work_order' => $workOrder->load(['vehicle', 'technician', 'vendor', 'parts']),
        ]);
    }

    /**
     * Remove the specified work order
     */
    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work order deleted successfully',
        ]);
    }

    /**
     * Assign technician to work order
     */
    public function assignTechnician(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'technician_id' => ['required', 'exists:users,id'],
        ]);

        $workOrder->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Technician assigned successfully',
            'work_order' => $workOrder->load('technician'),
        ]);
    }

    /**
     * Add parts to work order
     */
    public function addParts(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'parts' => ['required', 'array'],
            'parts.*.part_id' => ['required', 'exists:parts,id'],
            'parts.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $totalPartsCost = 0;

        foreach ($validated['parts'] as $partData) {
            $part = Part::find($partData['part_id']);
            
            // Check if enough stock
            if ($part->quantity_in_stock < $partData['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for part: {$part->name}",
                ], 400);
            }

            $unitPrice = $part->unit_price;
            $totalPrice = $unitPrice * $partData['quantity'];
            $totalPartsCost += $totalPrice;

            // Attach part to work order
            $workOrder->parts()->attach($partData['part_id'], [
                'quantity_used' => $partData['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ]);

            // Decrease stock
            $part->decreaseStock($partData['quantity']);
        }

        // Update parts cost
        $workOrder->update([
            'parts_cost' => $workOrder->parts_cost + $totalPartsCost,
        ]);
        $workOrder->calculateTotalCost();

        return response()->json([
            'success' => true,
            'message' => 'Parts added successfully',
            'work_order' => $workOrder->load('parts'),
        ]);
    }

    /**
     * Get work order statistics
     */
    public function getStatistics(Request $request)
    {
        $query = WorkOrder::query();

        // Apply date range if provided
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return response()->json([
            'success' => true,
            'statistics' => [
                'total' => $query->count(),
                'pending' => (clone $query)->where('status', 'Pending')->count(),
                'in_progress' => (clone $query)->where('status', 'In Progress')->count(),
                'completed' => (clone $query)->where('status', 'Completed')->count(),
                'total_cost' => (clone $query)->where('status', 'Completed')->sum('total_cost'),
                'average_cost' => (clone $query)->where('status', 'Completed')->avg('total_cost'),
                'average_downtime' => (clone $query)->where('status', 'Completed')->avg('downtime_hours'),
            ],
        ]);
    }

    /**
     * Update work order status
     */
    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Pending,In Progress,On Hold,Completed'],
        ]);

        $workOrder->update($validated);

        // If completed, set completion date
        if ($validated['status'] === 'Completed' && !$workOrder->completed_at) {
            $workOrder->update(['completed_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Work order status updated successfully',
            'work_order' => $workOrder,
        ]);
    }
}
