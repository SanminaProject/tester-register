# Tester Register API Design (Code-Accurate)

## 1. Scope and Base Path

This document describes the implemented API under `/api/v1`.

- Base URL example: `http://localhost:8000`
- API prefix: `/api/v1`
- Content type: `application/json`

## 2. Authentication and Security

- Public endpoints:
    - `POST /api/v1/auth/register`
    - `POST /api/v1/auth/login`
- Both public auth endpoints are rate-limited with `throttle:6,1`.
- All other endpoints are protected by `auth:sanctum`.
- Protected calls must send: `Authorization: Bearer <token>`.

### 2.1 Register Request (implemented fields)

```json
{
    "first_name": "API",
    "last_name": "User",
    "phone": "+358401234567",
    "email": "api-user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### 2.2 Login Request

```json
{
    "email": "api-user@example.com",
    "password": "password123"
}
```

### 2.3 Auth Response Data

Successful register/login returns token payload:

```json
{
    "token": "<plain-text-token>",
    "token_type": "Bearer",
    "user": {},
    "roles": []
}
```

Token expiration is based on Sanctum config (`SANCTUM_TOKEN_EXPIRATION`, default 1440 minutes).

## 3. Standard Response Envelope

### 3.1 Success

```json
{
    "success": true,
    "message": "Human readable message",
    "data": {},
    "code": 200
}
```

Note: for some successful actions (for example delete/logout), `data` may be omitted.

### 3.2 Paginated Success

```json
{
    "success": true,
    "message": "...",
    "data": {
        "items": [],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 0,
            "last_page": 1
        }
    },
    "code": 200
}
```

### 3.3 Error

```json
{
    "success": false,
    "message": "Validation failed",
    "code": 422,
    "errors": {
        "field": ["Validation message"]
    }
}
```

`errors` is returned for validation failures.

## 4. Status Codes Used by the API

- `200` OK
- `201` Created
- `401` Unauthenticated
- `403` Forbidden
- `404` Resource not found
- `405` Method not allowed
- `409` Conflict (customer cannot be deleted when testers are linked)
- `422` Validation failed
- `429` Too Many Requests
- `500` Internal server error

## 5. Endpoint Inventory

## 5.1 Authentication

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout` (auth required)

## 5.2 Customers

- `GET /api/v1/customers`
- `POST /api/v1/customers`
- `GET /api/v1/customers/{customer}`
- `PATCH /api/v1/customers/{customer}`
- `DELETE /api/v1/customers/{customer}`

List filters:

- `page`
- `per_page`
- `search`

## 5.3 Testers

- `GET /api/v1/testers`
- `POST /api/v1/testers`
- `GET /api/v1/testers/{tester}`
- `PATCH /api/v1/testers/{tester}`
- `DELETE /api/v1/testers/{tester}`
- `PATCH /api/v1/testers/{tester}/status`

List filters:

- `page`
- `per_page`
- `status` (`active|inactive|maintenance`)
- `customer_id`
- `search`

Create/update payload shape:

- `customer_id`
- `model`
- `serial_number`
- `purchase_date`
- `status`
- `location`
- `notes`

Status endpoint payload:

```json
{
    "status": "maintenance"
}
```

## 5.4 Fixtures

- `GET /api/v1/fixtures`
- `POST /api/v1/fixtures`
- `GET /api/v1/fixtures/{fixture}`
- `PATCH /api/v1/fixtures/{fixture}`
- `DELETE /api/v1/fixtures/{fixture}`

List filters:

- `page`
- `per_page`
- `tester_id`
- `status` (`active|inactive|maintenance`)
- `search`

Create/update payload shape:

- `tester_id`
- `name`
- `serial_number`
- `purchase_date`
- `status`
- `location`
- `notes`

## 5.5 Maintenance Schedules

- `GET /api/v1/maintenance-schedules`
- `POST /api/v1/maintenance-schedules`
- `GET /api/v1/maintenance-schedules/{maintenanceSchedule}`
- `PATCH /api/v1/maintenance-schedules/{maintenanceSchedule}`
- `DELETE /api/v1/maintenance-schedules/{maintenanceSchedule}`
- `POST /api/v1/maintenance-schedules/{maintenanceSchedule}/complete`

List filters:

- `page`
- `per_page`
- `tester_id`
- `status` (`scheduled|completed|overdue`)
- `start_date`
- `end_date`

Create payload:

- `tester_id` (required)
- `scheduled_date` (required)
- `procedure` (required)
- `notes` (optional)

Complete payload:

```json
{
    "completed_date": "2026-04-02",
    "performed_by": "Tech User",
    "notes": "Routine preventive maintenance complete"
}
```

## 5.6 Calibration Schedules

- `GET /api/v1/calibration-schedules`
- `POST /api/v1/calibration-schedules`
- `GET /api/v1/calibration-schedules/{calibrationSchedule}`
- `PATCH /api/v1/calibration-schedules/{calibrationSchedule}`
- `DELETE /api/v1/calibration-schedules/{calibrationSchedule}`
- `POST /api/v1/calibration-schedules/{calibrationSchedule}/complete`

List filters:

- `page`
- `per_page`
- `tester_id`
- `status` (`scheduled|completed|overdue`)
- `start_date`
- `end_date`

Create payload:

- `tester_id` (required)
- `scheduled_date` (required)
- `procedure` (required)
- `notes` (optional)

Complete payload is the same shape as maintenance complete.

## 5.7 Event Logs

- `GET /api/v1/event-logs`
- `POST /api/v1/event-logs`
- `GET /api/v1/event-logs/{eventLog}`
- `PATCH /api/v1/event-logs/{eventLog}`
- `DELETE /api/v1/event-logs/{eventLog}`

List filters:

- `page`
- `per_page`
- `tester_id`
- `type` (`maintenance|calibration|issue|problem|solution|repair|other`)
- `start_date`
- `end_date`

Create/update payload:

- `tester_id` (required)
- `type` (required)
- `event_date` (required)
- `description` (required)
- `performed_by` (optional)
- `metadata` (optional object)

`metadata` may include keys such as:

- `maintenance_schedule_id`
- `calibration_schedule_id`
- `resolution_description`

## 5.8 Spare Parts

- `GET /api/v1/spare-parts`
- `POST /api/v1/spare-parts`
- `GET /api/v1/spare-parts/{sparePart}`
- `PATCH /api/v1/spare-parts/{sparePart}`
- `DELETE /api/v1/spare-parts/{sparePart}`

List filters:

- `page`
- `per_page`
- `search`
- `stock_status` (`low|normal|full`)

Create/update payload fields:

- `name`
- `part_number`
- `quantity_in_stock`
- `unit_cost`
- `supplier`
- `notes`

## 6. Authorization Matrix (Policies)

Role aliases are supported in policy checks:

- admin aliases: `admin`, `Admin`
- manager aliases: `manager`, `Manager`, `Maintenance Technician`
- technician aliases: `technician`, `Technician`, `Calibration Specialist`
- guest aliases: `guest`, `Guest`

| Resource / Action | Admin | Manager | Technician | Guest |
| --- | --- | --- | --- | --- |
| Auth register/login/logout | Yes | Yes | Yes | Yes |
| Customers view/list | Yes | Yes | No | No |
| Customers create/update | Yes | Yes | No | No |
| Customers delete | Yes | No | No | No |
| Testers view/list | Yes | Yes | Yes | Yes |
| Testers create/update/updateStatus | Yes | Yes | No | No |
| Testers delete | Yes | No | No | No |
| Fixtures view/list | Yes | Yes | Yes | Yes |
| Fixtures create/update | Yes | Yes | No | No |
| Fixtures delete | Yes | No | No | No |
| Maintenance view/list | Yes | Yes | Yes | No |
| Maintenance create | Yes | Yes | No | No |
| Maintenance update/complete | Yes | Yes | Yes | No |
| Maintenance delete | Yes | Yes | No | No |
| Calibration view/list | Yes | Yes | Yes | No |
| Calibration create | Yes | Yes | No | No |
| Calibration update/complete | Yes | Yes | Yes | No |
| Calibration delete | Yes | Yes | No | No |
| Event logs list/show/create/update/delete | Yes | Yes | Yes | No |
| Spare parts list/show | Yes | Yes | Yes | Yes |
| Spare parts create/update | Yes | Yes | No | No |
| Spare parts delete | Yes | No | No | No |

## 7. Notes About API Shape

- API endpoints expose a modern request contract while mapping to a legacy schema internally (for example tester `model` maps to database column `name`).
- Some resources normalize date values to day boundaries before storage (notably event logs and completion actions).
