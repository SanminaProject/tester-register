<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListTesterRequest;
use App\Http\Requests\Api\StoreTesterRequest;
use App\Http\Requests\Api\UpdateTesterRequest;
use App\Http\Requests\Api\UpdateTesterStatusRequest;
use App\Models\Tester;
use Illuminate\Http\JsonResponse;

class TesterController extends Controller
{
    /**
     * Get all testers with pagination and filtering
     */
    public function index(ListTesterRequest $request): JsonResponse
    {
        $this->authorize('view', Tester::class);

        $validated = $request->validated();

        $perPage = (int)($validated['per_page'] ?? 15);
        $status = $validated['status'] ?? null;
        $customerId = $validated['customer_id'] ?? null;
        $search = $validated['search'] ?? '';

        $query = Tester::with('customer');

        if ($status) {
            $query->where('status', $status);
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('model', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $testers = $query->paginate($perPage);

        $items = $testers->getCollection()->map(
            fn(Tester $tester) => $this->transformTester($tester, true, true)
        );

        return response()->json([
            'success' => true,
            'message' => 'Device list retrieved successfully',
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $testers->currentPage(),
                    'per_page' => $testers->perPage(),
                    'total' => $testers->total(),
                    'total_pages' => $testers->lastPage(),
                    'has_next' => $testers->hasMorePages(),
                ],
            ],
            'code' => 200,
        ]);
    }

    /**
     * Create a new tester
     */
    public function store(StoreTesterRequest $request): JsonResponse
    {
        $this->authorize('create', Tester::class);

        $validated = $request->validated();

        $tester = Tester::create($validated);
        $tester->load('customer');

        return response()->json([
            'success' => true,
            'message' => 'Device created successfully',
            'data' => $this->transformTester($tester, true),
            'code' => 201,
        ], 201);
    }

    /**
     * Get a specific tester with related data
     */
    public function show(Tester $tester): JsonResponse
    {
        $this->authorize('view', $tester);

        $tester->load(['customer', 'fixtures', 'maintenanceSchedules', 'eventLogs']);

        return response()->json([
            'success' => true,
            'message' => 'Device details retrieved successfully',
            'data' => array_merge($this->transformTester($tester, true, true), [
                'fixtures' => $tester->fixtures->map(fn($f) => [
                    'id' => $f->id,
                    'name' => $f->name,
                    'serial_number' => $f->serial_number,
                    'status' => $f->status,
                ]),
                'recent_events' => $tester->eventLogs->take(5)->map(fn($e) => [
                    'id' => $e->id,
                    'type' => $e->type,
                    'description' => $e->description,
                    'event_date' => $e->event_date,
                ]),
                'maintenance_schedules' => $tester->maintenanceSchedules->take(5)->map(fn($m) => [
                    'id' => $m->id,
                    'scheduled_date' => $m->scheduled_date,
                    'status' => $m->status,
                ]),
            ]),
            'code' => 200,
        ]);
    }

    /**
     * Update a tester
     */
    public function update(UpdateTesterRequest $request, Tester $tester): JsonResponse
    {
        $this->authorize('update', $tester);

        $validated = $request->validated();

        $tester->update($validated);
        $tester->load('customer');

        return response()->json([
            'success' => true,
            'message' => 'Device updated successfully',
            'data' => $this->transformTester($tester, false, true),
            'code' => 200,
        ]);
    }

    /**
     * Transform tester model to API response payload.
     */
    private function transformTester(
        Tester $tester,
        bool $includeCreatedAt = false,
        bool $includeUpdatedAt = false
    ): array {
        $data = [
            'id' => $tester->id,
            'model' => $tester->model,
            'serial_number' => $tester->serial_number,
            'customer_id' => $tester->customer_id,
            'customer_name' => $tester->customer?->company_name,
            'status' => $tester->status,
            'purchase_date' => $tester->purchase_date,
            'location' => $tester->location,
        ];

        if ($includeCreatedAt) {
            $data['created_at'] = $tester->created_at;
        }

        if ($includeUpdatedAt) {
            $data['updated_at'] = $tester->updated_at;
        }

        return $data;
    }

    /**
     * Update tester status
     */
    public function updateStatus(UpdateTesterStatusRequest $request, Tester $tester): JsonResponse
    {
        $this->authorize('update', $tester);

        $validated = $request->validated();

        $tester->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Device status updated successfully',
            'data' => [
                'id' => $tester->id,
                'status' => $tester->status,
                'updated_at' => $tester->updated_at,
            ],
            'code' => 200,
        ]);
    }

    /**
     * Delete a tester
     */
    public function destroy(Tester $tester): JsonResponse
    {
        $this->authorize('delete', $tester);

        $tester->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully',
            'code' => 200,
        ]);
    }
}
