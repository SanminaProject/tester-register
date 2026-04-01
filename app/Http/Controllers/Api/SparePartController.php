<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SparePart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    /**
     * Get all spare parts with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view', SparePart::class);

        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);
        $search = $request->query('search', '');
        $stockStatus = $request->query('stock_status');

        $query = SparePart::query();

        if ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        if ($stockStatus) {
            if ($stockStatus === 'low') {
                $query->where('quantity_in_stock', '<=', 5);
            } elseif ($stockStatus === 'normal') {
                $query->whereBetween('quantity_in_stock', [6, 20]);
            } elseif ($stockStatus === 'full') {
                $query->where('quantity_in_stock', '>', 20);
            }
        }

        $total = $query->count();
        $parts = $query->forPage($page, $perPage)->get()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'part_number' => $p->part_number,
            'quantity_in_stock' => $p->quantity_in_stock,
            'unit_cost' => $p->unit_cost,
            'supplier' => $p->supplier,
            'stock_status' => $p->stock_status,
            'created_at' => $p->created_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Spare parts list retrieved successfully',
            'data' => [
                'items' => $parts,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => $total,
                    'total_pages' => (int)ceil($total / $perPage),
                ],
            ],
            'code' => 200,
        ]);
    }

    /**
     * Create a new spare part
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', SparePart::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'part_number' => 'required|string|unique:spare_parts',
            'quantity_in_stock' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'supplier' => 'nullable|string',
        ]);

        $part = SparePart::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Spare part created successfully',
            'data' => [
                'id' => $part->id,
                'name' => $part->name,
                'part_number' => $part->part_number,
                'quantity_in_stock' => $part->quantity_in_stock,
                'unit_cost' => $part->unit_cost,
                'supplier' => $part->supplier,
                'stock_status' => $part->stock_status,
                'created_at' => $part->created_at,
            ],
            'code' => 201,
        ], 201);
    }

    /**
     * Show a specific spare part
     */
    public function show(SparePart $part): JsonResponse
    {
        $this->authorize('view', $part);

        return response()->json([
            'success' => true,
            'message' => 'Spare part details retrieved successfully',
            'data' => [
                'id' => $part->id,
                'name' => $part->name,
                'part_number' => $part->part_number,
                'quantity_in_stock' => $part->quantity_in_stock,
                'unit_cost' => $part->unit_cost,
                'supplier' => $part->supplier,
                'stock_status' => $part->stock_status,
                'created_at' => $part->created_at,
                'updated_at' => $part->updated_at,
            ],
            'code' => 200,
        ]);
    }

    /**
     * Update a spare part
     */
    public function update(Request $request, SparePart $part): JsonResponse
    {
        $this->authorize('update', $part);

        $validated = $request->validate([
            'name' => 'string',
            'part_number' => 'string|unique:spare_parts,part_number,' . $part->id,
            'quantity_in_stock' => 'integer|min:0',
            'unit_cost' => 'numeric|min:0',
            'supplier' => 'nullable|string',
        ]);

        $part->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Spare part updated successfully',
            'data' => [
                'id' => $part->id,
                'name' => $part->name,
                'part_number' => $part->part_number,
                'quantity_in_stock' => $part->quantity_in_stock,
                'unit_cost' => $part->unit_cost,
                'supplier' => $part->supplier,
                'stock_status' => $part->stock_status,
                'updated_at' => $part->updated_at,
            ],
            'code' => 200,
        ]);
    }

    /**
     * Delete a spare part
     */
    public function destroy(SparePart $part): JsonResponse
    {
        $this->authorize('delete', $part);

        $part->delete();

        return response()->json([
            'success' => true,
            'message' => 'Spare part deleted successfully',
            'code' => 200,
        ]);
    }
}
