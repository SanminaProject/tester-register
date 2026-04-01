<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListSparePartRequest;
use App\Http\Requests\Api\StoreSparePartRequest;
use App\Http\Requests\Api\UpdateSparePartRequest;
use App\Models\SparePart;
use Illuminate\Http\JsonResponse;

class SparePartController extends Controller
{
    /**
     * Get all spare parts with pagination
     */
    public function index(ListSparePartRequest $request): JsonResponse
    {
        $this->authorize('view', SparePart::class);

        $validated = $request->validated();
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 15);
        $search = (string) ($validated['search'] ?? '');
        $stockStatus = $validated['stock_status'] ?? null;

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
                    'current_page' => $page,
                    'per_page' => $perPage,
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
    public function store(StoreSparePartRequest $request): JsonResponse
    {
        $this->authorize('create', SparePart::class);

        $part = SparePart::create($request->validated());

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
    public function update(UpdateSparePartRequest $request, SparePart $part): JsonResponse
    {
        $this->authorize('update', $part);

        $part->update($request->validated());

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
