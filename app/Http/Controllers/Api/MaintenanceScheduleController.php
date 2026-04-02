<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CompleteMaintenanceRequest;
use App\Http\Requests\Api\ListMaintenanceScheduleRequest;
use App\Http\Requests\Api\StoreMaintenanceScheduleRequest;
use App\Http\Requests\Api\UpdateMaintenanceScheduleRequest;
use App\Models\EventLog;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class MaintenanceScheduleController extends ApiController
{
    public function index(ListMaintenanceScheduleRequest $request): JsonResponse
    {
        $this->authorize('viewAny', MaintenanceSchedule::class);

        $validated = $request->validated();
        $query = MaintenanceSchedule::query()->with('tester:id,model,serial_number');

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

        return $this->paginated('Maintenance schedules retrieved successfully', $schedules);
    }

    public function store(StoreMaintenanceScheduleRequest $request): JsonResponse
    {
        $this->authorize('create', MaintenanceSchedule::class);

        $schedule = MaintenanceSchedule::create($request->validated());

        return $this->success('Maintenance schedule created successfully', $schedule->load('tester:id,model,serial_number'), 201);
    }

    public function show(MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $this->authorize('view', $maintenanceSchedule);

        return $this->success('Maintenance schedule retrieved successfully', $maintenanceSchedule->load('tester:id,model,serial_number'));
    }

    public function update(UpdateMaintenanceScheduleRequest $request, MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $this->authorize('update', $maintenanceSchedule);

        $maintenanceSchedule->update($request->validated());

        return $this->success('Maintenance schedule updated successfully', $maintenanceSchedule->fresh()->load('tester:id,model,serial_number'));
    }

    public function destroy(MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $this->authorize('delete', $maintenanceSchedule);

        $maintenanceSchedule->delete();

        return $this->success('Maintenance schedule deleted successfully');
    }

    public function complete(CompleteMaintenanceRequest $request, MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $this->authorize('complete', $maintenanceSchedule);

        $validated = $request->validated();

        $maintenanceSchedule->update([
            'status' => 'completed',
            'completed_date' => $validated['completed_date'],
            'performed_by' => $validated['performed_by'],
            'notes' => $validated['notes'] ?? $maintenanceSchedule->notes,
        ]);

        EventLog::create([
            'tester_id' => $maintenanceSchedule->tester_id,
            'type' => 'maintenance',
            'event_date' => Carbon::parse($validated['completed_date'])->endOfDay(),
            'description' => sprintf('Maintenance completed: %s', $maintenanceSchedule->procedure),
            'performed_by' => $validated['performed_by'],
            'metadata' => [
                'maintenance_schedule_id' => $maintenanceSchedule->id,
            ],
        ]);

        return $this->success('Maintenance completed successfully', $maintenanceSchedule->fresh()->load('tester:id,model,serial_number'));
    }
}
