<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ListTesterRequest;
use App\Http\Requests\Api\StoreTesterRequest;
use App\Http\Requests\Api\UpdateTesterRequest;
use App\Http\Requests\Api\UpdateTesterStatusRequest;
use App\Models\Tester;
use Illuminate\Http\JsonResponse;

class TesterController extends ApiController
{
    public function index(ListTesterRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Tester::class);

        $validated = $request->validated();
        $query = Tester::query()->with('customer:id,name');

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['customer_id'])) {
            $query->where('customer_id', $validated['customer_id']);
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('model', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $testers = $query
            ->orderByDesc('id')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        return $this->paginated('Testers retrieved successfully', $testers);
    }

    public function store(StoreTesterRequest $request): JsonResponse
    {
        $this->authorize('create', Tester::class);

        $tester = Tester::create($request->validated());

        return $this->success('Tester created successfully', $tester->load('customer:id,name'), 201);
    }

    public function show(Tester $tester): JsonResponse
    {
        $this->authorize('view', $tester);

        return $this->success('Tester retrieved successfully', $tester->load('customer:id,name'));
    }

    public function update(UpdateTesterRequest $request, Tester $tester): JsonResponse
    {
        $this->authorize('update', $tester);

        $tester->update($request->validated());

        return $this->success('Tester updated successfully', $tester->fresh()->load('customer:id,name'));
    }

    public function destroy(Tester $tester): JsonResponse
    {
        $this->authorize('delete', $tester);

        $tester->delete();

        return $this->success('Tester deleted successfully');
    }

    public function updateStatus(UpdateTesterStatusRequest $request, Tester $tester): JsonResponse
    {
        $this->authorize('updateStatus', $tester);

        $tester->update([
            'status' => $request->validated()['status'],
        ]);

        return $this->success('Tester status updated successfully', $tester->fresh()->load('customer:id,name'));
    }
}
