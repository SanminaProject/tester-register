# Tester Register API Design

## 1. Overview

## 1.1 Authentication & Authorization

All endpoints except register and login require authentication via Laravel Sanctum (`auth:sanctum`).

- Register and login endpoints are rate-limited (6 requests/minute).
- Use `Authorization: Bearer <token>` header for all protected endpoints.
- Role-based access control is enforced via middleware and policies (see Permission Matrix).
- Logout, resource management, and all sensitive actions require a valid token and appropriate role.

**Authorization Flow:**

1. Register or login to obtain a token.
2. Include the token in the `Authorization` header for all subsequent requests.
3. Access is granted or denied based on user role and endpoint policy.

- Base URL: `http://localhost:8000`
- Prefix: `/api/v1`
- Authentication: Bearer Token (Laravel Sanctum)
- Content type: `application/json`

## 2. Response Envelope

All API responses are wrapped in a standard envelope. On success, the `data` field contains the result. On error, `errors` is present for validation failures.

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

The API uses standard HTTP status codes. Notable cases:

- `409 Conflict` is returned when deleting a customer with linked testers.
- `422 Validation failed` includes a detailed `errors` object.

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

Each resource supports filtering and pagination as described below. List endpoints return `data.items` (array) and `data.pagination` (object with page info).

## 5.1 Customers

**Description:**
Customers represent organizations or individuals who own or use testers. This resource allows for listing, creating, viewing, updating, and deleting customer records. Deletion is blocked if linked testers exist.

- `GET /api/v1/customers`
- `POST /api/v1/customers`
- `GET /api/v1/customers/{customer}`
- `PATCH /api/v1/customers/{customer}`
- `DELETE /api/v1/customers/{customer}`

**Filter parameters:**

- `page`: Page number (integer)
- `per_page`: Items per page (integer)
- `search`: Search by customer name or other fields

## 5.2 Testers

**Description:**
Testers are devices or systems managed in the platform. Endpoints allow for listing, creating, viewing, updating, deleting testers, and updating their status (e.g., active, inactive, maintenance). Status changes are handled via a dedicated endpoint.

- `GET /api/v1/testers`
- `POST /api/v1/testers`
- `GET /api/v1/testers/{tester}`
- `PATCH /api/v1/testers/{tester}`
- `DELETE /api/v1/testers/{tester}`
- `PATCH /api/v1/testers/{tester}/status`  
   (Status update, body: `{ "status": "maintenance" }`)

**Filter parameters:**

- `page`: Page number
- `per_page`: Items per page
- `status`: Filter by tester status (`active`, `inactive`, `maintenance`)
- `customer_id`: Filter by customer
- `search`: Search by tester name or fields

## 5.3 Fixtures

**Description:**
Fixtures are hardware components associated with testers. These endpoints support listing, creating, viewing, updating, and deleting fixture records. Filtering by tester and status is supported.

- `GET /api/v1/fixtures`
- `POST /api/v1/fixtures`
- `GET /api/v1/fixtures/{fixture}`
- `PATCH /api/v1/fixtures/{fixture}`
- `DELETE /api/v1/fixtures/{fixture}`

**Filter parameters:**

- `page`: Page number
- `per_page`: Items per page
- `tester_id`: Filter by tester
- `status`: Filter by fixture status
- `search`: Search by fixture fields

## 5.4 Maintenance Schedules

**Description:**
Maintenance schedules define planned or completed maintenance activities for testers. Endpoints allow for full CRUD operations and marking a schedule as complete. Filtering by tester, status, and date range is supported.

- `GET /api/v1/maintenance-schedules`
- `POST /api/v1/maintenance-schedules`
- `GET /api/v1/maintenance-schedules/{maintenanceSchedule}`
- `PATCH /api/v1/maintenance-schedules/{maintenanceSchedule}`
- `DELETE /api/v1/maintenance-schedules/{maintenanceSchedule}`
  -- `POST /api/v1/maintenance-schedules/{maintenanceSchedule}/complete`  
   (Mark as complete, body: `{ "completed_date": "2026-04-02", "performed_by": "Tech User", "notes": "Routine preventive maintenance complete" }`)

**Filter parameters:**

- `page`: Page number
- `per_page`: Items per page
- `tester_id`: Filter by tester
- `status`: `scheduled`, `completed`, `overdue`
- `start_date`, `end_date`: Filter by date range

## 5.5 Calibration Schedules

**Description:**
Calibration schedules track calibration events for testers. Endpoints support CRUD operations and marking calibration as complete. Filtering by tester, status, and date range is available.

- `GET /api/v1/calibration-schedules`
- `POST /api/v1/calibration-schedules`
- `GET /api/v1/calibration-schedules/{calibrationSchedule}`
- `PATCH /api/v1/calibration-schedules/{calibrationSchedule}`
- `DELETE /api/v1/calibration-schedules/{calibrationSchedule}`
  -- `POST /api/v1/calibration-schedules/{calibrationSchedule}/complete`  
   (Mark as complete, body same as maintenance completion)

**Filter parameters:**

- `page`: Page number
- `per_page`: Items per page
- `tester_id`: Filter by tester
- `status`: `scheduled`, `completed`, `overdue`
- `start_date`, `end_date`: Filter by date range

## 5.6 Event Logs

**Description:**
Event logs record significant actions or issues related to testers, such as maintenance, calibration, repairs, or other events. Only listing, creation, and viewing of logs are supported. Filtering by tester, type, and date range is available.

- `GET /api/v1/event-logs`
- `POST /api/v1/event-logs`

    (Only supports index, store, show)

**Filter parameters:**

- `page`: Page number
- `per_page`: Items per page
- `tester_id`: Filter by tester
- `type`: `maintenance`, `calibration`, `issue`, `repair`, `other`
- `start_date`, `end_date`: Filter by date range

## 5.7 Spare Parts

**Description:**
Spare parts are inventory items used for tester maintenance or repair. Endpoints allow for listing, creating, viewing, updating, and deleting spare part records. Filtering by stock status and search is supported.

- `GET /api/v1/spare-parts`
- `POST /api/v1/spare-parts`
- `GET /api/v1/spare-parts/{sparePart}`
- `PATCH /api/v1/spare-parts/{sparePart}`
- `DELETE /api/v1/spare-parts/{sparePart}`

**Filter parameters:**

- `page`: Page number
- `per_page`: Items per page
- `search`: Search by part name or fields
- `stock_status`: `low`, `normal`, `full`

## 6. Permission Matrix

### Roles

- `Admin`
- `Manager` (alias: Maintenance Technician)
- `Technician` (alias: Calibration Specialist)
- `Guest`

### Permission Matrix

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

**Note:**

- All endpoints except register/login require authentication (`auth:sanctum`).
- Some management endpoints require specific roles (see above).
- The permission matrix is enforced via Laravel policies and middleware (e.g., `role:Admin`).

## 7. Integration Notes

See above for authentication, rate limiting, pagination, and error handling notes.

## 8. Frontend Page to API Mapping (Optional)

| Frontend Page | Related API Endpoints                                        |
| ------------- | ------------------------------------------------------------ |
| Dashboard     | /api/v1/event-logs, /api/v1/testers                          |
| Testers       | /api/v1/testers                                              |
| Fixtures      | /api/v1/fixtures                                             |
| Issues        | /api/v1/event-logs (type=issue)                              |
| Services      | /api/v1/maintenance-schedules, /api/v1/calibration-schedules |
| User Roles    | /api/v1/auth/\* (role management is internal)                |
| Profile       | /api/v1/auth/logout, user info endpoints                     |
