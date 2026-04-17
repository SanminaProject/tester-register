<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CompleteCalibrationRequest;
use App\Http\Requests\Api\ListCalibrationScheduleRequest;
use App\Http\Requests\Api\StoreCalibrationScheduleRequest;
use App\Http\Requests\Api\UpdateCalibrationScheduleRequest;
use App\Models\TesterCalibrationSchedule as CalibrationSchedule;
use App\Models\TesterEventLog as EventLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalibrationScheduleController extends ApiController
{
    public function index(ListCalibrationScheduleRequest $request): JsonResponse
    {
        $this->authorize('viewAny', CalibrationSchedule::class);

        $validated = $request->validated();
        $query = CalibrationSchedule::query()->with('tester:id,name,id_number_by_customer');

        if (! empty($validated['tester_id'])) {
            $query->where('tester_id', $validated['tester_id']);
        }

        if (! empty($validated['status'])) {
            $status = strtolower((string) $validated['status']);

            if ($status === 'completed') {
                $query->whereNotNull('last_calibration_date');
            } else {
                $statusId = $this->resolveScheduleStatusId($status, false);

                if ($statusId === null) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->where('calibration_status', $statusId);
                }
            }
        }

        if (! empty($validated['start_date'])) {
            $query->whereDate('next_calibration_due', '>=', $validated['start_date']);
        }

        if (! empty($validated['end_date'])) {
            $query->whereDate('next_calibration_due', '<=', $validated['end_date']);
        }

        $schedules = $query
            ->orderByDesc('next_calibration_due')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        $schedules->getCollection()->transform(
            fn (CalibrationSchedule $schedule): array => $this->toLegacySchedulePayload($schedule)
        );

        return $this->paginated('Calibration schedules retrieved successfully', $schedules);
    }

    public function store(StoreCalibrationScheduleRequest $request): JsonResponse
    {
        $this->authorize('create', CalibrationSchedule::class);

        $validated = $request->validated();
        $procedureId = $this->resolveCalibrationProcedureId((string) $validated['procedure']);

        $schedule = CalibrationSchedule::create([
            'schedule_created_date' => now(),
            'last_calibration_date' => null,
            'next_calibration_due' => Carbon::parse($validated['scheduled_date'])->endOfDay(),
            'tester_id' => $validated['tester_id'],
            'calibration_id' => $procedureId,
            'calibration_status' => $this->resolveScheduleStatusId('scheduled', false),
            'last_calibration_by_user_id' => null,
            'next_calibration_by_user_id' => $request->user()?->id,
        ]);

        $payload = $this->toLegacySchedulePayload($schedule->load('tester:id,name,id_number_by_customer'));

        if (! empty($validated['notes'])) {
            $payload['notes'] = $validated['notes'];
        }

        return $this->success('Calibration schedule created successfully', $payload, 201);
    }

    public function show(CalibrationSchedule $calibrationSchedule): JsonResponse
    {
        $this->authorize('view', $calibrationSchedule);

        return $this->success(
            'Calibration schedule retrieved successfully',
            $this->toLegacySchedulePayload($calibrationSchedule->load('tester:id,name,id_number_by_customer'))
        );
    }

    public function update(UpdateCalibrationScheduleRequest $request, CalibrationSchedule $calibrationSchedule): JsonResponse
    {
        $this->authorize('update', $calibrationSchedule);

        $validated = $request->validated();
        $payload = [];

        if (array_key_exists('tester_id', $validated)) {
            $payload['tester_id'] = $validated['tester_id'];
        }

        if (array_key_exists('scheduled_date', $validated) && ! empty($validated['scheduled_date'])) {
            $payload['next_calibration_due'] = Carbon::parse($validated['scheduled_date'])->endOfDay();
        }

        if (array_key_exists('procedure', $validated) && ! empty($validated['procedure'])) {
            $payload['calibration_id'] = $this->resolveCalibrationProcedureId((string) $validated['procedure']);
        }

        if (array_key_exists('status', $validated) && ! empty($validated['status'])) {
            $status = strtolower((string) $validated['status']);

            if ($status === 'completed') {
                $completedStatusId = $this->resolveScheduleStatusId('completed', false);

                if ($completedStatusId !== null) {
                    $payload['calibration_status'] = $completedStatusId;
                }

                if (! array_key_exists('completed_date', $validated) && $calibrationSchedule->last_calibration_date === null) {
                    $payload['last_calibration_date'] = now();
                }
            } else {
                $payload['calibration_status'] = $this->resolveScheduleStatusId($status);
            }
        }

        if (array_key_exists('completed_date', $validated)) {
            $payload['last_calibration_date'] = $validated['completed_date']
                ? Carbon::parse($validated['completed_date'])->endOfDay()
                : null;
        }

        if (array_key_exists('performed_by', $validated) && ! empty($validated['performed_by'])) {
            $payload['last_calibration_by_user_id'] = $this->resolveActorUserId(
                $request->user()?->id,
                (string) $validated['performed_by'],
            );
        }

        if ($payload !== []) {
            $calibrationSchedule->update($payload);
        }

        $responsePayload = $this->toLegacySchedulePayload(
            $calibrationSchedule->fresh()->load('tester:id,name,id_number_by_customer')
        );

        if (array_key_exists('notes', $validated)) {
            $responsePayload['notes'] = $validated['notes'];
        }

        return $this->success('Calibration schedule updated successfully', $responsePayload);
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
        $completedAt = Carbon::parse($validated['completed_date'])->endOfDay();
        $actorUserId = $this->resolveActorUserId($request->user()?->id, (string) $validated['performed_by']);

        $schedulePayload = [
            'last_calibration_date' => $completedAt,
            'last_calibration_by_user_id' => $actorUserId,
        ];

        $completedStatusId = $this->resolveScheduleStatusId('completed', false);

        if ($completedStatusId !== null) {
            $schedulePayload['calibration_status'] = $completedStatusId;
        }

        $calibrationSchedule->update($schedulePayload);

        $procedureName = $this->resolveCalibrationProcedureName($calibrationSchedule->calibration_id) ?? 'Calibration';
        $description = sprintf('Calibration completed: %s', $procedureName);

        if (! empty($validated['notes'])) {
            $description .= sprintf(' (Notes: %s)', $validated['notes']);
        }

        EventLog::create([
            'tester_id' => $calibrationSchedule->tester_id,
            'event_type' => $this->resolveEventTypeId('calibration'),
            'date' => $completedAt,
            'description' => $description,
            'created_by_user_id' => $actorUserId,
            'maintenance_schedule_id' => null,
            'calibration_schedule_id' => $calibrationSchedule->id,
            'resolution_description' => $validated['notes'] ?? null,
            'resolved_date' => $completedAt,
            'resolved_by_user_id' => $actorUserId,
        ]);

        $responsePayload = $this->toLegacySchedulePayload(
            $calibrationSchedule->fresh()->load('tester:id,name,id_number_by_customer')
        );
        $responsePayload['status'] = 'completed';
        $responsePayload['completed_date'] = Carbon::parse($validated['completed_date'])->toDateString();
        $responsePayload['performed_by'] = $validated['performed_by'];
        $responsePayload['notes'] = $validated['notes'] ?? null;

        return $this->success('Calibration completed successfully', $responsePayload);
    }

    /**
     * @return array<string, mixed>
     */
    private function toLegacySchedulePayload(CalibrationSchedule $schedule): array
    {
        $status = $this->resolveScheduleStatusName($schedule->calibration_status);

        if ($schedule->last_calibration_date !== null) {
            $status = 'completed';
        }

        if ($status === null && $schedule->next_calibration_due !== null && Carbon::parse($schedule->next_calibration_due)->isPast()) {
            $status = 'overdue';
        }

        return [
            'id' => $schedule->id,
            'tester_id' => $schedule->tester_id,
            'scheduled_date' => $this->toDateString($schedule->next_calibration_due),
            'status' => $status ?? 'scheduled',
            'procedure' => $this->resolveCalibrationProcedureName($schedule->calibration_id),
            'completed_date' => $this->toDateString($schedule->last_calibration_date),
            'performed_by' => $this->resolveUserDisplayName($schedule->last_calibration_by_user_id),
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

    private function resolveCalibrationProcedureId(string $procedure): int
    {
        $normalizedProcedure = trim($procedure);

        $existingId = DB::table('tester_calibration_procedures')
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

        return (int) DB::table('tester_calibration_procedures')->insertGetId([
            'type' => $normalizedProcedure,
            'interval_value' => 6,
            'description' => null,
            'interval_unit' => $intervalUnitId,
        ]);
    }

    private function resolveCalibrationProcedureName(?int $calibrationId): ?string
    {
        if ($calibrationId === null) {
            return null;
        }

        $procedureName = DB::table('tester_calibration_procedures')
            ->where('id', $calibrationId)
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
