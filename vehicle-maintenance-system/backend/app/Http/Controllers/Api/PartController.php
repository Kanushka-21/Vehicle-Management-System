<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Http\Request;

class PartController extends Controller
{
    /**
     * Display a listing of parts
     */
    public function index(Request $request)
    {
        $query = Part::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('part_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('manufacturer')) {
            $query->where('manufacturer', $request->manufacturer);
        }

        if ($request->has('low_stock') && $request->low_stock) {
            $query->lowStock();
        }

        // Sort
        $sortField = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 15);
        $parts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'parts' => $parts,
        ]);
    }

    /**
     * Store a newly created part
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_number' => ['required', 'string', 'max:100', 'unique:parts'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'manufacturer' => ['nullable', 'string'],
            'quantity_in_stock' => ['required', 'integer', 'min:0'],
            'minimum_stock_level' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'location' => ['nullable', 'string'],
        ]);

        $part = Part::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Part created successfully',
            'part' => $part,
        ], 201);
    }

    /**
     * Display the specified part
     */
    public function show(Part $part)
    {
        $part->load('workOrders');

        return response()->json([
            'success' => true,
            'part' => $part,
        ]);
    }

    /**
     * Update the specified part
     */
    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'part_number' => ['sometimes', 'string', 'max:100', 'unique:parts,part_number,' . $part->id],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'manufacturer' => ['nullable', 'string'],
            'quantity_in_stock' => ['sometimes', 'integer', 'min:0'],
            'minimum_stock_level' => ['sometimes', 'integer', 'min:0'],
            'unit_price' => ['sometimes', 'numeric', 'min:0'],
            'location' => ['nullable', 'string'],
        ]);

        $part->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Part updated successfully',
            'part' => $part,
        ]);
    }

    /**
     * Remove the specified part
     */
    public function destroy(Part $part)
    {
        $part->delete();

        return response()->json([
            'success' => true,
            'message' => 'Part deleted successfully',
        ]);
    }

    /**
     * Get low stock parts (reorder alerts)
     */
    public function getLowStock()
    {
        $parts = Part::lowStock()->get();

        return response()->json([
            'success' => true,
            'low_stock_parts' => $parts,
        ]);
    }

    /**
     * Adjust stock quantity
     */
    public function adjustStock(Request $request, Part $part)
    {
        $validated = $request->validate([
            'adjustment' => ['required', 'integer'],
            'type' => ['required', 'in:increase,decrease,set'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldStock = $part->quantity_in_stock;

        switch ($validated['type']) {
            case 'increase':
                $part->increaseStock($validated['adjustment']);
                break;
            case 'decrease':
                $part->decreaseStock($validated['adjustment']);
                break;
            case 'set':
                $part->update(['quantity_in_stock' => $validated['adjustment']]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully',
            'old_stock' => $oldStock,
            'new_stock' => $part->quantity_in_stock,
            'part' => $part,
        ]);
    }

    /**
     * Get parts categories
     */
    public function getCategories()
    {
        $categories = Part::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * Get parts manufacturers
     */
    public function getManufacturers()
    {
        $manufacturers = Part::select('manufacturer')
            ->whereNotNull('manufacturer')
            ->distinct()
            ->pluck('manufacturer');

        return response()->json([
            'success' => true,
            'manufacturers' => $manufacturers,
        ]);
    }
}
