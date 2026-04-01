# Tester Register API Contract (V15)

Status: Active
Version: V15
Last Updated: 2026-04-01

## 1. Scope

This document defines the formal API contract for the Tester Register backend.
It covers:

- Endpoint list and method contracts
- Request and response fields
- Validation rules
- Error codes and error body expectations
- Authorization and permission matrix

## 2. Base URL and Versioning

Note:

- `Version: V15` in this file refers to document revision based on chenqi commit order.
- API route version is still `/api/v1` and is unchanged.

- Base URL (local): http://localhost:8000
- API prefix: /api/v1
- Default content type: application/json

## 3. Authentication

Authentication method:

- Bearer token via Laravel Sanctum

Header:

- Authorization: Bearer <access_token>

Public endpoints:

- POST /api/v1/auth/register
- POST /api/v1/auth/login

Protected endpoints:

- All other /api/v1 routes in this contract require auth:sanctum

## 4. Common Response Conventions

### 4.1 Business Success Envelope

Most successful business responses use:

```json
{
    "success": true,
    "message": "Human readable message",
    "data": {},
    "code": 200
}
```

Notes:

- code mirrors the HTTP status code in business responses.
- list endpoints return data.items and data.pagination.

### 4.2 Framework Error Envelopes

Validation and framework exceptions can return Laravel default JSON bodies.
Typical validation body:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field": ["Validation message"]
    }
}
```

## 5. Error Codes

| HTTP | Meaning               | Typical Trigger                                 | Body Notes                                        |
| ---- | --------------------- | ----------------------------------------------- | ------------------------------------------------- |
| 200  | OK                    | Query/update/delete success                     | success=true                                      |
| 201  | Created               | Resource created or registration success        | success=true                                      |
| 401  | Unauthorized          | Missing/invalid token, or bad login credentials | For login failure, custom body with success=false |
| 403  | Forbidden             | Authenticated but policy denies action          | Framework authorization error                     |
| 404  | Not Found             | Missing route model binding id                  | Framework not found error                         |
| 409  | Conflict              | Delete customer with linked testers             | Custom conflict body                              |
| 422  | Unprocessable Entity  | Validation failure                              | Validation errors object                          |
| 500  | Internal Server Error | Unhandled server exception                      | Should be treated as non-contract failure         |

## 6. Role Model and Permission Matrix

### 6.1 Canonical Roles and Aliases

| Canonical Role | Alias Names Recognized by Policy   |
| -------------- | ---------------------------------- |
| admin          | admin, Admin                       |
| manager        | manager, Maintenance Technician    |
| technician     | technician, Calibration Specialist |
| guest          | guest, Guest                       |

### 6.2 Permission Matrix

Legend:

- Y = allowed
- N = denied
- N/A = endpoint not provided

| Resource / Action           |                guest | technician |   manager |     admin |
| --------------------------- | -------------------: | ---------: | --------: | --------: |
| Auth register/login         |                    Y |          Y |         Y |         Y |
| Auth logout                 | Y (if authenticated) |          Y |         Y |         Y |
| Customers list/show         |                    Y |          Y |         Y |         Y |
| Customers create            |                    N |          N |         Y |         Y |
| Customers update            |                    N |          N |         Y |         Y |
| Customers delete            |                    N |          N |         N |         Y |
| Testers list/show           |                    Y |          Y |         Y |         Y |
| Testers create              |                    N |          N |         Y |         Y |
| Testers update/status       |                    N |          N |         Y |         Y |
| Testers delete              |                    N |          N |         N |         Y |
| Fixtures list/show          |                    Y |          Y |         Y |         Y |
| Fixtures create             |                    N |          N |         Y |         Y |
| Fixtures update             |                    N |          N |         Y |         Y |
| Fixtures delete             |                    N |          N |         N |         Y |
| Maintenance list/show       |                    N |          Y |         Y |         Y |
| Maintenance create          |                    N |          N |         Y |         Y |
| Maintenance update/complete |                    N |          Y |         Y |         Y |
| Maintenance delete          |                    N |          N |         N |         Y |
| Calibration list/show       |                    N |          Y |         Y |         Y |
| Calibration create          |                    N |          N |         Y |         Y |
| Calibration update/complete |                    N |          Y |         Y |         Y |
| Calibration delete          |                    N |          N |         N |         Y |
| Event logs list/show        |                    N |          Y |         Y |         Y |
| Event logs create           |                    N |          Y |         Y |         Y |
| Event logs update           |                  N/A |        N/A |       N/A |       N/A |
| Event logs delete           |            N/A route |  N/A route | N/A route | N/A route |
| Spare parts list/show       |                    N |          Y |         Y |         Y |
| Spare parts create          |                    N |          N |         Y |         Y |
| Spare parts update          |                    N |          N |         Y |         Y |
| Spare parts delete          |                    N |          N |         N |         Y |

## 7. Endpoint Contracts

## 7.1 Auth

### POST /api/v1/auth/register

Purpose:

- Create user account and issue access token

Request body fields:

| Field                 | Type   | Required | Rules                     |
| --------------------- | ------ | -------: | ------------------------- |
| name                  | string |        Y | max:255                   |
| email                 | string |        Y | email, unique:users,email |
| password              | string |        Y | min:8, confirmed          |
| password_confirmation | string |        Y | must match password       |

Example request:

```json
{
    "name": "API User",
    "email": "api-user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Example success (201):

```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "access_token": "1|token...",
        "token_type": "Bearer",
        "user": {
            "id": 10,
            "name": "API User",
            "email": "api-user@example.com",
            "roles": []
        }
    },
    "code": 201
}
```

### POST /api/v1/auth/login

Request body fields:

| Field    | Type   | Required | Rules |
| -------- | ------ | -------: | ----- |
| email    | string |        Y | email |
| password | string |        Y | min:6 |

Example request:

```json
{
    "email": "admin@example.com",
    "password": "12345678"
}
```

Example success (200):

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "access_token": "1|token...",
        "token_type": "Bearer",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com",
            "roles": ["Admin"]
        }
    },
    "code": 200
}
```

Example failure (401):

```json
{
    "success": false,
    "message": "Invalid email or password",
    "error": "UnauthorizedException",
    "code": 401
}
```

### POST /api/v1/auth/logout

Headers:

- Authorization: Bearer <token>

Example success (200):

```json
{
    "success": true,
    "message": "Logout successful",
    "code": 200
}
```

## 7.2 Customers

Endpoints:

- GET /api/v1/customers
- POST /api/v1/customers
- GET /api/v1/customers/{customer}
- PUT/PATCH /api/v1/customers/{customer}
- DELETE /api/v1/customers/{customer}

List query params:

| Param    | Type    | Required | Notes                       |
| -------- | ------- | -------: | --------------------------- |
| page     | integer |        N | default 1                   |
| per_page | integer |        N | default 15                  |
| search   | string  |        N | company_name/email contains |

Create fields:

| Field          | Type   | Required | Rules                   |
| -------------- | ------ | -------: | ----------------------- |
| company_name   | string |        Y | unique:tester_customers |
| address        | string |        Y | -                       |
| contact_person | string |        Y | -                       |
| phone          | string |        Y | -                       |
| email          | string |        Y | email                   |

Update fields:

- company_name, address, contact_person, phone, email (all optional)

Conflict rule:

- DELETE returns 409 if customer has linked testers

Example create request:

```json
{
    "company_name": "Acme Inc",
    "address": "1 Infinite Loop",
    "contact_person": "John Doe",
    "phone": "+1-555-1234",
    "email": "contact@acme.example"
}
```

Example create success (201):

```json
{
    "success": true,
    "message": "Customer created successfully",
    "data": {
        "id": 1,
        "company_name": "Acme Inc",
        "address": "1 Infinite Loop",
        "contact_person": "John Doe",
        "phone": "+1-555-1234",
        "email": "contact@acme.example",
        "created_at": "2026-04-01T09:00:00.000000Z",
        "updated_at": "2026-04-01T09:00:00.000000Z"
    },
    "code": 201
}
```

## 7.3 Testers

Endpoints:

- GET /api/v1/testers
- POST /api/v1/testers
- GET /api/v1/testers/{tester}
- PUT/PATCH /api/v1/testers/{tester}
- PATCH /api/v1/testers/{tester}/status
- DELETE /api/v1/testers/{tester}

List query params:

- page, per_page, status, customer_id, search

Create fields:

| Field         | Type    | Required | Rules                       |
| ------------- | ------- | -------: | --------------------------- |
| model         | string  |        Y | max:100                     |
| serial_number | string  |        Y | unique:testers, max:50      |
| customer_id   | integer |        Y | exists:tester_customers,id  |
| purchase_date | date    |        Y | YYYY-MM-DD                  |
| status        | string  |        N | active/inactive/maintenance |
| location      | string  |        N | nullable                    |

Update status fields:

- status (required, active/inactive/maintenance)

Example update status request:

```json
{
    "status": "maintenance"
}
```

## 7.4 Fixtures

Endpoints:

- GET /api/v1/fixtures
- POST /api/v1/fixtures
- GET /api/v1/fixtures/{fixture}
- PUT/PATCH /api/v1/fixtures/{fixture}
- DELETE /api/v1/fixtures/{fixture}

List query params:

- page, per_page, tester_id, status, search

Create fields:

| Field         | Type    | Required | Rules             |
| ------------- | ------- | -------: | ----------------- |
| name          | string  |        Y | -                 |
| serial_number | string  |        Y | unique:fixtures   |
| tester_id     | integer |        Y | exists:testers,id |
| purchase_date | date    |        Y | YYYY-MM-DD        |
| status        | string  |        N | active/inactive   |

## 7.5 Maintenance Schedules

Endpoints:

- GET /api/v1/maintenance-schedules
- POST /api/v1/maintenance-schedules
- GET /api/v1/maintenance-schedules/{schedule}
- PUT/PATCH /api/v1/maintenance-schedules/{schedule}
- DELETE /api/v1/maintenance-schedules/{schedule}
- POST /api/v1/maintenance-schedules/{schedule}/complete

List query params:

- page, per_page, tester_id, status, start_date, end_date

Create fields:

| Field          | Type    | Required | Rules             |
| -------------- | ------- | -------: | ----------------- |
| tester_id      | integer |        Y | exists:testers,id |
| scheduled_date | date    |        Y | YYYY-MM-DD        |
| procedure      | string  |        Y | -                 |
| notes          | string  |        N | nullable          |

Complete fields:

| Field          | Type   | Required | Rules      |
| -------------- | ------ | -------: | ---------- |
| completed_date | date   |        Y | YYYY-MM-DD |
| performed_by   | string |        Y | -          |
| notes          | string |        N | nullable   |

## 7.6 Calibration Schedules

Endpoints:

- GET /api/v1/calibration-schedules
- POST /api/v1/calibration-schedules
- GET /api/v1/calibration-schedules/{schedule}
- PUT/PATCH /api/v1/calibration-schedules/{schedule}
- DELETE /api/v1/calibration-schedules/{schedule}
- POST /api/v1/calibration-schedules/{schedule}/complete

List query params:

- page, per_page, tester_id, status, start_date, end_date

Create and complete fields:

- Same rules as maintenance schedules

## 7.7 Event Logs

Endpoints:

- GET /api/v1/event-logs
- POST /api/v1/event-logs
- GET /api/v1/event-logs/{log}

List query params:

- page, per_page, tester_id, type, start_date, end_date

Create fields:

| Field       | Type     | Required | Rules                                      |
| ----------- | -------- | -------: | ------------------------------------------ |
| tester_id   | integer  |        Y | exists:testers,id                          |
| type        | string   |        Y | maintenance/calibration/issue/repair/other |
| description | string   |        Y | -                                          |
| event_date  | datetime |        Y | format: YYYY-MM-DD HH:MM:SS                |

Behavior notes:

- recorded_by is filled from authenticated user name (or System fallback)

Example create request:

```json
{
    "tester_id": 1,
    "type": "maintenance",
    "description": "Routine maintenance completed",
    "event_date": "2026-04-01 10:30:00"
}
```

## 7.8 Spare Parts

Endpoints:

- GET /api/v1/spare-parts
- POST /api/v1/spare-parts
- GET /api/v1/spare-parts/{part}
- PUT/PATCH /api/v1/spare-parts/{part}
- DELETE /api/v1/spare-parts/{part}

List query params:

- page, per_page, search, stock_status
- stock_status values: low, normal, full

Create fields:

| Field             | Type    | Required | Rules              |
| ----------------- | ------- | -------: | ------------------ |
| name              | string  |        Y | -                  |
| part_number       | string  |        Y | unique:spare_parts |
| quantity_in_stock | integer |        Y | min:0              |
| unit_cost         | number  |        Y | min:0              |
| supplier          | string  |        N | nullable           |

Update fields:

- name, part_number, quantity_in_stock, unit_cost, supplier (all optional)

Derived field:

- stock_status is computed from quantity_in_stock:
    - low: <= 5
    - normal: 6-20
    - full: > 20

## 8. Non-Functional Contract Notes

- All protected endpoints require valid Sanctum token.
- Route model binding ids must exist, otherwise 404.
- Validation failures return 422.
- Authorization denials return 403.
- For stable client integrations, clients should rely on HTTP status first, then parse body fields.

## 9. Change Control

When changing any endpoint behavior, update this file in the same commit as code changes.
Recommended minimum checks for contract changes:

- Update API tests in tests/Feature/Api
- Re-run full suite: php artisan test
- Re-export Postman collection if examples changed
