# API Implementation Guide (Code-Accurate)

## 1. Goal

This guide explains how the current API is implemented in this repository and how to extend it without breaking existing behavior.

## 2. Runtime Architecture

## 2.1 Routing and Middleware

API routes are defined in `routes/api.php` under `Route::prefix('v1')`.

- Public auth routes (`register`, `login`) are inside `throttle:6,1` middleware.
- All other routes are inside `auth:sanctum` middleware.

Implemented API controllers:

- `AuthController`
- `TesterCustomerController`
- `TesterController`
- `FixtureController`
- `MaintenanceScheduleController`
- `CalibrationScheduleController`
- `EventLogController`
- `SparePartController`

## 2.2 Response Layer

All API controllers extend `ApiController`.

`ApiController` centralizes three response helpers:

- `success(message, data, code)`
- `error(message, code, errors)`
- `paginated(message, paginator, code)`

Paginated responses always use:

- `data.items`
- `data.pagination.current_page`
- `data.pagination.per_page`
- `data.pagination.total`
- `data.pagination.last_page`

## 2.3 Validation Layer

FormRequest classes exist in `app/Http/Requests/Api` for list/store/update/complete actions.

Important implementation detail:

- Most resources use FormRequest directly in controller method signatures.
- `TesterController` and `FixtureController` use inline `$request->validate(...)` for store/update, while still using FormRequest for list/status where applicable.

## 2.4 Authorization Layer

Policies are mapped in `AppServiceProvider` using `Gate::policy(...)`.

Policy classes used by API:

- `TesterCustomerPolicy`
- `TesterPolicy`
- `FixturePolicy`
- `MaintenanceSchedulePolicy`
- `CalibrationSchedulePolicy`
- `EventLogPolicy`
- `SparePartPolicy`

`BasePolicy` provides alias-aware role checks through `hasAnyRole()`.

## 2.5 Exception Rendering

`bootstrap/app.php` defines API-specific JSON exception handlers for requests matching `api/*`:

- `ValidationException` -> `422` with `errors`
- `AuthenticationException` -> `401`
- `AuthorizationException` -> `403`
- `ModelNotFoundException` and `NotFoundHttpException` -> `404`
- Fallback `Throwable` -> HTTP status from exception or `500`

Fallback mapping also includes user-facing messages for `405` and `429`.

## 3. Domain Models Used by API

Core models used by controllers:

- `TesterCustomer`
- `Tester`
- `Fixture`
- `TesterMaintenanceSchedule`
- `TesterCalibrationSchedule`
- `TesterEventLog`
- `SparePart`
- `User`

Notable implementation characteristics:

- Several models run with `public $timestamps = false` due legacy schema.
- Controllers map API-facing fields into legacy column names, for example:
	- tester `customer_id` -> `owner_id`
	- tester `model` -> `name`
	- tester `serial_number` -> `id_number_by_customer`
	- fixture `serial_number/purchase_date/notes` are stored in `fixtures.description` as JSON metadata
- Lookup tables are used at runtime (`asset_statuses`, `schedule_statuses`, `event_types`, procedure tables, location table).

## 4. Implemented Business Flows

## 4.1 Auth Flow

- Register creates a `User`, assigns `Guest` role if that role exists, creates Sanctum token, and returns token + user + roles.
- Login validates credentials, creates Sanctum token, and returns the same envelope shape.
- Logout deletes the current access token.

## 4.2 Customer CRUD

- Create/update uses validated payload from FormRequest.
- Delete checks for linked testers (`owner_id`) and returns `409` conflict if linked rows exist.

## 4.3 Tester CRUD and Status

- List endpoint supports filters and transforms DB rows into legacy-compatible API payloads.
- Store/update map modern request keys to legacy columns.
- Serial number uniqueness is enforced by custom controller check against `id_number_by_customer`.
- Location names are resolved to `tester_and_fixture_locations`; missing location names are inserted automatically.
- `PATCH /testers/{tester}/status` resolves status name to lookup ID and updates tester status.
- Delete writes a `DataChangeLog` entry after deletion.

## 4.4 Fixture CRUD

- Similar mapping pattern to testers.
- Serial number uniqueness is checked by searching serialized metadata in `fixtures.description`.
- Metadata helper methods:
	- `decodeFixtureLegacyMeta()`
	- `encodeFixtureLegacyMeta()`

## 4.5 Maintenance Schedule Flow

- List supports tester/status/date filters.
- Store resolves maintenance procedure by name; inserts a new procedure if missing.
- Update supports partial payload updates and status transitions.
- Complete action:
	- resolves actor user
	- marks schedule as completed
	- writes a `tester_event_logs` row with event type `maintenance`

## 4.6 Calibration Schedule Flow

- Mirrors maintenance flow with calibration tables/columns.
- Complete action writes event log with event type `calibration`.

## 4.7 Event Log CRUD

- Supported operations are `index`, `store`, `show`, `update`, `destroy`.
- Event type is resolved from `event_types` table.
- `event_date` normalization:
	- `issue`/`problem` types use start-of-day timestamp
	- other types use end-of-day timestamp
- `performed_by` is optional when authenticated user exists; otherwise controller attempts mapping by full name or email.
- Metadata currently maps these optional DB fields:
	- `maintenance_schedule_id`
	- `calibration_schedule_id`
	- `resolution_description`

## 4.8 Spare Part CRUD

- Standard FormRequest-backed CRUD.
- List supports search and stock bucket filter (`low|normal|full`).
- Model appends computed `stock_status` attribute.

## 5. Roles and Permissions in Seed Data

`RoleSeeder` creates these roles:

- `Admin`
- `Manager`
- `Maintenance Technician`
- `Calibration Specialist`
- `Guest`

`RoleSeeder` also creates default users:

- `admin@example.com`
- `manager@example.com`
- `technician@example.com`
- `guest@example.com`
- `test@example.com`

## 6. Test Coverage Relevant to API

Current API-focused feature tests include:

- `ApiSmokeTest`
	- register returns token envelope
	- protected endpoint requires auth
	- admin customer CRUD
	- guest cannot create customer
	- completing maintenance creates event log row
- `IssueEventLogHistoryTest`
	- issue create/update/delete flows do not write legacy history rows

## 7. Extension Checklist (Aligned to Current Code)

When adding a new API resource:

1. Add/adjust database schema and model mapping for legacy column names if needed.
2. Create FormRequest classes (or follow existing inline validation pattern intentionally).
3. Add policy class and register it in `AppServiceProvider`.
4. Implement controller methods with explicit `$this->authorize(...)` calls.
5. Add route definitions in `routes/api.php` under `/api/v1` and correct middleware groups.
6. Return responses only through `ApiController` helper methods to keep envelope consistency.
7. Update both API documents and add/extend feature tests in `tests/Feature/Api`.

## 8. Verification Commands

```bash
php artisan migrate
php artisan db:seed
php artisan route:list --path=api
php artisan test --filter=ApiSmokeTest
php artisan test --filter=IssueEventLogHistoryTest
php artisan test
```
