<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceScheduleController extends Controller
{
    /**
     * Get all maintenance schedules with filtering
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view', MaintenanceSchedule::class);

        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);
        $testerId = $request->query('tester_id');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = MaintenanceSchedule::with('tester');

        if ($testerId) {
            $query->where('tester_id', $testerId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('scheduled_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('scheduled_date', '<=', $endDate);
        }

        $total = $query->count();
        $schedules = $query->forPage($page, $perPage)->get()->map(fn($s) => [
            'id' => $s->id,
            'tester_id' => $s->tester_id,
            'tester_model' => $s->tester?->model,
            'scheduled_date' => $s->scheduled_date,
            'status' => $s->status,
            'procedure' => $s->procedure,
            'notes' => $s->notes,
            'created_at' => $s->created_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance schedule list retrieved successfully',
            'data' => [
                'items' => $schedules,
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
     * Create a new maintenance schedule
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', MaintenanceSchedule::class);

        $validated = $request->validate([
            'tester_id' => 'required|exists:testers,id',
            'scheduled_date' => 'required|date',
            'procedure' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'pending';
        $schedule = MaintenanceSchedule::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance schedule created successfully',
            'data' => $schedule,
            'code' => 201,
        ], 201);
    }

    /**
     * Show a specific maintenance schedule
     */
    public function show(MaintenanceSchedule $schedule): JsonResponse
    {
        $this->authorize('view', $schedule);

        $schedule->load('tester');

        return response()->json([
            'success' => true,
            'message' => 'Maintenance schedule details retrieved successfully',
            'data' => $schedule,
            'code' => 200,
        ]);
    }

    /**
     * Update a maintenance schedule
     */
    public function update(Request $request, MaintenanceSchedule $schedule): JsonResponse
    {
        $this->authorize('update', $schedule);

        $validated = $request->validate([
            'scheduled_date' => 'date',
            'procedure' => 'string',
            'notes' => 'nullable|string',
        ]);

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance schedule updated successfully',
            'data' => $schedule,
            'code' => 200,
        ]);
    }

    /**
     * Complete a maintenance task
     */
    public function complete(Request $request, MaintenanceSchedule $schedule): JsonResponse
    {
        $this->authorize('update', $schedule);

        $validated = $request->validate([
            'completed_date' => 'required|date',
            'performed_by' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $schedule->update([
            'status' => 'completed',
            'completed_date' => $validated['completed_date'],
            'performed_by' => $validated['performed_by'],
            'notes' => $validated['notes'] ?? $schedule->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance task marked as completed',
            'data' => $schedule,
            'code' => 200,
        ]);
    }

    /**
     * Delete a maintenance schedule
     */
    public function destroy(MaintenanceSchedule $schedule): JsonResponse
    {
        $this->authorize('delete', $schedule);

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance schedule deleted successfully',
            'code' => 200,
        ]);
    }
}
