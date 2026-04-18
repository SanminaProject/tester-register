<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CompleteMaintenanceRequest;
use App\Http\Requests\Api\ListMaintenanceScheduleRequest;
use App\Http\Requests\Api\StoreMaintenanceScheduleRequest;
use App\Http\Requests\Api\UpdateMaintenanceScheduleRequest;
use App\Models\TesterEventLog as EventLog;
use App\Models\TesterMaintenanceSchedule as MaintenanceSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaintenanceScheduleController extends ApiController
{
    public function index(ListMaintenanceScheduleRequest $request): JsonResponse
    {
        $this->authorize('viewAny', MaintenanceSchedule::class);

        $validated = $request->validated();
        $query = MaintenanceSchedule::query()->with('tester:id,name,id_number_by_customer');

        if (! empty($validated['tester_id'])) {
            $query->where('tester_id', $validated['tester_id']);
        }

        if (! empty($validated['status'])) {
            $status = strtolower((string) $validated['status']);

            if ($status === 'completed') {
                $query->whereNotNull('last_maintenance_date');
            } else {
                $statusId = $this->resolveScheduleStatusId($status, false);

                if ($statusId === null) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->where('maintenance_status', $statusId);
                }
            }
        }

        if (! empty($validated['start_date'])) {
            $query->whereDate('next_maintenance_due', '>=', $validated['start_date']);
        }

        if (! empty($validated['end_date'])) {
            $query->whereDate('next_maintenance_due', '<=', $validated['end_date']);
        }

        $schedules = $query
            ->orderByDesc('next_maintenance_due')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        $schedules->getCollection()->transform(
            fn(MaintenanceSchedule $schedule): array => $this->toLegacySchedulePayload($schedule)
        );

        return $this->paginated('Maintenance schedules retrieved successfully', $schedules);
    }

    public function store(StoreMaintenanceScheduleRequest $request): JsonResponse
    {
        $this->authorize('create', MaintenanceSchedule::class);

        $validated = $request->validated();
        $procedureId = $this->resolveMaintenanceProcedureId((string) $validated['procedure']);

        $schedule = MaintenanceSchedule::create([
            'schedule_created_date' => now(),
            'last_maintenance_date' => null,
            'next_maintenance_due' => Carbon::parse($validated['scheduled_date'])->endOfDay(),
            'tester_id' => $validated['tester_id'],
            'maintenance_id' => $procedureId,
            'maintenance_status' => $this->resolveScheduleStatusId('scheduled', false),
            'last_maintenance_by_user_id' => null,
            'next_maintenance_by_user_id' => $request->user()?->id,
        ]);

        $payload = $this->toLegacySchedulePayload($schedule->load('tester:id,name,id_number_by_customer'));

        if (! empty($validated['notes'])) {
            $payload['notes'] = $validated['notes'];
        }

        return $this->success('Maintenance schedule created successfully', $payload, 201);
    }

    public function show(MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $this->authorize('view', $maintenanceSchedule);

        return $this->success(
            'Maintenance schedule retrieved successfully',
            $this->toLegacySchedulePayload($maintenanceSchedule->load('tester:id,name,id_number_by_customer'))
        );
    }

    public function update(UpdateMaintenanceScheduleRequest $request, MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $this->authorize('update', $maintenanceSchedule);

        $validated = $request->validated();
        $payload = [];

        if (array_key_exists('tester_id', $validated)) {
            $payload['tester_id'] = $validated['tester_id'];
        }

        if (array_key_exists('scheduled_date', $validated) && ! empty($validated['scheduled_date'])) {
            $payload['next_maintenance_due'] = Carbon::parse($validated['scheduled_date'])->endOfDay();
        }

        if (array_key_exists('procedure', $validated) && ! empty($validated['procedure'])) {
            $payload['maintenance_id'] = $this->resolveMaintenanceProcedureId((string) $validated['procedure']);
        }

        if (array_key_exists('status', $validated) && ! empty($validated['status'])) {
            $status = strtolower((string) $validated['status']);

            if ($status === 'completed') {
                $completedStatusId = $this->resolveScheduleStatusId('completed', false);

                if ($completedStatusId !== null) {
                    $payload['maintenance_status'] = $completedStatusId;
                }

                if (! array_key_exists('completed_date', $validated) && $maintenanceSchedule->last_maintenance_date === null) {
                    $payload['last_maintenance_date'] = now();
                }
            } else {
                $payload['maintenance_status'] = $this->resolveScheduleStatusId($status);
            }
        }

        if (array_key_exists('completed_date', $validated)) {
            $payload['last_maintenance_date'] = $validated['completed_date']
                ? Carbon::parse($validated['completed_date'])->endOfDay()
                : null;
        }

        if (array_key_exists('performed_by', $validated) && ! empty($validated['performed_by'])) {
            $payload['last_maintenance_by_user_id'] = $this->resolveActorUserId(
                $request->user()?->id,
                (string) $validated['performed_by'],
            );
        }

        if ($payload !== []) {
            $maintenanceSchedule->update($payload);
        }

        $responsePayload = $this->toLegacySchedulePayload(
            $maintenanceSchedule->fresh()->load('tester:id,name,id_number_by_customer')
        );

        if (array_key_exists('notes', $validated)) {
            $responsePayload['notes'] = $validated['notes'];
        }

        return $this->success('Maintenance schedule updated successfully', $responsePayload);
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
        $completedAt = Carbon::parse($validated['completed_date'])->endOfDay();
        $actorUserId = $this->resolveActorUserId($request->user()?->id, (string) $validated['performed_by']);

        $schedulePayload = [
            'last_maintenance_date' => $completedAt,
            'last_maintenance_by_user_id' => $actorUserId,
        ];

        $completedStatusId = $this->resolveScheduleStatusId('completed', false);

        if ($completedStatusId !== null) {
            $schedulePayload['maintenance_status'] = $completedStatusId;
        }

        $maintenanceSchedule->update($schedulePayload);

        $procedureName = $this->resolveMaintenanceProcedureName($maintenanceSchedule->maintenance_id) ?? 'Maintenance';
        $description = sprintf('Maintenance completed: %s', $procedureName);

        if (! empty($validated['notes'])) {
            $description .= sprintf(' (Notes: %s)', $validated['notes']);
        }

        EventLog::create([
            'tester_id' => $maintenanceSchedule->tester_id,
            'event_type' => $this->resolveEventTypeId('maintenance'),
            'date' => $completedAt,
            'description' => $description,
            'created_by_user_id' => $actorUserId,
            'maintenance_schedule_id' => $maintenanceSchedule->id,
            'calibration_schedule_id' => null,
            'resolution_description' => $validated['notes'] ?? null,
            'resolved_date' => $completedAt,
            'resolved_by_user_id' => $actorUserId,
        ]);

        $responsePayload = $this->toLegacySchedulePayload(
            $maintenanceSchedule->fresh()->load('tester:id,name,id_number_by_customer')
        );
        $responsePayload['status'] = 'completed';
        $responsePayload['completed_date'] = Carbon::parse($validated['completed_date'])->toDateString();
        $responsePayload['performed_by'] = $validated['performed_by'];
        $responsePayload['notes'] = $validated['notes'] ?? null;

        return $this->success('Maintenance completed successfully', $responsePayload);
    }

    /**
     * @return array<string, mixed>
     */
    private function toLegacySchedulePayload(MaintenanceSchedule $schedule): array
    {
        $status = $this->resolveScheduleStatusName($schedule->maintenance_status);

        if ($schedule->last_maintenance_date !== null) {
            $status = 'completed';
        }

        if ($status === null && $schedule->next_maintenance_due !== null && Carbon::parse($schedule->next_maintenance_due)->isPast()) {
            $status = 'overdue';
        }

        return [
            'id' => $schedule->id,
            'tester_id' => $schedule->tester_id,
            'scheduled_date' => $this->toDateString($schedule->next_maintenance_due),
            'status' => $status ?? 'scheduled',
            'procedure' => $this->resolveMaintenanceProcedureName($schedule->maintenance_id),
            'completed_date' => $this->toDateString($schedule->last_maintenance_date),
            'performed_by' => $this->resolveUserDisplayName($schedule->last_maintenance_by_user_id),
            'notes' => null,
            'tester' => $this->toLegacyTesterPayload($schedule->tester),
        ];
    }

    private function resolveScheduleStatusId(string $status, bool $strict = true): ?int
    {
        $statusId = DB::table('schedule_statuses')
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($status))])
            ->value('id');

        if ($statusId === null && $strict) {
            throw ValidationException::withMessages([
                'status' => ['Unsupported status value.'],
            ]);
        }

        return $statusId !== null ? (int) $statusId : null;
    }

    private function resolveScheduleStatusName(?int $statusId): ?string
    {
        if ($statusId === null) {
            return null;
        }

        $statusName = DB::table('schedule_statuses')
            ->where('id', $statusId)
            ->value('name');

        return is_string($statusName) ? strtolower($statusName) : null;
    }

    private function resolveMaintenanceProcedureId(string $procedure): int
    {
        $normalizedProcedure = trim($procedure);

        $existingId = DB::table('tester_maintenance_procedures')
            ->whereRaw('LOWER(type) = ?', [strtolower($normalizedProcedure)])
            ->value('id');

        if ($existingId !== null) {
            return (int) $existingId;
        }

        $intervalUnitId = DB::table('procedure_interval_units')
            ->whereRaw('LOWER(name) = ?', ['months'])
            ->value('id')
            ?? DB::table('procedure_interval_units')->orderBy('id')->value('id');

        if ($intervalUnitId === null) {
            throw ValidationException::withMessages([
                'procedure' => ['Unable to map procedure because interval units are missing.'],
            ]);
        }

        return (int) DB::table('tester_maintenance_procedures')->insertGetId([
            'type' => $normalizedProcedure,
            'interval_value' => 6,
            'description' => null,
            'interval_unit' => $intervalUnitId,
        ]);
    }

    private function resolveMaintenanceProcedureName(?int $maintenanceId): ?string
    {
        if ($maintenanceId === null) {
            return null;
        }

        $procedureName = DB::table('tester_maintenance_procedures')
            ->where('id', $maintenanceId)
            ->value('type');

        return is_string($procedureName) ? $procedureName : null;
    }

    private function resolveEventTypeId(string $eventType): int
    {
        $eventTypeId = DB::table('event_types')
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($eventType))])
            ->value('id');

        if ($eventTypeId === null) {
            throw ValidationException::withMessages([
                'type' => ['Unsupported event type.'],
            ]);
        }

        return (int) $eventTypeId;
    }

    private function resolveActorUserId(?int $authenticatedUserId, ?string $performedBy): int
    {
        if ($authenticatedUserId !== null) {
            return $authenticatedUserId;
        }

        if ($performedBy !== null && trim($performedBy) !== '') {
            $normalized = strtolower(trim($performedBy));

            $userId = DB::table('users')
                ->whereRaw("LOWER(CONCAT(first_name, ' ', last_name)) = ?", [$normalized])
                ->orWhereRaw('LOWER(email) = ?', [$normalized])
                ->value('id');

            if ($userId !== null) {
                return (int) $userId;
            }
        }

        throw ValidationException::withMessages([
            'performed_by' => ['Unable to map performed_by to an existing user.'],
        ]);
    }

    private function resolveUserDisplayName(?int $userId): ?string
    {
        if ($userId === null) {
            return null;
        }

        $user = DB::table('users')
            ->select(['first_name', 'last_name'])
            ->where('id', $userId)
            ->first();

        if ($user === null) {
            return null;
        }

        $name = trim(((string) ($user->first_name ?? '')) . ' ' . ((string) ($user->last_name ?? '')));

        return $name !== '' ? $name : null;
    }

    private function toDateString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function toLegacyTesterPayload(mixed $tester): ?array
    {
        if ($tester === null) {
            return null;
        }

        return [
            'id' => $tester->id,
            'model' => $tester->name,
            'serial_number' => $tester->id_number_by_customer,
        ];
    }
}
