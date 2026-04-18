<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ListFixtureRequest;
use App\Models\Fixture;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FixtureController extends ApiController
{
    public function index(ListFixtureRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Fixture::class);

        $validated = $request->validated();
        $query = Fixture::query()->with(['tester:id,name,id_number_by_customer', 'location:id,name', 'status:id,name']);

        if (! empty($validated['tester_id'])) {
            $query->where('tester_id', $validated['tester_id']);
        }

        if (! empty($validated['status'])) {
            $statusId = $this->resolveAssetStatusId((string) $validated['status'], false);

            if ($statusId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('fixture_status', $statusId);
            }
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('location', function ($locationQuery) use ($search): void {
                        $locationQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $fixtures = $query
            ->orderByDesc('id')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        $fixtures->getCollection()->transform(
            fn(Fixture $fixture): array => $this->toLegacyFixturePayload($fixture)
        );

        return $this->paginated('Fixtures retrieved successfully', $fixtures);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Fixture::class);

        $validated = $request->validate([
            'tester_id' => ['required', 'integer', 'exists:testers,id'],
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:100'],
            'purchase_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:active,inactive,maintenance'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->ensureUniqueFixtureSerial((string) $validated['serial_number']);

        $fixture = Fixture::create([
            'tester_id' => $validated['tester_id'],
            'name' => $validated['name'],
            'description' => $this->encodeFixtureLegacyMeta(
                (string) $validated['serial_number'],
                $validated['purchase_date'] ?? null,
                $validated['notes'] ?? null,
            ),
            'manufacturer' => null,
            'location_id' => $this->resolveLocationId($validated['location'] ?? null),
            'fixture_status' => ! empty($validated['status'])
                ? $this->resolveAssetStatusId((string) $validated['status'])
                : null,
        ]);

        return $this->success(
            'Fixture created successfully',
            $this->toLegacyFixturePayload($fixture->load(['tester:id,name,id_number_by_customer', 'location:id,name', 'status:id,name'])),
            201
        );
    }

    public function show(Fixture $fixture): JsonResponse
    {
        $this->authorize('view', $fixture);

        return $this->success(
            'Fixture retrieved successfully',
            $this->toLegacyFixturePayload($fixture->load(['tester:id,name,id_number_by_customer', 'location:id,name', 'status:id,name']))
        );
    }

    public function update(Request $request, Fixture $fixture): JsonResponse
    {
        $this->authorize('update', $fixture);

        $validated = $request->validate([
            'tester_id' => ['sometimes', 'integer', 'exists:testers,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'serial_number' => ['sometimes', 'string', 'max:100'],
            'purchase_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'in:active,inactive,maintenance'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        if (array_key_exists('serial_number', $validated)) {
            $this->ensureUniqueFixtureSerial((string) $validated['serial_number'], $fixture->id);
        }

        $payload = [];

        if (array_key_exists('tester_id', $validated)) {
            $payload['tester_id'] = $validated['tester_id'];
        }

        if (array_key_exists('name', $validated)) {
            $payload['name'] = $validated['name'];
        }

        if (array_key_exists('status', $validated)) {
            $payload['fixture_status'] = $this->resolveAssetStatusId((string) $validated['status']);
        }

        if (array_key_exists('location', $validated)) {
            $payload['location_id'] = $this->resolveLocationId($validated['location']);
        }

        if (
            array_key_exists('serial_number', $validated)
            || array_key_exists('purchase_date', $validated)
            || array_key_exists('notes', $validated)
        ) {
            $currentMeta = $this->decodeFixtureLegacyMeta($fixture->description);

            $payload['description'] = $this->encodeFixtureLegacyMeta(
                array_key_exists('serial_number', $validated)
                    ? (string) $validated['serial_number']
                    : $currentMeta['serial_number'],
                array_key_exists('purchase_date', $validated)
                    ? $validated['purchase_date']
                    : $currentMeta['purchase_date'],
                array_key_exists('notes', $validated)
                    ? $validated['notes']
                    : $currentMeta['notes'],
            );
        }

        if ($payload !== []) {
            $fixture->update($payload);
        }

        return $this->success(
            'Fixture updated successfully',
            $this->toLegacyFixturePayload($fixture->fresh()->load(['tester:id,name,id_number_by_customer', 'location:id,name', 'status:id,name']))
        );
    }

    public function destroy(Fixture $fixture): JsonResponse
    {
        $this->authorize('delete', $fixture);

        $fixture->delete();

        return $this->success('Fixture deleted successfully');
    }

    /**
     * @return array<string, mixed>
     */
    private function toLegacyFixturePayload(Fixture $fixture): array
    {
        $meta = $this->decodeFixtureLegacyMeta($fixture->description);

        $statusName = null;

        if ($fixture->relationLoaded('status') && $fixture->status !== null) {
            $statusName = strtolower((string) $fixture->status->name);
        } elseif ($fixture->fixture_status !== null) {
            $status = DB::table('asset_statuses')->where('id', $fixture->fixture_status)->value('name');
            $statusName = is_string($status) ? strtolower($status) : null;
        }

        $locationName = null;

        if ($fixture->relationLoaded('location') && $fixture->location !== null) {
            $locationName = $fixture->location->name;
        } elseif ($fixture->location_id !== null) {
            $location = DB::table('tester_and_fixture_locations')->where('id', $fixture->location_id)->value('name');
            $locationName = is_string($location) ? $location : null;
        }

        return [
            'id' => $fixture->id,
            'tester_id' => $fixture->tester_id,
            'name' => $fixture->name,
            'serial_number' => $meta['serial_number'],
            'purchase_date' => $meta['purchase_date'],
            'status' => $statusName,
            'location' => $locationName,
            'notes' => $meta['notes'],
            'tester' => $this->toLegacyTesterPayload($fixture->tester),
        ];
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

    /**
     * @return array{serial_number: ?string, purchase_date: ?string, notes: ?string}
     */
    private function decodeFixtureLegacyMeta(?string $description): array
    {
        if ($description === null || trim($description) === '') {
            return [
                'serial_number' => null,
                'purchase_date' => null,
                'notes' => null,
            ];
        }

        $decoded = json_decode($description, true);

        if (
            is_array($decoded)
            && (
                array_key_exists('serial_number', $decoded)
                || array_key_exists('purchase_date', $decoded)
                || array_key_exists('notes', $decoded)
            )
        ) {
            return [
                'serial_number' => isset($decoded['serial_number']) ? (string) $decoded['serial_number'] : null,
                'purchase_date' => isset($decoded['purchase_date']) ? (string) $decoded['purchase_date'] : null,
                'notes' => isset($decoded['notes']) ? (string) $decoded['notes'] : null,
            ];
        }

        return [
            'serial_number' => null,
            'purchase_date' => null,
            'notes' => $description,
        ];
    }

    private function encodeFixtureLegacyMeta(?string $serialNumber, mixed $purchaseDate, mixed $notes): string
    {
        $normalizedPurchaseDate = null;

        if (is_string($purchaseDate) && trim($purchaseDate) !== '') {
            $normalizedPurchaseDate = Carbon::parse($purchaseDate)->toDateString();
        }

        return (string) json_encode([
            'serial_number' => $serialNumber !== null && trim($serialNumber) !== '' ? $serialNumber : null,
            'purchase_date' => $normalizedPurchaseDate,
            'notes' => is_string($notes) ? $notes : null,
        ], JSON_UNESCAPED_SLASHES);
    }

    private function ensureUniqueFixtureSerial(string $serialNumber, ?int $ignoreFixtureId = null): void
    {
        $escapedSerial = addcslashes($serialNumber, "\\%_\"");
        $pattern = '%"serial_number":"' . $escapedSerial . '"%';

        $query = Fixture::query()->where('description', 'like', $pattern);

        if ($ignoreFixtureId !== null) {
            $query->where('id', '!=', $ignoreFixtureId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'serial_number' => ['The serial number has already been taken.'],
            ]);
        }
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
}
