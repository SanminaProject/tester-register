<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreEventLogRequest;
use App\Models\EventLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Request is kept here for index() method

class EventLogController extends Controller
{
    /**
     * Get all event logs with filtering
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view', EventLog::class);

        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);
        $testerId = $request->query('tester_id');
        $type = $request->query('type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = EventLog::with('tester');

        if ($testerId) {
            $query->where('tester_id', $testerId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($startDate) {
            $query->whereDate('event_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('event_date', '<=', $endDate);
        }

        $total = $query->count();
        $logs = $query->latest('event_date')->forPage($page, $perPage)->get()->map(fn($l) => [
            'id' => $l->id,
            'tester_id' => $l->tester_id,
            'type' => $l->type,
            'description' => $l->description,
            'event_date' => $l->event_date,
            'recorded_by' => $l->recorded_by,
            'created_at' => $l->created_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event log list retrieved successfully',
            'data' => [
                'items' => $logs,
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
     * Create a new event log
     */
    public function store(StoreEventLogRequest $request): JsonResponse
    {
        $this->authorize('create', EventLog::class);

        $validated = $request->validated();
        $validated['recorded_by'] = $request->user()->name ?? 'System';
        $log = EventLog::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event log created successfully',
            'data' => [
                'id' => $log->id,
                'tester_id' => $log->tester_id,
                'type' => $log->type,
                'description' => $log->description,
                'event_date' => $log->event_date,
                'created_at' => $log->created_at,
            ],
            'code' => 201,
        ], 201);
    }

    /**
     * Show a specific event log
     */
    public function show(EventLog $log): JsonResponse
    {
        $this->authorize('view', $log);

        $log->load('tester');

        return response()->json([
            'success' => true,
            'message' => 'Event log details retrieved successfully',
            'data' => $log,
            'code' => 200,
        ]);
    }
}
