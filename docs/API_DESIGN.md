# Tester Register API Design

## 1. Overview

- Base URL: `http://localhost:8000`
- Prefix: `/api/v1`
- Authentication: Bearer Token (Laravel Sanctum)
- Content type: `application/json`

## 2. Response Envelope

### 2.1 Success

```json
{
    "success": true,
    "message": "Human readable message",
    "data": {},
    "code": 200
}
```

### 2.2 Error

```json
{
    "success": false,
    "message": "Error message",
    "code": 422,
    "errors": {
        "field": ["Validation message"]
    }
}
```

`errors` is only included for validation failures.

## 3. HTTP Status Codes

- `200` OK
- `201` Created
- `401` Unauthenticated
- `403` Forbidden
- `404` Resource not found
- `409` Conflict
- `422` Validation failed
- `500` Internal server error

## 4. Authentication Endpoints

### Register

- `POST /api/v1/auth/register`
- Body:

```json
{
    "name": "API User",
    "email": "api-user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Login

- `POST /api/v1/auth/login`
- Body:

```json
{
    "email": "api-user@example.com",
    "password": "password123"
}
```

### Logout

- `POST /api/v1/auth/logout`
- Auth required

## 5. Resource Endpoints

## 5.1 Customers

- `GET /api/v1/customers`
- `POST /api/v1/customers`
- `GET /api/v1/customers/{customer}`
- `PATCH /api/v1/customers/{customer}`
- `DELETE /api/v1/customers/{customer}`

Filter params:

- `page`
- `per_page`
- `search`

## 5.2 Testers

- `GET /api/v1/testers`
- `POST /api/v1/testers`
- `GET /api/v1/testers/{tester}`
- `PATCH /api/v1/testers/{tester}`
- `DELETE /api/v1/testers/{tester}`
- `PATCH /api/v1/testers/{tester}/status`

Filter params:

- `page`
- `per_page`
- `status`: `active|inactive|maintenance`
- `customer_id`
- `search`

Example status update body:

```json
{
    "status": "maintenance"
}
```

## 5.3 Fixtures

- `GET /api/v1/fixtures`
- `POST /api/v1/fixtures`
- `GET /api/v1/fixtures/{fixture}`
- `PATCH /api/v1/fixtures/{fixture}`
- `DELETE /api/v1/fixtures/{fixture}`

Filter params:

- `page`
- `per_page`
- `tester_id`
- `status`
- `search`

## 5.4 Maintenance Schedules

- `GET /api/v1/maintenance-schedules`
- `POST /api/v1/maintenance-schedules`
- `GET /api/v1/maintenance-schedules/{maintenanceSchedule}`
- `PATCH /api/v1/maintenance-schedules/{maintenanceSchedule}`
- `DELETE /api/v1/maintenance-schedules/{maintenanceSchedule}`
- `POST /api/v1/maintenance-schedules/{maintenanceSchedule}/complete`

Filter params:

- `page`
- `per_page`
- `tester_id`
- `status`: `scheduled|completed|overdue`
- `start_date`
- `end_date`

Complete body:

```json
{
    "completed_date": "2026-04-02",
    "performed_by": "Tech User",
    "notes": "Routine preventive maintenance complete"
}
```

## 5.5 Calibration Schedules

- `GET /api/v1/calibration-schedules`
- `POST /api/v1/calibration-schedules`
- `GET /api/v1/calibration-schedules/{calibrationSchedule}`
- `PATCH /api/v1/calibration-schedules/{calibrationSchedule}`
- `DELETE /api/v1/calibration-schedules/{calibrationSchedule}`
- `POST /api/v1/calibration-schedules/{calibrationSchedule}/complete`

Filter params:

- `page`
- `per_page`
- `tester_id`
- `status`: `scheduled|completed|overdue`
- `start_date`
- `end_date`

## 5.6 Event Logs

- `GET /api/v1/event-logs`
- `POST /api/v1/event-logs`
- `GET /api/v1/event-logs/{eventLog}`

Filter params:

- `page`
- `per_page`
- `tester_id`
- `type`: `maintenance|calibration|issue|repair|other`
- `start_date`
- `end_date`

## 5.7 Spare Parts

- `GET /api/v1/spare-parts`
- `POST /api/v1/spare-parts`
- `GET /api/v1/spare-parts/{sparePart}`
- `PATCH /api/v1/spare-parts/{sparePart}`
- `DELETE /api/v1/spare-parts/{sparePart}`

Filter params:

- `page`
- `per_page`
- `search`
- `stock_status`: `low|normal|full`

## 6. Permission Matrix

Roles:

- `Admin`
- `Manager` (alias compatible with `Maintenance Technician`)
- `Technician` (alias compatible with `Calibration Specialist`)
- `Guest`

| Resource / Action            | Admin | Manager | Technician | Guest |
| ---------------------------- | ----- | ------- | ---------- | ----- |
| Auth register/login/logout   | Yes   | Yes     | Yes        | Yes   |
| Customers list/show          | Yes   | Yes     | No         | No    |
| Customers create/update      | Yes   | Yes     | No         | No    |
| Customers delete             | Yes   | No      | No         | No    |
| Testers list/show            | Yes   | Yes     | Yes        | Yes   |
| Testers create/update/status | Yes   | Yes     | No         | No    |
| Testers delete               | Yes   | No      | No         | No    |
| Fixtures list/show           | Yes   | Yes     | Yes        | Yes   |
| Fixtures create/update       | Yes   | Yes     | No         | No    |
| Fixtures delete              | Yes   | No      | No         | No    |
| Maintenance list/show        | Yes   | Yes     | Yes        | No    |
| Maintenance create           | Yes   | Yes     | No         | No    |
| Maintenance update/complete  | Yes   | Yes     | Yes        | No    |
| Calibration list/show        | Yes   | Yes     | Yes        | No    |
| Calibration create           | Yes   | Yes     | No         | No    |
| Calibration update/complete  | Yes   | Yes     | Yes        | No    |
| Event logs list/show/create  | Yes   | Yes     | Yes        | No    |
| Spare parts list/show        | Yes   | Yes     | Yes        | Yes   |
| Spare parts create/update    | Yes   | Yes     | No         | No    |
| Spare parts delete           | Yes   | No      | No         | No    |

## 7. Integration Notes

- Use `Authorization: Bearer <token>` for all protected endpoints.
- Login and register endpoints are rate limited (`6 requests / minute`).
- List endpoints return `data.items` + `data.pagination`.
- Customer delete returns `409` when there are linked testers.
