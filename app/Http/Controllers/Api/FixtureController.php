<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ListFixtureRequest;
use App\Http\Requests\Api\StoreFixtureRequest;
use App\Http\Requests\Api\UpdateFixtureRequest;
use App\Models\Fixture;
use Illuminate\Http\JsonResponse;

class FixtureController extends ApiController
{
    public function index(ListFixtureRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Fixture::class);

        $validated = $request->validated();
        $query = Fixture::query()->with('tester:id,model,serial_number');

        if (! empty($validated['tester_id'])) {
            $query->where('tester_id', $validated['tester_id']);
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $fixtures = $query
            ->orderByDesc('id')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        return $this->paginated('Fixtures retrieved successfully', $fixtures);
    }

    public function store(StoreFixtureRequest $request): JsonResponse
    {
        $this->authorize('create', Fixture::class);

        $fixture = Fixture::create($request->validated());

        return $this->success('Fixture created successfully', $fixture->load('tester:id,model,serial_number'), 201);
    }

    public function show(Fixture $fixture): JsonResponse
    {
        $this->authorize('view', $fixture);

        return $this->success('Fixture retrieved successfully', $fixture->load('tester:id,model,serial_number'));
    }

    public function update(UpdateFixtureRequest $request, Fixture $fixture): JsonResponse
    {
        $this->authorize('update', $fixture);

        $fixture->update($request->validated());

        return $this->success('Fixture updated successfully', $fixture->fresh()->load('tester:id,model,serial_number'));
    }

    public function destroy(Fixture $fixture): JsonResponse
    {
        $this->authorize('delete', $fixture);

        $fixture->delete();

        return $this->success('Fixture deleted successfully');
    }
}
