# API Implementation Guide

## 1. Goal

This guide explains how the API is implemented in the project and where to extend it safely.

## 2. Architecture Layers

### 2.1 Models

Domain models are located in `app/Models`:

- `TesterCustomer`
- `Tester`
- `Fixture`
- `MaintenanceSchedule`
- `CalibrationSchedule`
- `EventLog`
- `SparePart`

Each model defines:

- fillable fields
- casts
- relationships

### 2.2 Controllers

API controllers are in `app/Http/Controllers/Api` and extend `ApiController`.

`ApiController` provides:

- standardized success responses
- standardized error responses
- standardized paginated responses

### 2.3 Request Validation

FormRequest classes are in `app/Http/Requests/Api`.

Rules are split by operation:

- list requests for query params
- store requests for create payloads
- update requests for patch payloads
- custom action requests (status/complete)

### 2.4 Policies

Policies are in `app/Policies` and mapped in `AppServiceProvider`.

`BasePolicy` centralizes alias-compatible role checks:

- `admin` <-> `Admin`
- `manager` <-> `Manager` and `Maintenance Technician`
- `technician` <-> `Technician` and `Calibration Specialist`
- `guest` <-> `Guest`

## 3. Request Lifecycle

1. Route receives request (`routes/api.php`, `/api/v1`)
2. Middleware checks rate limits / token auth
3. FormRequest validates input
4. Controller calls policy authorization
5. Controller executes business logic
6. API returns envelope response

## 4. Error Handling

`bootstrap/app.php` defines API-only exception rendering:

- `ValidationException` -> 422 + `errors`
- `AuthenticationException` -> 401
- `AuthorizationException` -> 403
- `ModelNotFoundException`/`NotFoundHttpException` -> 404
- fallback `Throwable` -> 500

All responses keep the same envelope contract.

## 5. Business Flows

### 5.1 Tester Status Transition

Endpoint: `PATCH /api/v1/testers/{tester}/status`

Validation:

- `status` required
- values: `active|inactive|maintenance`

Authorization:

- admin or manager only

### 5.2 Complete Maintenance

Endpoint: `POST /api/v1/maintenance-schedules/{maintenanceSchedule}/complete`

Behavior:

- updates schedule status to `completed`
- stores completed date, performer, notes
- writes an `event_logs` record of type `maintenance`

### 5.3 Complete Calibration

Endpoint: `POST /api/v1/calibration-schedules/{calibrationSchedule}/complete`

Behavior:

- updates schedule status to `completed`
- stores completed date, performer, notes
- writes an `event_logs` record of type `calibration`

## 6. Database and Seeders

### 6.1 New Tables

- `personal_access_tokens`
- `tester_customers`
- `testers`
- `fixtures`
- `maintenance_schedules`
- `calibration_schedules`
- `event_logs`
- `spare_parts`

### 6.2 Seeders

- `RoleSeeder` creates roles and default users
- `DatabaseSeeder` ensures a default test account exists

Default role-compatible accounts:

- `admin@example.com`
- `manager@example.com`
- `technician@example.com`
- `guest@example.com`
- `test@example.com`

## 7. Extension Checklist

When adding a new API resource:

1. Create migration and model with relationships
2. Create FormRequest classes for list/store/update/custom actions
3. Create policy and map it in `AppServiceProvider`
4. Add controller with `authorize()` checks
5. Add routes under `/api/v1` with `auth:sanctum`
6. Update `docs/API_DESIGN.md`
7. Add request examples to Postman collection

## 8. Local Verification Commands

```bash
php artisan migrate
php artisan db:seed
php artisan route:list --path=api
php artisan test --filter=ApiSmokeTest
php artisan test
```

If full test suite cannot run due environment limitations, at minimum run route list and migration checks.
