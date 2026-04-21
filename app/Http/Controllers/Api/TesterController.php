<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ListTesterRequest;
use App\Http\Requests\Api\UpdateTesterStatusRequest;
use App\Models\Tester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TesterController extends ApiController
{
    public function index(ListTesterRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Tester::class);

        $validated = $request->validated();
        $query = Tester::query()->with(['owner:id,name', 'location:id,name', 'statusRelation:id,name']);

        if (! empty($validated['status'])) {
            $statusId = $this->resolveAssetStatusId((string) $validated['status'], false);

            if ($statusId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('status', $statusId);
            }
        }

        if (! empty($validated['customer_id'])) {
            $query->where('owner_id', $validated['customer_id']);
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('id_number_by_customer', 'like', "%{$search}%")
                    ->orWhereHas('location', function ($locationQuery) use ($search): void {
                        $locationQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $testers = $query
            ->orderByDesc('id')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        $testers->getCollection()->transform(
            fn(Tester $tester): array => $this->toLegacyTesterPayload($tester)
        );

        return $this->paginated('Testers retrieved successfully', $testers);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Tester::class);

        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:tester_customers,id'],
            'model' => ['required', 'string', 'max:100'],
            'serial_number' => ['required', 'string', 'max:100'],
            'purchase_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:active,inactive,maintenance'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->ensureUniqueSerialNumber((string) $validated['serial_number']);

        $tester = Tester::create([
            'owner_id' => $validated['customer_id'],
            'name' => $validated['model'],
            'id_number_by_customer' => $validated['serial_number'],
            'implementation_date' => ! empty($validated['purchase_date'])
                ? Carbon::parse($validated['purchase_date'])->toDateString()
                : null,
            'status' => ! empty($validated['status'])
                ? $this->resolveAssetStatusId((string) $validated['status'])
                : null,
            'location_id' => $this->resolveLocationId($validated['location'] ?? null),
            'additional_info' => $validated['notes'] ?? null,
        ]);

        return $this->success(
            'Tester created successfully',
            $this->toLegacyTesterPayload($tester->load(['owner:id,name', 'location:id,name', 'statusRelation:id,name'])),
            201
        );
    }

    public function show(Tester $tester): JsonResponse
    {
        $this->authorize('view', $tester);

        return $this->success(
            'Tester retrieved successfully',
            $this->toLegacyTesterPayload($tester->load(['owner:id,name', 'location:id,name', 'statusRelation:id,name']))
        );
    }

    public function update(Request $request, Tester $tester): JsonResponse
    {
        $this->authorize('update', $tester);

        $validated = $request->validate([
            'customer_id' => ['sometimes', 'integer', 'exists:tester_customers,id'],
            'model' => ['sometimes', 'string', 'max:100'],
            'serial_number' => ['sometimes', 'string', 'max:100'],
            'purchase_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'in:active,inactive,maintenance'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        if (array_key_exists('serial_number', $validated)) {
            $this->ensureUniqueSerialNumber((string) $validated['serial_number'], $tester->id);
        }

        $payload = [];

        if (array_key_exists('customer_id', $validated)) {
            $payload['owner_id'] = $validated['customer_id'];
        }

        if (array_key_exists('model', $validated)) {
            $payload['name'] = $validated['model'];
        }

        if (array_key_exists('serial_number', $validated)) {
            $payload['id_number_by_customer'] = $validated['serial_number'];
        }

        if (array_key_exists('purchase_date', $validated)) {
            $payload['implementation_date'] = $validated['purchase_date']
                ? Carbon::parse($validated['purchase_date'])->toDateString()
                : null;
        }

        if (array_key_exists('status', $validated)) {
            $payload['status'] = $this->resolveAssetStatusId((string) $validated['status']);
        }

        if (array_key_exists('location', $validated)) {
            $payload['location_id'] = $this->resolveLocationId($validated['location']);
        }

        if (array_key_exists('notes', $validated)) {
            $payload['additional_info'] = $validated['notes'];
        }

        if ($payload !== []) {
            $tester->update($payload);
        }

        return $this->success(
            'Tester updated successfully',
            $this->toLegacyTesterPayload($tester->fresh()->load(['owner:id,name', 'location:id,name', 'statusRelation:id,name']))
        );
    }

    public function destroy(Tester $tester): JsonResponse
    {
        $this->authorize('delete', $tester);

        $tester->load('assets');

        $details = [
            "- id: [{$tester->id}]",
            "- name: [" . ($tester->name ?? 'empty') . "]",
            "- description: [" . ($tester->description ?? 'empty') . "]",
            "- id_number_by_customer: [" . ($tester->id_number_by_customer ?? 'empty') . "]",
            "- operating_system: [" . ($tester->operating_system ?? 'empty') . "]",
            "- type: [" . ($tester->type ?? 'empty') . "]",
            "- product_family: [" . ($tester->product_family ?? 'empty') . "]",
            "- manufacturer: [" . ($tester->manufacturer ?? 'empty') . "]",
            "- implementation_date: [" . ($tester->implementation_date ?? 'empty') . "]",
            "- additional_info: [" . ($tester->additional_info ?? 'empty') . "]",
            "- location_id: [" . ($tester->location_id ?? 'empty') . "]",
            "- owner_id: [" . ($tester->owner_id ?? 'empty') . "]",
            "- status: [" . ($tester->status ?? 'empty') . "]",
        ];

        foreach ($tester->assets as $index => $asset) {
            $details[] = "- asset_no " . ($index + 1) . ": [" . ($asset->asset_no ?? 'empty') . "]";
        }

        $tester->delete();

        \App\Models\DataChangeLog::create([
            'changed_at' => now(),
            'explanation' => "Deleted tester details:\n" . implode("\n", $details),
            'tester_id' => $tester->id,
            'user_id' => auth()->id() ?? 1,
        ]);

        return $this->success('Tester deleted successfully');
    }

    public function updateStatus(UpdateTesterStatusRequest $request, Tester $tester): JsonResponse
    {
        $this->authorize('updateStatus', $tester);

        $tester->update([
            'status' => $this->resolveAssetStatusId($request->validated()['status']),
        ]);

        return $this->success(
            'Tester status updated successfully',
            $this->toLegacyTesterPayload($tester->fresh()->load(['owner:id,name', 'location:id,name', 'statusRelation:id,name']))
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function toLegacyTesterPayload(Tester $tester): array
    {
        $statusName = null;

        if ($tester->relationLoaded('statusRelation') && $tester->statusRelation !== null) {
            $statusName = strtolower((string) $tester->statusRelation->name);
        } elseif ($tester->status !== null) {
            $status = DB::table('asset_statuses')->where('id', $tester->status)->value('name');
            $statusName = is_string($status) ? strtolower($status) : null;
        }

        $locationName = null;

        if ($tester->relationLoaded('location') && $tester->location !== null) {
            $locationName = $tester->location->name;
        } elseif ($tester->location_id !== null) {
            $location = DB::table('tester_and_fixture_locations')->where('id', $tester->location_id)->value('name');
            $locationName = is_string($location) ? $location : null;
        }

        $customer = null;

        if ($tester->relationLoaded('owner') && $tester->owner !== null) {
            $customer = [
                'id' => $tester->owner->id,
                'name' => $tester->owner->name,
            ];
        } elseif ($tester->owner_id !== null) {
            $ownerName = DB::table('tester_customers')->where('id', $tester->owner_id)->value('name');

            if (is_string($ownerName)) {
                $customer = [
                    'id' => $tester->owner_id,
                    'name' => $ownerName,
                ];
            }
        }

        return [
            'id' => $tester->id,
            'customer_id' => $tester->owner_id,
            'model' => $tester->name,
            'serial_number' => $tester->id_number_by_customer,
            'purchase_date' => $this->toDateString($tester->implementation_date),
            'status' => $statusName,
            'location' => $locationName,
            'notes' => $tester->additional_info,
            'customer' => $customer,
        ];
    }

    private function resolveAssetStatusId(?string $status, bool $strict = true): ?int
    {
        if ($status === null || trim($status) === '') {
            return null;
        }

        $statusId = DB::table('asset_statuses')
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($status))])
            ->value('id');

        if ($statusId === null && $strict) {
            throw ValidationException::withMessages([
                'status' => ['Unsupported status value.'],
            ]);
        }

        return $statusId !== null ? (int) $statusId : null;
    }

    private function resolveLocationId(?string $location): ?int
    {
        if ($location === null || trim($location) === '') {
            return null;
        }

        $normalized = trim($location);

        $existingLocationId = DB::table('tester_and_fixture_locations')
            ->whereRaw('LOWER(name) = ?', [strtolower($normalized)])
            ->value('id');

        if ($existingLocationId !== null) {
            return (int) $existingLocationId;
        }

        return (int) DB::table('tester_and_fixture_locations')->insertGetId([
            'name' => $normalized,
            'description' => null,
            'address' => null,
        ]);
    }

    private function ensureUniqueSerialNumber(string $serialNumber, ?int $ignoreTesterId = null): void
    {
        $query = Tester::query()->where('id_number_by_customer', $serialNumber);

        if ($ignoreTesterId !== null) {
            $query->where('id', '!=', $ignoreTesterId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'serial_number' => ['The serial number has already been taken.'],
            ]);
        }
    }

    private function toDateString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }
}
