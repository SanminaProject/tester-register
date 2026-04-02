<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ListEventLogRequest;
use App\Http\Requests\Api\StoreEventLogRequest;
use App\Models\EventLog;
use Illuminate\Http\JsonResponse;

class EventLogController extends ApiController
{
    public function index(ListEventLogRequest $request): JsonResponse
    {
        $this->authorize('viewAny', EventLog::class);

        $validated = $request->validated();
        $query = EventLog::query()->with('tester:id,model,serial_number');

        if (! empty($validated['tester_id'])) {
            $query->where('tester_id', $validated['tester_id']);
        }

        if (! empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (! empty($validated['start_date'])) {
            $query->whereDate('event_date', '>=', $validated['start_date']);
        }

        if (! empty($validated['end_date'])) {
            $query->whereDate('event_date', '<=', $validated['end_date']);
        }

        $eventLogs = $query
            ->orderByDesc('event_date')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        return $this->paginated('Event logs retrieved successfully', $eventLogs);
    }

    public function store(StoreEventLogRequest $request): JsonResponse
    {
        $this->authorize('create', EventLog::class);

        $validated = $request->validated();

        if (empty($validated['performed_by'])) {
            $validated['performed_by'] = $request->user()?->name;
        }

        $eventLog = EventLog::create($validated);

        return $this->success('Event log created successfully', $eventLog->load('tester:id,model,serial_number'), 201);
    }

    public function show(EventLog $eventLog): JsonResponse
    {
        $this->authorize('view', $eventLog);

        return $this->success('Event log retrieved successfully', $eventLog->load('tester:id,model,serial_number'));
    }
}
