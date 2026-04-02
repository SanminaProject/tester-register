<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ListSparePartRequest;
use App\Http\Requests\Api\StoreSparePartRequest;
use App\Http\Requests\Api\UpdateSparePartRequest;
use App\Models\SparePart;
use Illuminate\Http\JsonResponse;

class SparePartController extends ApiController
{
    public function index(ListSparePartRequest $request): JsonResponse
    {
        $this->authorize('viewAny', SparePart::class);

        $validated = $request->validated();
        $query = SparePart::query();

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        if (! empty($validated['stock_status'])) {
            if ($validated['stock_status'] === 'low') {
                $query->where('quantity_in_stock', '<=', 5);
            }

            if ($validated['stock_status'] === 'normal') {
                $query->whereBetween('quantity_in_stock', [6, 20]);
            }

            if ($validated['stock_status'] === 'full') {
                $query->where('quantity_in_stock', '>', 20);
            }
        }

        $parts = $query
            ->orderBy('name')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        return $this->paginated('Spare parts retrieved successfully', $parts);
    }

    public function store(StoreSparePartRequest $request): JsonResponse
    {
        $this->authorize('create', SparePart::class);

        $part = SparePart::create($request->validated());

        return $this->success('Spare part created successfully', $part, 201);
    }

    public function show(SparePart $sparePart): JsonResponse
    {
        $this->authorize('view', $sparePart);

        return $this->success('Spare part retrieved successfully', $sparePart);
    }

    public function update(UpdateSparePartRequest $request, SparePart $sparePart): JsonResponse
    {
        $this->authorize('update', $sparePart);

        $sparePart->update($request->validated());

        return $this->success('Spare part updated successfully', $sparePart->fresh());
    }

    public function destroy(SparePart $sparePart): JsonResponse
    {
        $this->authorize('delete', $sparePart);

        $sparePart->delete();

        return $this->success('Spare part deleted successfully');
    }
}
