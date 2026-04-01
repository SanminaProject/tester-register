<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListCalibrationScheduleRequest;
use App\Http\Requests\Api\StoreCalibrationScheduleRequest;
use App\Http\Requests\Api\UpdateCalibrationScheduleRequest;
use App\Http\Requests\Api\CompleteCalibrationRequest;
use App\Models\CalibrationSchedule;
use Illuminate\Http\JsonResponse;

class CalibrationScheduleController extends Controller
{
    /**
     * Get all calibration schedules with filtering
     */
    public function index(ListCalibrationScheduleRequest $request): JsonResponse
    {
        $this->authorize('view', CalibrationSchedule::class);

        $validated = $request->validated();
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 15);
        $testerId = $validated['tester_id'] ?? null;
        $status = $validated['status'] ?? null;
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        $query = CalibrationSchedule::with('tester');

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
            'message' => 'Calibration schedule list retrieved successfully',
            'data' => [
                'items' => $schedules,
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
     * Create a new calibration schedule
     */
    public function store(StoreCalibrationScheduleRequest $request): JsonResponse
    {
        $this->authorize('create', CalibrationSchedule::class);

        $validated = $request->validated();
        $validated['status'] = 'pending';
        $schedule = CalibrationSchedule::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Calibration schedule created successfully',
            'data' => $schedule,
            'code' => 201,
        ], 201);
    }

    /**
     * Show a specific calibration schedule
     */
    public function show(CalibrationSchedule $schedule): JsonResponse
    {
        $this->authorize('view', $schedule);

        $schedule->load('tester');

        return response()->json([
            'success' => true,
            'message' => 'Calibration schedule details retrieved successfully',
            'data' => $schedule,
            'code' => 200,
        ]);
    }

    /**
     * Update a calibration schedule
     */
    public function update(UpdateCalibrationScheduleRequest $request, CalibrationSchedule $schedule): JsonResponse
    {
        $this->authorize('update', $schedule);

        $schedule->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Calibration schedule updated successfully',
            'data' => $schedule,
            'code' => 200,
        ]);
    }

    /**
     * Complete a calibration task
     */
    public function complete(CompleteCalibrationRequest $request, CalibrationSchedule $schedule): JsonResponse
    {
        $this->authorize('update', $schedule);

        $validated = $request->validated();
        $schedule->update([
            'status' => 'completed',
            'completed_date' => $validated['completed_date'],
            'performed_by' => $validated['performed_by'],
            'notes' => $validated['notes'] ?? $schedule->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Calibration task marked as completed',
            'data' => $schedule,
            'code' => 200,
        ]);
    }

    /**
     * Delete a calibration schedule
     */
    public function destroy(CalibrationSchedule $schedule): JsonResponse
    {
        $this->authorize('delete', $schedule);

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Calibration schedule deleted successfully',
            'code' => 200,
        ]);
    }
}
