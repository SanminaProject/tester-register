# Tester Register API Implementation Guide (V15)

Status: Active
Version: V15
Last Updated: 2026-04-01

Versioning note:

- `Version: V15` is the document revision aligned with chenqi branch commit progression.
- Actual API routes remain under `/api/v1` in code.

## 1. Purpose and Audience

This guide explains how the API is implemented in this repository, not just what the API contract is.
It is intended for:

- Backend developers implementing or modifying endpoints
- QA engineers writing automated tests
- New contributors learning the project architecture

If you need endpoint fields/examples only, read API_DESIGN.md.
If you need architecture and coding workflow, read this file.

## 2. Stack and Key Packages

Backend framework:

- Laravel 12

Auth and permission:

- laravel/sanctum
- spatie/laravel-permission

Interactive web layer (non-API):

- livewire/livewire
- livewire/volt

Testing:

- phpunit

Package versions are defined in composer.json.

## 3. API Folder and File Map

Core implementation files:

- routes/api.php
- app/Http/Controllers/Controller.php
- app/Http/Controllers/Api/AuthController.php
- app/Http/Controllers/Api/TesterCustomerController.php
- app/Http/Controllers/Api/TesterController.php
- app/Http/Controllers/Api/FixtureController.php
- app/Http/Controllers/Api/MaintenanceScheduleController.php
- app/Http/Controllers/Api/CalibrationScheduleController.php
- app/Http/Controllers/Api/EventLogController.php
- app/Http/Controllers/Api/SparePartController.php

Authorization:

- app/Policies/BasePolicy.php
- app/Policies/\*Policy.php
- app/Providers/AppServiceProvider.php

Models:

- app/Models/User.php
- app/Models/TesterCustomer.php
- app/Models/Tester.php
- app/Models/Fixture.php
- app/Models/MaintenanceSchedule.php
- app/Models/CalibrationSchedule.php
- app/Models/EventLog.php
- app/Models/SparePart.php

Database:

- database/migrations/\*.php
- database/seeders/DatabaseSeeder.php
- database/seeders/RoleSeeder.php

Tests:

- tests/Feature/Api/AuthApiTest.php
- tests/Feature/Api/CustomerApiTest.php

## 4. Request Lifecycle (How an API Request Is Processed)

A typical protected API request goes through these stages:

1. Route matching

- routes/api.php maps URI + method to controller action.
- Most endpoints are grouped under /api/v1 and auth:sanctum middleware.

2. Authentication

- Sanctum verifies bearer token for protected routes.

3. Controller authorization check

- Actions call $this->authorize(...).
- This requires app/Http/Controllers/Controller.php to use AuthorizesRequests.

4. Validation

- Most actions use dedicated FormRequest classes for validation.
- Validation failures return HTTP 422.

5. Business logic and persistence

- Eloquent model queries/creates/updates/deletes are executed.

6. JSON response

- Success responses generally follow:
  {
  "success": true,
  "message": "...",
  "data": ...,
  "code": <http_status>
  }

## 5. Routing Architecture

API routes use v1 prefix:

- Public: /auth/register, /auth/login
- Protected (auth:sanctum): all resource endpoints and /auth/logout

Resource routing style:

- Route::apiResource(...) for standard REST actions
- Custom action routes for status completion flows
    - PATCH /testers/{tester}/status
    - POST /maintenance-schedules/{schedule}/complete
    - POST /calibration-schedules/{schedule}/complete

Model binding parameter customization is used for readability:

- maintenance-schedules -> {schedule}
- calibration-schedules -> {schedule}
- event-logs -> {log}
- spare-parts -> {part}

## 6. Authentication Implementation Details

### 6.1 Register

File:

- app/Http/Controllers/Api/AuthController.php

Behavior:

- Validates name/email/password + password_confirmation
- Creates user
- Issues Sanctum token
- Returns 201 with token and user basic info

### 6.2 Login

Behavior:

- Validates email/password
- Manual credential check with Hash::check
- On success: issue token, return user roles
- On failure: return 401 custom JSON body

### 6.3 Logout

Behavior:

- Deletes current access token only
- Returns 200 success

## 7. Authorization and Policy System

### 7.1 Policy Registration

File:

- app/Providers/AppServiceProvider.php

A model->policy map is registered in boot() via Gate::policy().

### 7.2 BasePolicy Design

File:

- app/Policies/BasePolicy.php

BasePolicy intentionally holds only shared role helper logic:

- ROLE_ALIASES mapping
- hasAnyRole(User $user, array $roles)

Why helper-only:

- Prevents method signature conflicts between base methods and child policy model-specific signatures.

### 7.3 Role Alias Compatibility

Canonical to accepted names:

- admin: admin, Admin
- manager: manager, Maintenance Technician
- technician: technician, Calibration Specialist
- guest: guest, Guest

This supports older naming conventions and API naming together.

### 7.4 Per-Resource Authorization Pattern

Controller actions follow pattern:

- index/store -> authorize against Model::class
- show/update/destroy -> authorize against model instance

Examples:

- $this->authorize('view', Tester::class)
- $this->authorize('update', $customer)

## 8. Validation Strategy (Current and Recommended)

### 8.1 Current Strategy

Most controllers use inline validation rules with $request->validate().

Pros:

- Fast to implement
- Rules are close to action logic

Cons:

- Rules are duplicated across controllers
- Harder to reuse and version consistently

### 8.2 Recommended Next Step

Introduce Form Request classes in app/Http/Requests for:

- Store and update actions per resource
- Shared list query parameter validation (page, per_page, date ranges)

Migration approach:

1. Start with high-traffic endpoints (customers/testers)
2. Move inline rules to Form Requests
3. Keep response shape unchanged
4. Update tests to lock behavior

## 9. Data Access and Domain Patterns

### 9.1 Pagination Pattern

List endpoints generally implement manual pagination:

- Read page/per_page from query
- Query->count()
- Query->forPage($page, $perPage)->get()
- Return custom pagination object

This is consistent with current response envelope, but not Laravel paginator object.

### 9.2 Filtering Pattern

Filters vary by resource:

- customers: search company_name/email
- testers: status, customer_id, search
- fixtures: tester_id, status, search
- schedules: tester_id, status, start_date, end_date
- event logs: tester_id, type, date range
- spare parts: search, stock_status

### 9.3 Derived Field Pattern

SparePart model computes stock_status dynamically via accessor based on quantity_in_stock.

### 9.4 Complete Action Pattern

Maintenance/Calibration "complete" action:

- Validates completion fields
- Sets status = completed
- Fills completion metadata (date, performer, optional notes)

## 10. Database and Seeding Implementation

### 10.1 Schema Source of Truth

Primary schema for runtime is migration files in database/migrations.

### 10.2 Seeders

- DatabaseSeeder creates baseline test user with firstOrCreate.
- RoleSeeder creates default roles and default admin assignment.

Important:

- Seeders are designed to be repeatable for local/dev workflows.

### 10.3 Known Difference to Legacy SQL Docs

Files under database/docs represent historical design notes and broader planning.
The running API behavior should always be treated as defined by migrations + models + controllers.

## 11. Error Handling Behavior

Current behavior is unified for API routes (`/api/*`) via centralized exception rendering in `bootstrap/app.php`.

1. Business errors (manual response)

- Example: invalid login -> custom 401 envelope
- Example: delete customer with linked testers -> 409 custom envelope

2. Framework errors

- Validation failures (422)
- Authorization denials (403)
- Route model not found (404)

All framework errors above are normalized to the API error envelope:

```json
{
    "success": false,
    "message": "Human readable error message",
    "code": 422,
    "errors": {
        "field": ["Validation message"]
    }
}
```

Notes:

- `errors` is present for validation exceptions.
- 401/403/404/405/500 return the same envelope shape without `errors` by default.

## 12. API Testing Strategy

### 12.1 Existing API Test Coverage

Implemented tests:

- tests/Feature/Api/AuthApiTest.php
    - register success
    - register validation failure
    - login success
    - login invalid credentials

- tests/Feature/Api/CustomerApiTest.php
    - unauthenticated blocked
    - guest role blocked from customer list
        - authenticated without role blocked
        - admin CRUD happy path
        - validation failure

- tests/Feature/Api/TesterApiTest.php
    - list/show/create/update/delete/status flows
    - filters, pagination, and validation checks

- tests/Feature/Api/FixtureApiTest.php
    - list/show/create/update/delete flows
    - filters, search, pagination, and validation checks

- tests/Feature/Api/MaintenanceScheduleApiTest.php
    - list/show/create/update/delete flows
    - complete action and validation checks

- tests/Feature/Api/CalibrationScheduleApiTest.php
    - list/show/create/update/delete flows
    - complete action and validation checks

- tests/Feature/Api/EventLogApiTest.php
    - list/show/create flows
    - immutable endpoint constraints (update/delete blocked)

- tests/Feature/Api/SparePartApiTest.php
    - list/show/create/update/delete flows
    - stock filters/search/pagination and validation checks

Current status:

- API test suite: 120 passed

### 12.2 Test Commands

Run only API tests:

```bash
php artisan test tests/Feature/Api
```

Run full suite:

```bash
php artisan test
```

### 12.3 Next Testing Improvements

Suggested next additions:

1. Rate limit and abuse-protection tests on auth endpoints
2. Data-volume and pagination boundary tests with larger fixtures
3. Contract tests that assert response envelope consistency for all error paths

## 13. Implementation Workflow for New API Endpoints

Use this checklist when adding a new endpoint:

1. Define contract first

- Update API_DESIGN.md fields/examples/errors/permissions.

2. Add route

- Place route under correct auth group.
- Use explicit model binding parameter names if needed.

3. Implement controller action

- Add authorize() check first.
- Add validation rules.
- Implement Eloquent operation.
- Return standardized success envelope.

4. Implement or update policy

- Add action permissions by role.
- Keep alias compatibility via hasAnyRole().

5. Add tests

- auth required test
- forbidden role test
- validation failure test
- success path test

6. Run tests

- php artisan test tests/Feature/Api
- php artisan test

7. Update docs

- API_DESIGN.md
- this implementation guide if architecture pattern changed
- Postman collection if examples changed

## 14. Common Pitfalls and How to Avoid Them

Pitfall 1:

- Calling authorize() fails at runtime
  Cause:
- Base controller missing AuthorizesRequests trait
  Fix:
- Ensure app/Http/Controllers/Controller.php extends routing base controller and uses required traits

Pitfall 2:

- Policy class signature fatal errors
  Cause:
- Base policy defines CRUD methods with incompatible signatures
  Fix:
- Keep BasePolicy helper-only, implement CRUD methods in concrete policies

Pitfall 3:

- Seed fails on repeated db:seed
  Cause:
- Non-idempotent create with fixed unique fields
  Fix:
- Use firstOrCreate/updateOrCreate in seeders

Pitfall 4:

- Authorization behavior differs by role naming
  Cause:
- Role naming inconsistency between UI and API docs
  Fix:
- Use alias mapping in BasePolicy and keep role naming table in docs updated

## 15. Security and Hardening Backlog

Recommended hardening tasks:

1. Add throttling for /auth/login and /auth/register
2. Normalize all API errors with a consistent envelope
3. Introduce Form Request classes for all write endpoints
4. Add request correlation id in logs for traceability
5. Add API rate limit and abuse monitoring
6. Add token expiration strategy if required by business policy

## 16. Delivery Checklist

Before release:

1. php artisan db:seed runs repeatedly without failure
2. php artisan test tests/Feature/Api passes
3. php artisan test passes
4. API_DESIGN.md and this guide are updated in same commit as endpoint changes
5. Postman collection examples reflect actual response bodies

## 17. Quick Command Reference

Route inspection:

```bash
php artisan route:list --path=api
```

Seed database:

```bash
php artisan db:seed --no-interaction
```

Run API tests:

```bash
php artisan test tests/Feature/Api
```

Run all tests:

```bash
php artisan test
```

## 18. Document Ownership

Primary maintainers:

- Backend/API contributors

Change rule:

- Any API behavior change must update both:
    - API_DESIGN.md (contract)
    - API_IMPLEMENTATION_GUIDE.md (implementation/process)
