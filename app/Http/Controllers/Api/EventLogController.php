<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ListEventLogRequest;
use App\Http\Requests\Api\StoreEventLogRequest;
use App\Models\TesterEventLog as EventLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventLogController extends ApiController
{
    public function index(ListEventLogRequest $request): JsonResponse
    {
        $this->authorize('viewAny', EventLog::class);

        $validated = $request->validated();
        $query = EventLog::query()->with('tester:id,name,id_number_by_customer');

        if (! empty($validated['tester_id'])) {
            $query->where('tester_id', $validated['tester_id']);
        }

        if (! empty($validated['type'])) {
            $eventTypeId = $this->resolveEventTypeId((string) $validated['type'], false);

            if ($eventTypeId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('event_type', $eventTypeId);
            }
        }

        if (! empty($validated['start_date'])) {
            $query->whereDate('date', '>=', $validated['start_date']);
        }

        if (! empty($validated['end_date'])) {
            $query->whereDate('date', '<=', $validated['end_date']);
        }

        $eventLogs = $query
            ->orderByDesc('date')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        $eventLogs->getCollection()->transform(
            fn (EventLog $eventLog): array => $this->toLegacyEventLogPayload($eventLog)
        );

        return $this->paginated('Event logs retrieved successfully', $eventLogs);
    }

    public function store(StoreEventLogRequest $request): JsonResponse
    {
        $this->authorize('create', EventLog::class);

        $validated = $request->validated();
        $metadata = isset($validated['metadata']) && is_array($validated['metadata'])
            ? $validated['metadata']
            : [];
        $actorUserId = $this->resolveActorUserId($request->user()?->id, $validated['performed_by'] ?? null);
        $eventTypeId = $this->resolveEventTypeId((string) $validated['type']);

        $eventLog = EventLog::create([
            'tester_id' => $validated['tester_id'],
            'event_type' => $eventTypeId,
            'date' => Carbon::parse($validated['event_date'])->endOfDay(),
            'description' => $validated['description'],
            'created_by_user_id' => $actorUserId,
            'maintenance_schedule_id' => $this->extractOptionalInt($metadata, 'maintenance_schedule_id'),
            'calibration_schedule_id' => $this->extractOptionalInt($metadata, 'calibration_schedule_id'),
            'resolution_description' => isset($metadata['resolution_description']) && is_string($metadata['resolution_description'])
                ? $metadata['resolution_description']
                : null,
        ]);

        return $this->success(
            'Event log created successfully',
            $this->toLegacyEventLogPayload($eventLog->load('tester:id,name,id_number_by_customer')),
            201
        );
    }

    public function show(EventLog $eventLog): JsonResponse
    {
        $this->authorize('view', $eventLog);

        return $this->success(
            'Event log retrieved successfully',
            $this->toLegacyEventLogPayload($eventLog->load('tester:id,name,id_number_by_customer'))
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function toLegacyEventLogPayload(EventLog $eventLog): array
    {
        $metadata = [];

        if ($eventLog->maintenance_schedule_id !== null) {
            $metadata['maintenance_schedule_id'] = (int) $eventLog->maintenance_schedule_id;
        }

        if ($eventLog->calibration_schedule_id !== null) {
            $metadata['calibration_schedule_id'] = (int) $eventLog->calibration_schedule_id;
        }

        if ($eventLog->resolution_description !== null) {
            $metadata['resolution_description'] = $eventLog->resolution_description;
        }

        return [
            'id' => $eventLog->id,
            'tester_id' => $eventLog->tester_id,
            'type' => $this->resolveEventTypeName($eventLog->event_type),
            'event_date' => $this->toDateString($eventLog->date),
            'description' => $eventLog->description,
            'performed_by' => $this->resolveUserDisplayName($eventLog->created_by_user_id),
            'metadata' => $metadata === [] ? null : $metadata,
            'tester' => $this->toLegacyTesterPayload($eventLog->tester),
        ];
    }

    private function resolveEventTypeId(string $eventType, bool $strict = true): ?int
    {
        $eventTypeId = DB::table('event_types')
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($eventType))])
            ->value('id');

        if ($eventTypeId === null && $strict) {
            throw ValidationException::withMessages([
                'type' => ['Unsupported event type.'],
            ]);
        }

        return $eventTypeId !== null ? (int) $eventTypeId : null;
    }

    private function resolveEventTypeName(?int $eventTypeId): string
    {
        if ($eventTypeId === null) {
            return 'other';
        }

        $eventTypeName = DB::table('event_types')
            ->where('id', $eventTypeId)
            ->value('name');

        return is_string($eventTypeName) ? strtolower($eventTypeName) : 'other';
    }

    private function resolveActorUserId(?int $authenticatedUserId, mixed $performedBy): int
    {
        if ($authenticatedUserId !== null) {
            return $authenticatedUserId;
        }

        if (is_string($performedBy) && trim($performedBy) !== '') {
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

    private function extractOptionalInt(array $metadata, string $key): ?int
    {
        if (! array_key_exists($key, $metadata) || ! is_numeric($metadata[$key])) {
            return null;
        }

        return (int) $metadata[$key];
    }
}
