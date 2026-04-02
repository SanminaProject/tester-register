<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CompleteCalibrationRequest;
use App\Http\Requests\Api\ListCalibrationScheduleRequest;
use App\Http\Requests\Api\StoreCalibrationScheduleRequest;
use App\Http\Requests\Api\UpdateCalibrationScheduleRequest;
use App\Models\CalibrationSchedule;
use App\Models\EventLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class CalibrationScheduleController extends ApiController
{
    public function index(ListCalibrationScheduleRequest $request): JsonResponse
    {
        $this->authorize('viewAny', CalibrationSchedule::class);

        $validated = $request->validated();
        $query = CalibrationSchedule::query()->with('tester:id,model,serial_number');

        if (! empty($validated['tester_id'])) {
            $query->where('tester_id', $validated['tester_id']);
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['start_date'])) {
            $query->whereDate('scheduled_date', '>=', $validated['start_date']);
        }

        if (! empty($validated['end_date'])) {
            $query->whereDate('scheduled_date', '<=', $validated['end_date']);
        }

        $schedules = $query
            ->orderByDesc('scheduled_date')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        return $this->paginated('Calibration schedules retrieved successfully', $schedules);
    }

    public function store(StoreCalibrationScheduleRequest $request): JsonResponse
    {
        $this->authorize('create', CalibrationSchedule::class);

        $schedule = CalibrationSchedule::create($request->validated());

        return $this->success('Calibration schedule created successfully', $schedule->load('tester:id,model,serial_number'), 201);
    }

    public function show(CalibrationSchedule $calibrationSchedule): JsonResponse
    {
        $this->authorize('view', $calibrationSchedule);

        return $this->success('Calibration schedule retrieved successfully', $calibrationSchedule->load('tester:id,model,serial_number'));
    }

    public function update(UpdateCalibrationScheduleRequest $request, CalibrationSchedule $calibrationSchedule): JsonResponse
    {
        $this->authorize('update', $calibrationSchedule);

        $calibrationSchedule->update($request->validated());

        return $this->success('Calibration schedule updated successfully', $calibrationSchedule->fresh()->load('tester:id,model,serial_number'));
    }

    public function destroy(CalibrationSchedule $calibrationSchedule): JsonResponse
    {
        $this->authorize('delete', $calibrationSchedule);

        $calibrationSchedule->delete();

        return $this->success('Calibration schedule deleted successfully');
    }

    public function complete(CompleteCalibrationRequest $request, CalibrationSchedule $calibrationSchedule): JsonResponse
    {
        $this->authorize('complete', $calibrationSchedule);

        $validated = $request->validated();

        $calibrationSchedule->update([
            'status' => 'completed',
            'completed_date' => $validated['completed_date'],
            'performed_by' => $validated['performed_by'],
            'notes' => $validated['notes'] ?? $calibrationSchedule->notes,
        ]);

        EventLog::create([
            'tester_id' => $calibrationSchedule->tester_id,
            'type' => 'calibration',
            'event_date' => Carbon::parse($validated['completed_date'])->endOfDay(),
            'description' => sprintf('Calibration completed: %s', $calibrationSchedule->procedure),
            'performed_by' => $validated['performed_by'],
            'metadata' => [
                'calibration_schedule_id' => $calibrationSchedule->id,
            ],
        ]);

        return $this->success('Calibration completed successfully', $calibrationSchedule->fresh()->load('tester:id,model,serial_number'));
    }
}
