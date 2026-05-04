# Tester Register API Design – Current State (As-Implemented)

## Executive Summary

The Tester Register system includes a fully implemented, production-ready REST API layer (`/api/v1`) that provides comprehensive endpoints for managing testers, fixtures, maintenance schedules, calibration schedules, event logs, and spare parts. However, **this API is not currently consumed by the web frontend**. The frontend uses server-side Livewire components with direct Eloquent database access. This document describes the API's current state as an isolated, well-tested layer available for:

- External API consumers (mobile apps, third-party integrations, desktop tools)
- Future frontend migration (when shifting to a JavaScript-based SPA)
- Interoperability and system extensibility

---

## 1. Architecture Overview

### 1.1 Current System Design

```
┌─────────────────┐
│  Web Browser    │
│  (Vue/Blade)    │
└────────┬────────┘
         │
         └──→ Livewire Components (server-side rendering)
                     │
                     └──→ Direct Eloquent Queries
                              │
                              └──→ Database

┌─────────────────┐
│  External Client│
│  (Mobile, etc)  │
└────────┬────────┘
         │
         └──→ REST API (/api/v1)
                     │
                     └──→ Sanctum Token Auth
                              │
                              └──→ Controllers → Models → Database
```

**Key Observation**: The REST API layer exists in parallel with the Livewire frontend, not integrated with it. The API is fully functional and tested, but the web frontend does not make REST calls.

### 1.2 Why the API Exists

1. **External Integration**: Third-party systems can consume tester management data
2. **Mobile/Desktop Clients**: Native apps can use the same backend contract
3. **Future-Proofing**: Foundation for eventual migration to API-first frontend architecture
4. **Microservices Ready**: API encapsulates business logic independently from UI layer
5. **Testing & Quality**: REST endpoints provide a clean, mockable interface for QA and integration testing

---

## 2. Authentication & Authorization

### 2.1 Auth Endpoints (Public, Rate-Limited)

```
POST /api/v1/auth/register    [Rate limit: 6 req/min]
POST /api/v1/auth/login       [Rate limit: 6 req/min]
POST /api/v1/auth/logout      [Requires auth:sanctum]
```

**Code Reference**: [app/Http/Controllers/Api/AuthController.php](../app/Http/Controllers/Api/AuthController.php)

#### Register Request

```json
{
    "first_name": "string (required)",
    "last_name": "string (required)",
    "phone": "string (required)",
    "email": "string (required, unique:users, email)",
    "password": "string (required, min:8, confirmed)",
    "password_confirmation": "string (required)"
}
```

#### Login Request

```json
{
    "email": "string (required, email, exists:users)",
    "password": "string (required)"
}
```

#### Auth Response

```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "token": "1|abc123def456xyz",
        "token_type": "Bearer",
        "user": {
            "id": 1,
            "first_name": "API",
            "last_name": "User",
            "email": "api@example.com",
            "phone": "+358401234567"
        },
        "roles": ["admin"]
    },
    "code": 201
}
```

**Token Format**: Plain-text bearer token valid for 1440 minutes (configurable via `SANCTUM_TOKEN_EXPIRATION`).

### 2.2 Protected Endpoints

All endpoints except `/api/v1/auth/register` and `/api/v1/auth/login` require:

```
Authorization: Bearer <token>
```

**Code Reference**: [routes/api.php](../routes/api.php) uses `auth:sanctum` middleware on resource routes.

### 2.3 Authorization Model

All endpoints enforce role-based access control via **policies** registered in [app/Providers/AppServiceProvider.php](../app/Providers/AppServiceProvider.php).

#### Role-Based Access Matrix

| Resource            | viewAny                           | view                              | create         | update         | delete |
| ------------------- | --------------------------------- | --------------------------------- | -------------- | -------------- | ------ |
| TesterCustomer      | admin, manager                    | admin, manager                    | admin, manager | admin, manager | admin  |
| Tester              | admin, manager, technician, guest | admin, manager, technician, guest | admin, manager | admin, manager | admin  |
| Fixture             | admin, manager, technician        | admin, manager, technician        | admin, manager | admin, manager | admin  |
| MaintenanceSchedule | admin, manager, technician        | admin, manager, technician        | admin, manager | admin, manager | admin  |
| CalibrationSchedule | admin, manager, technician        | admin, manager, technician        | admin, manager | admin, manager | admin  |
| EventLog            | admin, manager, technician        | admin, manager, technician        | admin, manager | admin, manager | admin  |
| SparePart           | admin, manager, technician        | admin, manager, technician        | admin, manager | admin, manager | admin  |

**Code Reference**: [app/Policies/](../app/Policies/) – BasePolicy implements role alias mapping; individual policies (TesterPolicy, FixturePolicy, etc.) extend BasePolicy.

#### Role Aliases

The system recognizes roles with canonical aliases for permission checking:

- `admin` → `Admin`
- `manager` → `Maintenance Technician`
- `technician` → `Calibration Specialist`
- `guest` → `Guest`

**Code Reference**: [app/Policies/BasePolicy.php](../app/Policies/BasePolicy.php) defines `ROLE_ALIASES` constant.

### 2.4 Default Test Credentials

For development/testing, the [database/seeders/RoleSeeder.php](../database/seeders/RoleSeeder.php) creates:

```
admin@example.com / password: 12345678 → role: admin
manager@example.com / password: 12345678 → role: manager (Maintenance Technician)
technician@example.com / password: 12345678 → role: technician (Calibration Specialist)
calibrator@example.com / password: 12345678 → role: technician
guest@example.com / password: 12345678 → role: guest
```

---

## 3. Standard Response Envelope

### 3.1 Success Response

```json
{
    "success": true,
    "message": "Resource created successfully",
    "data": {
        /* resource object or array */
    },
    "code": 200
}
```

- **code**: HTTP status code (200, 201, 204, etc.)
- **data**: Omitted for DELETE/logout operations

### 3.2 Paginated Response

```json
{
    "success": true,
    "message": "Resources retrieved successfully",
    "data": {
        "items": [
            {
                /* resource objects */
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 42,
            "last_page": 3
        }
    },
    "code": 200
}
```

### 3.3 Error Response

```json
{
    "success": false,
    "message": "Validation failed",
    "code": 422,
    "errors": {
        "email": ["Email has already been taken"],
        "password": ["Password must be at least 8 characters"]
    }
}
```

**Code Reference**: [bootstrap/app.php](../bootstrap/app.php) – Exceptions section handles JSON response formatting.

### 3.4 Error Codes

| Code | Meaning               | Example                                  |
| ---- | --------------------- | ---------------------------------------- |
| 400  | Bad Request           | Malformed query parameters               |
| 401  | Unauthorized          | Missing or invalid token                 |
| 403  | Forbidden             | Insufficient permissions (policy denies) |
| 404  | Not Found             | Resource does not exist                  |
| 405  | Method Not Allowed    | POST to read-only endpoint               |
| 422  | Validation Failed     | Invalid form data                        |
| 429  | Too Many Requests     | Rate limit exceeded                      |
| 500  | Internal Server Error | Unhandled exception                      |

---

## 4. Resource Endpoints

### 4.1 Tester Customers

**Endpoint**: `/api/v1/tester-customers`

#### List (with Search & Pagination)

```
GET /api/v1/tester-customers?page=1&per_page=15&search=acme
```

**Query Parameters**:

- `page` (int): Page number (default: 1)
- `per_page` (int): Items per page (default: 15)
- `search` (string): Filter by customer name

**Response**:

```json
{
    "success": true,
    "message": "Tester customers retrieved successfully",
    "data": {
        "items": [
            {
                "id": 1,
                "name": "ACME Corporation",
                "contact_person": "John Doe",
                "email": "contact@acme.com",
                "phone": "+358401234567",
                "address": "123 Main St, City",
                "created_at": "2024-01-15T10:30:00Z",
                "updated_at": "2024-01-20T14:22:00Z"
            }
        ],
        "pagination": {
            /* ... */
        }
    },
    "code": 200
}
```

**Code Reference**: [app/Http/Controllers/Api/TesterCustomerController.php#index](../app/Http/Controllers/Api/TesterCustomerController.php)

#### Create

```
POST /api/v1/tester-customers
Content-Type: application/json
Authorization: Bearer <token>
```

**Request Body**:

```json
{
    "name": "string (required, max:100)",
    "contact_person": "string (required, max:100)",
    "email": "string (required, email, unique)",
    "phone": "string (required, max:20)",
    "address": "string (nullable, max:255)"
}
```

**Code Reference**: [app/Http/Requests/Api/StoreTesterCustomerRequest.php](../app/Http/Requests/Api/StoreTesterCustomerRequest.php)

#### Retrieve

```
GET /api/v1/tester-customers/{id}
```

#### Update

```
PATCH /api/v1/tester-customers/{id}
Content-Type: application/json
Authorization: Bearer <token>
```

#### Delete

```
DELETE /api/v1/tester-customers/{id}
Authorization: Bearer <token>
```

---

### 4.2 Testers

**Endpoint**: `/api/v1/testers`

#### List (with Filtering)

```
GET /api/v1/testers?page=1&per_page=15&status=active&customer_id=1&search=T001
```

**Query Parameters**:

- `page`, `per_page`: Pagination
- `status` (string): Filter by status (active, inactive, maintenance)
- `customer_id` (int): Filter by customer
- `search` (string): Filter by model or serial number

**Response Fields**:

```json
{
    "id": 1,
    "customer_id": 1,
    "name": "T001",
    "model": "DMM-2000",
    "serial_number": "SN-12345",
    "purchase_date": "2023-06-15",
    "status": "active",
    "location": "Lab A",
    "notes": "Last calibrated 2024-03-20",
    "created_at": "2023-06-15T09:00:00Z",
    "updated_at": "2024-04-01T16:30:00Z"
}
```

**Note**: `name` and `model` fields use legacy naming for backward compatibility with existing external consumers.

**Code Reference**: [app/Http/Controllers/Api/TesterController.php](../app/Http/Controllers/Api/TesterController.php) – toLegacyPayload() method

#### Create

```
POST /api/v1/testers
```

**Request Body**:

```json
{
    "customer_id": "int (required, exists:tester_customers)",
    "model": "string (required, max:100)",
    "serial_number": "string (required, unique)",
    "purchase_date": "date (nullable)",
    "status": "string (in:active,inactive,maintenance; default:active)",
    "location": "string (nullable, max:255)",
    "notes": "string (nullable)"
}
```

**Code Reference**: [app/Http/Requests/Api/StoreTesterRequest.php](../app/Http/Requests/Api/StoreTesterRequest.php)

#### Update Status (Custom Action)

```
PATCH /api/v1/testers/{id}/status
Content-Type: application/json
Authorization: Bearer <token>
```

**Request Body**:

```json
{
    "status": "string (in:active,inactive,maintenance)"
}
```

**Code Reference**: [app/Http/Controllers/Api/TesterController.php#updateStatus](../app/Http/Controllers/Api/TesterController.php)

---

### 4.3 Fixtures

**Endpoint**: `/api/v1/fixtures`

#### List

```
GET /api/v1/fixtures?page=1&per_page=15&tester_id=1
```

**Query Parameters**:

- `tester_id` (int): Filter by parent tester (optional)

#### Create

```
POST /api/v1/fixtures
```

**Request Body**:

```json
{
    "tester_id": "int (required, exists:testers)",
    "name": "string (required, max:100)",
    "serial_number": "string (required, unique:fixtures,serial_number)",
    "type": "string (nullable, max:100)",
    "metadata": "object (nullable; stored as JSON)"
}
```

**Code Reference**: [app/Http/Requests/Api/StoreFixtureRequest.php](../app/Http/Requests/Api/StoreFixtureRequest.php)

**Note**: Fixture metadata is encoded in JSON format for legacy field mapping. See [app/Http/Controllers/Api/FixtureController.php](../app/Http/Controllers/Api/FixtureController.php) for transformation details.

---

### 4.4 Maintenance Schedules

**Endpoint**: `/api/v1/maintenance-schedules`

#### Create

```
POST /api/v1/maintenance-schedules
```

**Request Body**:

```json
{
    "tester_id": "int (required, exists:testers)",
    "scheduled_date": "date (required)",
    "description": "string (nullable)",
    "maintenance_type": "string (nullable, max:100)"
}
```

#### Complete (Custom Action)

```
POST /api/v1/maintenance-schedules/{id}/complete
Content-Type: application/json
Authorization: Bearer <token>
```

**Request Body**:

```json
{
    "completed_date": "date (required, before_or_equal:today)",
    "performed_by": "string (required, exists:users,email)",
    "notes": "string (nullable)"
}
```

**Side Effects**:

- Updates maintenance schedule `maintenance_status` to 'completed'
- Creates associated TesterEventLog entry with event_type='maintenance'
- Records performed_by user ID and completion timestamp

**Code Reference**: [app/Http/Controllers/Api/MaintenanceScheduleController.php#complete](../app/Http/Controllers/Api/MaintenanceScheduleController.php)

**Code Reference**: [app/Http/Requests/Api/CompleteMaintenanceRequest.php](../app/Http/Requests/Api/CompleteMaintenanceRequest.php)

---

### 4.5 Calibration Schedules

**Endpoint**: `/api/v1/calibration-schedules`

Follows same pattern as maintenance schedules. Completion endpoint:

```
POST /api/v1/calibration-schedules/{id}/complete
```

**Side Effects**: Creates TesterEventLog with event_type='calibration'

**Code Reference**: [app/Http/Controllers/Api/CalibrationScheduleController.php](../app/Http/Controllers/Api/CalibrationScheduleController.php)

---

### 4.6 Event Logs

**Endpoint**: `/api/v1/event-logs`

#### List (with Filtering)

```
GET /api/v1/event-logs?page=1&type=maintenance&date_from=2024-01-01&date_to=2024-01-31
```

**Query Parameters**:

- `type` (string): Filter by event type (maintenance, calibration, repair, etc.)
- `date_from` (date): Filter events on or after this date
- `date_to` (date): Filter events on or before this date

#### Create

```
POST /api/v1/event-logs
```

**Request Body**:

```json
{
    "tester_id": "int (required, exists:testers)",
    "type": "string (required, in:maintenance,calibration,repair,inspection)",
    "event_date": "date (required)",
    "description": "string (required)",
    "metadata": "object (nullable)"
}
```

**Code Reference**: [app/Http/Controllers/Api/EventLogController.php](../app/Http/Controllers/Api/EventLogController.php)

---

### 4.7 Spare Parts

**Endpoint**: `/api/v1/spare-parts`

#### List (with Stock Filter)

```
GET /api/v1/spare-parts?page=1&stock_status=low
```

**Query Parameters**:

- `stock_status` (string): Filter by stock_status (low, normal, full)

The `stock_status` is a computed attribute on SparePart model based on:

- `low`: quantity <= minimum_quantity
- `normal`: quantity between minimum_quantity and maximum_quantity
- `full`: quantity >= maximum_quantity

**Code Reference**: [app/Http/Controllers/Api/SparePartController.php#index](../app/Http/Controllers/Api/SparePartController.php)

**Response Fields**:

```json
{
    "id": 1,
    "part_number": "SP-001",
    "description": "Replacement battery",
    "quantity": 5,
    "minimum_quantity": 2,
    "maximum_quantity": 20,
    "unit_cost": 45.99,
    "supplier_id": 1,
    "stock_status": "normal",
    "created_at": "2024-01-10T08:00:00Z",
    "updated_at": "2024-04-15T14:22:00Z"
}
```

---

## 5. Current Integration Status

### 5.1 Frontend Integration

**Status**: ❌ **Not Integrated**

The web frontend (built with Livewire Volt components) does **not** make HTTP calls to `/api/v1` endpoints. Instead:

1. **Data Access**: Livewire components use direct Eloquent queries (`Model::find()`, `Model::all()`, etc.)
2. **Mutations**: Form submissions call Livewire server-side methods which directly mutate models
3. **Authorization**: Permission checks occur via `Gate::allows()` within Livewire component methods
4. **Rendering**: Views are server-side Blade templates, rendered with data fetched directly from the database

**Files demonstrating this pattern**:

- [app/Livewire/Pages/Testers/Index.php](../app/Livewire/Pages/Testers/Index.php) – Direct Eloquent query, no API call
- [app/Livewire/Pages/Inventory/SpareParts/Index.php](../app/Livewire/Pages/Inventory/SpareParts/Index.php) – Direct DB access
- [resources/views/...] – Blade templates render data passed directly from controllers/Livewire

### 5.2 External Consumer Integration

**Status**: ✅ **Available & Tested**

The API is fully available for external clients:

1. **Postman Collection**: [Tester_Register_API.postman_collection.json](../Tester_Register_API.postman_collection.json)
2. **Test Coverage**: [tests/Feature/Api/](../tests/Feature/Api/) contains comprehensive feature tests
3. **Documentation**: This file and [API_IMPLEMENTATION_GUIDE.md](../API_IMPLEMENTATION_GUIDE.md)

External consumers (mobile apps, third-party tools) can authenticate with bearer tokens and use all endpoints.

### 5.3 Internal API Testing

**Status**: ✅ **Comprehensive**

Test suite in [tests/Feature/Api/](../tests/Feature/Api/):

- **[ApiSmokeTest.php](../tests/Feature/Api/ApiSmokeTest.php)**: Tests register, login, auth checks, full CRUD on all resources, maintenance/calibration completion with event log creation
- **[IssueEventLogHistoryTest.php](../tests/Feature/Api/IssueEventLogHistoryTest.php)**: Verifies event log creation behavior on maintenance/calibration completion

**Run Tests**:

```bash
php artisan test tests/Feature/Api/
```

---

## 6. Design Rationale

The API exists as an isolated layer for several strategic reasons:

### 6.1 External Interoperability

The Tester Register system may need to exchange data with:

- Mobile apps for field technicians
- ERP systems for inventory management
- Analytics platforms for historical data
- Third-party calibration services

A clean REST API enables these integrations without coupling to the web UI.

### 6.2 Future Frontend Migration

If the system evolves toward a JavaScript-based SPA (React, Vue, etc.), the API provides a ready-made contract. No backend changes needed—only frontend refactoring.

### 6.3 Separation of Concerns

- **Backend Concern**: REST API enforces consistent data validation, authorization, and response formatting
- **Frontend Concern**: Livewire handles user interactions and server-side rendering
- **Benefit**: Each layer can evolve independently

### 6.4 Quality Assurance

A well-tested API layer allows:

- Automated integration tests (mocking HTTP calls)
- Contract testing with consumers
- Load testing and performance profiling
- Clear service boundaries for debugging

### 6.5 Scalability

As the system grows, the API layer can be:

- Deployed to separate servers
- Scaled horizontally with load balancing
- Cached with API gateway layers
- Versioned independently from frontend

---

## 7. Current Limitations & Considerations

### 7.1 No Frontend Synchronization

If external clients modify data via the API, the **web frontend will not see real-time updates**. This is expected in the current architecture since:

- Web frontend uses direct Eloquent queries (no polling)
- No WebSocket or event broadcasting to sync API changes
- Frontend assumes it is the sole writer to the database

**Mitigation for Future**: Event broadcasting can be added when frontend moves to API consumption.

### 7.2 Legacy Field Mapping

For backward compatibility with existing external consumers, the API uses legacy field names:

- Tester `model` field is stored as `name` in database
- Tester `serial_number` maps to `id_number_by_customer`
- Fixture metadata encoded as JSON string

See [app/Http/Controllers/Api/TesterController.php#toLegacyPayload](../app/Http/Controllers/Api/TesterController.php) for transformation logic.

### 7.3 Rate Limiting

Auth endpoints (register, login) are rate-limited to 6 requests per minute to prevent brute-force attacks. Adjust via `config/rate-limiting` if needed.

---

## 8. Security Considerations

### 8.1 Token Security

- Tokens are plain-text bearer tokens stored in Sanctum token table
- Tokens expire after 1440 minutes (configurable)
- No token refresh endpoint; reissue via login

### 8.2 CORS & Origin Validation

If enabling CORS for cross-origin requests, configure `config/cors.php` to whitelist trusted origins.

### 8.3 Input Validation

All endpoints validate input via FormRequest classes (code reference: [app/Http/Requests/Api/](../app/Http/Requests/Api/)).

### 8.4 Authorization Enforcement

Policies check user roles before allowing operations. See [app/Policies/](../app/Policies/) for authorization matrix.

---

## 9. Monitoring & Observability

### 9.1 Logging

API requests and errors are logged to `storage/logs/laravel.log`. Enable debug logging in `.env` to capture detailed request/response bodies.

### 9.2 Error Tracking

Unhandled exceptions are formatted as JSON error responses in [bootstrap/app.php](../bootstrap/app.php) exception handler.

### 9.3 Performance Monitoring

Add Laravel Telescope or New Relic for query profiling and API performance tracking.

---

## 10. Next Steps & Migration Path

For future adoption of the API by the frontend, see **API_DESIGN_FUTURE_MIGRATION.md**. That document outlines:

- Phased migration strategy (which pages to migrate first)
- Frontend authentication & token management
- Caching & data synchronization strategy
- Breaking changes to avoid

---

## Appendix: Key Files Reference

| Purpose                | File                                        | Lines                     |
| ---------------------- | ------------------------------------------- | ------------------------- |
| Route definitions      | routes/api.php                              | ~220                      |
| Auth logic             | app/Http/Controllers/Api/AuthController.php | ~220                      |
| CRUD controllers       | app/Http/Controllers/Api/                   | ~2800 total               |
| Request validation     | app/Http/Requests/Api/                      | ~18 classes               |
| Authorization policies | app/Policies/                               | ~7 classes                |
| Models                 | app/Models/                                 | ~8 core models            |
| Response formatting    | bootstrap/app.php                           | Exception handler section |
| Test suite             | tests/Feature/Api/                          | ~2 main test files        |
| Collection             | Tester_Register_API.postman_collection.json | Postman import            |

---

**Document Version**: 1.0 (Current State)  
**Last Updated**: 2026-04-30  
**API Version**: v1  
**Frontend Integration Status**: Not Integrated (Livewire-based)  
**External Consumer Status**: Available & Tested
