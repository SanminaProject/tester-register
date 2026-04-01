<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListFixtureRequest;
use App\Http\Requests\Api\StoreFixtureRequest;
use App\Http\Requests\Api\UpdateFixtureRequest;
use App\Models\Fixture;
use Illuminate\Http\JsonResponse;

class FixtureController extends Controller
{
    /**
     * Get all fixtures with pagination
     */
    public function index(ListFixtureRequest $request): JsonResponse
    {
        $this->authorize('view', Fixture::class);

        $validated = $request->validated();
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 15);
        $testerId = $validated['tester_id'] ?? null;
        $status = $validated['status'] ?? null;
        $search = (string) ($validated['search'] ?? '');

        $query = Fixture::with('tester');

        if ($testerId) {
            $query->where('tester_id', $testerId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $fixtures = $query->forPage($page, $perPage)->get()->map(fn($f) => [
            'id' => $f->id,
            'name' => $f->name,
            'serial_number' => $f->serial_number,
            'tester_id' => $f->tester_id,
            'tester_model' => $f->tester?->model,
            'status' => $f->status,
            'purchase_date' => $f->purchase_date,
            'created_at' => $f->created_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fixture list retrieved successfully',
            'data' => [
                'items' => $fixtures,
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
     * Create a new fixture
     */
    public function store(StoreFixtureRequest $request): JsonResponse
    {
        $this->authorize('create', Fixture::class);

        $validated = $request->validated();

        $fixture = Fixture::create($validated);
        $fixture->load('tester');

        return response()->json([
            'success' => true,
            'message' => 'Fixture created successfully',
            'data' => [
                'id' => $fixture->id,
                'name' => $fixture->name,
                'serial_number' => $fixture->serial_number,
                'tester_id' => $fixture->tester_id,
                'status' => $fixture->status,
                'purchase_date' => $fixture->purchase_date,
                'created_at' => $fixture->created_at,
            ],
            'code' => 201,
        ], 201);
    }

    /**
     * Show a specific fixture
     */
    public function show(Fixture $fixture): JsonResponse
    {
        $this->authorize('view', $fixture);

        $fixture->load('tester');

        return response()->json([
            'success' => true,
            'message' => 'Fixture details retrieved successfully',
            'data' => $fixture,
            'code' => 200,
        ]);
    }

    /**
     * Update a fixture
     */
    public function update(UpdateFixtureRequest $request, Fixture $fixture): JsonResponse
    {
        $this->authorize('update', $fixture);

        $fixture->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Fixture updated successfully',
            'data' => $fixture,
            'code' => 200,
        ]);
    }

    /**
     * Delete a fixture
     */
    public function destroy(Fixture $fixture): JsonResponse
    {
        $this->authorize('delete', $fixture);

        $fixture->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fixture deleted successfully',
            'code' => 200,
        ]);
    }
}
