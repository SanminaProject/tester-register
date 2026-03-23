# API Interface Design Specification Document

**Project**: Tester Register  
**Author**: Beginner Student  
**Date**: 2026-03-23  
**Version**: 1.0

---

## 📌 Overall API Specification

### Basic Information

- **API Base URL**: `http://localhost:8000/api`
- **API Version**: `v1`
- **Content Type**: `application/json`
- **Authentication**: Bearer Token (in Authorization Header)

### Request Header Example

```
GET /api/v1/testers HTTP/1.1
Host: localhost:8000
Authorization: Bearer {access_token}
Content-Type: application/json
Accept: application/json
```

---

## 🔐 Authentication API Endpoints

### 1️⃣ User Login

**Endpoint Information**

```
Method: POST
URL: /api/v1/auth/login
Description: Users log in with email and password to obtain access token
```

**Request Parameters** (Request Body - JSON)

```json
{
    "email": "user@example.com", // Required: User email
    "password": "password123" // Required: User password
}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "Bearer",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "roles": ["admin"]
        }
    },
    "code": 200
}
```

**Error Response** (HTTP 401)

```json
{
    "success": false,
    "message": "Invalid email or password",
    "error": "UnauthorizedException",
    "code": 401
}
```

---

### 2️⃣ User Logout

**Endpoint Information**

```
Method: POST
URL: /api/v1/auth/logout
Description: Logout the current user session
```

**Request Parameters**: None

**Request Header**

```
Authorization: Bearer {access_token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Logout successful",
    "code": 200
}
```

---

## 👥 Customers API Endpoints

### 1️⃣ Get Customer List

**Endpoint Information**

```
Method: GET
URL: /api/v1/customers
Description: Retrieve paginated list of all customers
Permission: view_customers
```

**Request Parameters** (Query Parameters)

```
page          [integer] Optional  Page number, default 1
per_page      [integer] Optional  Items per page, default 15
search        [string]  Optional  Search customer names
```

**Request Example**

```
GET /api/v1/customers?page=1&per_page=20&search=apple
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Customer list retrieved successfully",
    "data": {
        "items": [
            {
                "id": 1,
                "company_name": "Apple Inc",
                "address": "Cupertino, CA",
                "contact_person": "John Smith",
                "phone": "+1-408-996-1010",
                "email": "contact@apple.com",
                "created_at": "2026-03-01T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 5,
            "total_pages": 1
        }
    },
    "code": 200
}
```

---

### 2️⃣ Create Customer

**Endpoint Information**

```
Method: POST
URL: /api/v1/customers
Description: Create a new customer
Permission: create_customers
```

**Request Parameters** (Request Body - JSON)

```json
{
    "company_name": "Apple Inc", // Required: Company name
    "address": "Cupertino, CA", // Required: Address
    "contact_person": "John Smith", // Required: Contact person
    "phone": "+1-408-996-1010", // Required: Phone number
    "email": "contact@apple.com" // Required: Email
}
```

**Successful Response** (HTTP 201 Created)

```json
{
    "success": true,
    "message": "Customer created successfully",
    "data": {
        "id": 1,
        "company_name": "Apple Inc",
        "address": "Cupertino, CA",
        "contact_person": "John Smith",
        "phone": "+1-408-996-1010",
        "email": "contact@apple.com",
        "created_at": "2026-03-23T15:30:00Z"
    },
    "code": 201
}
```

**Validation Error** (HTTP 422 Unprocessable Entity)

```json
{
    "success": false,
    "message": "Validation failed",
    "error": "ValidationException",
    "data": {
        "company_name": ["Company name is required"],
        "email": ["Email format is invalid"]
    },
    "code": 422
}
```

---

### 3️⃣ Get Single Customer Details

**Endpoint Information**

```
Method: GET
URL: /api/v1/customers/{id}
Description: Get detailed information of a specific customer
Permission: view_customers
```

**Request Parameters**: None (ID in URL)

**Request Example**

```
GET /api/v1/customers/1
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Customer details retrieved successfully",
    "data": {
        "id": 1,
        "company_name": "Apple Inc",
        "address": "Cupertino, CA",
        "contact_person": "John Smith",
        "phone": "+1-408-996-1010",
        "email": "contact@apple.com",
        "testers_count": 5,
        "testers": [
            { "id": 1, "model": "iPhone Test 3000" },
            { "id": 2, "model": "iPad Test Pro" }
        ],
        "created_at": "2026-03-01T10:00:00Z"
    },
    "code": 200
}
```

**Not Found** (HTTP 404)

```json
{
    "success": false,
    "message": "Customer not found",
    "error": "NotFoundException",
    "code": 404
}
```

---

### 4️⃣ Update Customer Information

**Endpoint Information**

```
Method: PUT
URL: /api/v1/customers/{id}
Description: Update customer information
Permission: edit_customers
```

**Request Parameters** (Request Body - JSON - Only send fields to be updated)

```json
{
    "company_name": "Apple Inc Updated",
    "phone": "+1-408-996-2000"
}
```

**Request Example**

```
PUT /api/v1/customers/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "company_name": "Apple Inc Updated"
}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Customer information updated successfully",
    "data": {
        "id": 1,
        "company_name": "Apple Inc Updated",
        "address": "Cupertino, CA",
        "contact_person": "John Smith",
        "phone": "+1-408-996-1010",
        "email": "contact@apple.com",
        "updated_at": "2026-03-23T16:45:00Z"
    },
    "code": 200
}
```

---

### 5️⃣ Delete Customer

**Endpoint Information**

```
Method: DELETE
URL: /api/v1/customers/{id}
Description: Delete a customer (if no related assets)
Permission: delete_customers
```

**Request Example**

```
DELETE /api/v1/customers/1
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Customer deleted successfully",
    "code": 200
}
```

**Conflict** (HTTP 409 - Customer has associated devices and cannot be deleted)

```json
{
    "success": false,
    "message": "Cannot delete: this customer has 5 associated test devices",
    "error": "ConflictException",
    "code": 409
}
```

---

## 🔧 Test Devices (Testers) API Endpoints

### 1️⃣ Get Device List

**Endpoint Information**

```
Method: GET
URL: /api/v1/testers
Description: Retrieve paginated list of all test devices with filtering support
Permission: view_testers
```

**Request Parameters** (Query Parameters)

```
page          [integer] Optional  Page number, default 1
per_page      [integer] Optional  Items per page, default 15
status        [string]  Optional  Filter by status (active|inactive|maintenance)
customer_id   [integer] Optional  Filter by customer ID
search        [string]  Optional  Search by model or serial number
sort_by       [string]  Optional  Sort by field (model|purchase_date)
```

**Request Example**

```
GET /api/v1/testers?page=1&status=active&customer_id=5&search=iPhone
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Device list retrieved successfully",
    "data": {
        "items": [
            {
                "id": 1,
                "model": "iPhone Test 3000",
                "serial_number": "SN12345",
                "customer_id": 5,
                "customer_name": "Apple Inc",
                "status": "active",
                "purchase_date": "2025-01-15",
                "location": "Building A, Room 201",
                "created_at": "2026-03-20T10:30:00Z",
                "updated_at": "2026-03-23T14:22:00Z"
            },
            {
                "id": 2,
                "model": "Samsung Test Pro",
                "serial_number": "SN12346",
                "customer_id": 6,
                "customer_name": "Samsung Inc",
                "status": "maintenance",
                "purchase_date": "2025-02-10",
                "location": "Building B, Room 105",
                "created_at": "2026-03-21T11:45:00Z",
                "updated_at": "2026-03-23T09:15:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 42,
            "total_pages": 3,
            "has_next": true
        }
    },
    "code": 200
}
```

---

### 2️⃣ Create Device

**Endpoint Information**

```
Method: POST
URL: /api/v1/testers
Description: Create a new test device
Permission: create_testers
```

**Request Parameters** (Request Body - JSON)

```json
{
    "model": "iPhone Test 3000", // Required: Device model
    "serial_number": "SN12345", // Required: Serial number (unique)
    "customer_id": 5, // Required: Associated customer ID
    "purchase_date": "2025-01-15", // Required: Purchase date (YYYY-MM-DD)
    "status": "active", // Optional: Status (active|inactive|maintenance)
    "location": "Building A, Room 201" // Optional: Location information
}
```

**Successful Response** (HTTP 201 Created)

```json
{
    "success": true,
    "message": "Device created successfully",
    "data": {
        "id": 1,
        "model": "iPhone Test 3000",
        "serial_number": "SN12345",
        "customer_id": 5,
        "customer_name": "Apple Inc",
        "status": "active",
        "purchase_date": "2025-01-15",
        "location": "Building A, Room 201",
        "created_at": "2026-03-23T15:30:00Z"
    },
    "code": 201
}
```

**Validation Error** (HTTP 422)

```json
{
    "success": false,
    "message": "Validation failed",
    "error": "ValidationException",
    "data": {
        "serial_number": ["Serial number already exists"],
        "customer_id": ["Associated customer does not exist"]
    },
    "code": 422
}
```

---

### 3️⃣ Get Single Device Details

**Endpoint Information**

```
Method: GET
URL: /api/v1/testers/{id}
Description: Get detailed information of a device, including associated fixtures and recent events
Permission: view_testers
```

**Request Example**

```
GET /api/v1/testers/1
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Device details retrieved successfully",
    "data": {
        "id": 1,
        "model": "iPhone Test 3000",
        "serial_number": "SN12345",
        "customer_id": 5,
        "customer_name": "Apple Inc",
        "status": "active",
        "purchase_date": "2025-01-15",
        "location": "Building A, Room 201",
        "created_at": "2026-03-20T10:30:00Z",
        "updated_at": "2026-03-23T14:22:00Z",
        "fixtures": [
            {
                "id": 10,
                "name": "iPhone Connector Fixture",
                "serial_number": "FIX123",
                "status": "active"
            }
        ],
        "recent_events": [
            {
                "id": 100,
                "type": "maintenance",
                "description": "Regular maintenance",
                "date": "2026-03-20T10:00:00Z"
            }
        ],
        "maintenance_schedules": [
            {
                "id": 1,
                "scheduled_date": "2026-04-20",
                "status": "pending"
            }
        ]
    },
    "code": 200
}
```

---

### 4️⃣ Update Device Information

**Endpoint Information**

```
Method: PUT
URL: /api/v1/testers/{id}
Description: Update device information
Permission: edit_testers
```

**Request Parameters** (Request Body - JSON)

```json
{
    "location": "Building B, Room 105",
    "purchase_date": "2025-02-01"
}
```

**Request Example**

```
PUT /api/v1/testers/1
Authorization: Bearer {token}
Content-Type: application/json
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Device information updated successfully",
    "data": {
        "id": 1,
        "model": "iPhone Test 3000",
        "serial_number": "SN12345",
        "customer_id": 5,
        "customer_name": "Apple Inc",
        "status": "active",
        "purchase_date": "2025-02-01",
        "location": "Building B, Room 105",
        "updated_at": "2026-03-23T16:45:00Z"
    },
    "code": 200
}
```

---

### 5️⃣ Update Device Status

**Endpoint Information**

```
Method: PATCH
URL: /api/v1/testers/{id}/status
Description: Quickly update device status
Permission: edit_testers
```

**Request Parameters** (Request Body - JSON)

```json
{
    "status": "maintenance" // active|inactive|maintenance
}
```

**Request Example**

```
PATCH /api/v1/testers/1/status
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Device status updated successfully",
    "data": {
        "id": 1,
        "status": "maintenance",
        "updated_at": "2026-03-23T17:00:00Z"
    },
    "code": 200
}
```

---

### 6️⃣ Delete Device

**Endpoint Information**

```
Method: DELETE
URL: /api/v1/testers/{id}
Description: Delete a device
Permission: delete_testers
```

**Request Example**

```
DELETE /api/v1/testers/1
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Device deleted successfully",
    "code": 200
}
```

---

## 🎯 Test Fixtures API Endpoints

### 1️⃣ Get Fixture List

**Endpoint Information**

```
Method: GET
URL: /api/v1/fixtures
Description: Retrieve paginated list of all fixtures
Permission: view_fixtures
```

**Request Parameters** (Query Parameters)

```
page          [integer] Optional  Page number, default 1
per_page      [integer] Optional  Items per page, default 15
status        [string]  Optional  Filter by status
tester_id     [integer] Optional  Filter by associated device
search        [string]  Optional  Search by name or serial number
```

**Request Example**

```
GET /api/v1/fixtures?tester_id=1&status=active
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Fixture list retrieved successfully",
    "data": {
        "items": [
            {
                "id": 10,
                "name": "iPhone Connector Fixture",
                "serial_number": "FIX123",
                "tester_id": 1,
                "tester_model": "iPhone Test 3000",
                "status": "active",
                "purchase_date": "2025-01-20",
                "created_at": "2026-03-20T10:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 15,
            "total_pages": 1
        }
    },
    "code": 200
}
```

---

### 2️⃣ Create Fixture

**Endpoint Information**

```
Method: POST
URL: /api/v1/fixtures
Description: Create a new fixture
Permission: create_fixtures
```

**Request Parameters** (Request Body - JSON)

```json
{
    "name": "iPhone Connector Fixture", // Required: Fixture name
    "serial_number": "FIX123", // Required: Serial number (unique)
    "tester_id": 1, // Required: Associated device ID
    "purchase_date": "2025-01-20", // Required: Purchase date
    "status": "active" // Optional: Status
}
```

**Successful Response** (HTTP 201)

```json
{
    "success": true,
    "message": "Fixture created successfully",
    "data": {
        "id": 10,
        "name": "iPhone Connector Fixture",
        "serial_number": "FIX123",
        "tester_id": 1,
        "status": "active",
        "purchase_date": "2025-01-20",
        "created_at": "2026-03-23T15:30:00Z"
    },
    "code": 201
}
```

---

(Additional interface definitions such as: Get fixture details, Update fixture, Delete fixture follow the same patterns as above)

---

## 📅 Maintenance Schedules API Endpoints

### 1️⃣ Get Maintenance Schedule List

**Endpoint Information**

```
Method: GET
URL: /api/v1/maintenance-schedules
Description: Retrieve all maintenance schedules with filtering by device and date
Permission: view_maintenance_schedules
```

**Request Parameters** (Query Parameters)

```
page              [integer] Optional  Page number
per_page          [integer] Optional  Items per page
tester_id         [integer] Optional  Filter by device
status            [string]  Optional  Status (pending|completed)
start_date        [string]  Optional  Start date (YYYY-MM-DD)
end_date          [string]  Optional  End date (YYYY-MM-DD)
```

**Request Example**

```
GET /api/v1/maintenance-schedules?tester_id=1&status=pending
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Maintenance schedule list retrieved successfully",
    "data": {
        "items": [
            {
                "id": 1,
                "tester_id": 1,
                "tester_model": "iPhone Test 3000",
                "scheduled_date": "2026-04-20",
                "status": "pending",
                "procedure": "Regular maintenance",
                "notes": "Routine inspection",
                "created_at": "2026-03-23T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 8,
            "total_pages": 1
        }
    },
    "code": 200
}
```

---

### 2️⃣ Create Maintenance Schedule

**Endpoint Information**

```
Method: POST
URL: /api/v1/maintenance-schedules
Description: Create a new maintenance task for a device
Permission: create_maintenance_schedules
```

**Request Parameters** (Request Body - JSON)

```json
{
    "tester_id": 1, // Required: Device ID
    "scheduled_date": "2026-04-20", // Required: Scheduled maintenance date
    "procedure": "Regular maintenance", // Required: Maintenance procedure/type
    "notes": "Routine inspection" // Optional: Notes
}
```

**Successful Response** (HTTP 201)

```json
{
    "success": true,
    "message": "Maintenance schedule created successfully",
    "data": {
        "id": 1,
        "tester_id": 1,
        "scheduled_date": "2026-04-20",
        "status": "pending",
        "procedure": "Regular maintenance",
        "notes": "Routine inspection",
        "created_at": "2026-03-23T15:30:00Z"
    },
    "code": 201
}
```

---

### 3️⃣ Complete Maintenance

**Endpoint Information**

```
Method: POST
URL: /api/v1/maintenance-schedules/{id}/complete
Description: Mark a maintenance task as completed
Permission: edit_maintenance_schedules
```

**Request Parameters** (Request Body - JSON)

```json
{
    "completed_date": "2026-04-20", // Required: Completion date
    "performed_by": "John Smith", // Required: Performed by
    "notes": "Regular maintenance completed" // Optional: Notes
}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Maintenance task marked as completed",
    "data": {
        "id": 1,
        "status": "completed",
        "completed_date": "2026-04-20",
        "performed_by": "John Smith",
        "updated_at": "2026-03-23T16:45:00Z"
    },
    "code": 200
}
```

---

## 📝 Event Logs API Endpoints

### 1️⃣ Get Event Log List

**Endpoint Information**

```
Method: GET
URL: /api/v1/event-logs
Description: Retrieve all event logs with filtering support
Permission: view_event_logs
```

**Request Parameters** (Query Parameters)

```
page         [integer] Optional  Page number
per_page     [integer] Optional  Items per page
tester_id    [integer] Optional  Filter by device
type         [string]  Optional  Event type (maintenance|calibration|issue|repair)
start_date   [string]  Optional  Start date
end_date     [string]  Optional  End date
```

**Request Example**

```
GET /api/v1/event-logs?tester_id=1
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Event log list retrieved successfully",
    "data": {
        "items": [
            {
                "id": 100,
                "tester_id": 1,
                "type": "maintenance",
                "description": "Regular maintenance",
                "event_date": "2026-03-20T10:00:00Z",
                "recorded_by": "admin",
                "created_at": "2026-03-20T10:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 50,
            "total_pages": 3
        }
    },
    "code": 200
}
```

---

### 2️⃣ Create Event Log

**Endpoint Information**

```
Method: POST
URL: /api/v1/event-logs
Description: Manually record an event
Permission: create_event_logs
```

**Request Parameters** (Request Body - JSON)

```json
{
    "tester_id": 1, // Required: Device ID
    "type": "maintenance", // Required: Event type
    "description": "Regular maintenance completed", // Required: Event description
    "event_date": "2026-03-20" // Required: Event date
}
```

**Successful Response** (HTTP 201)

```json
{
    "success": true,
    "message": "Event log created successfully",
    "data": {
        "id": 100,
        "tester_id": 1,
        "type": "maintenance",
        "description": "Regular maintenance completed",
        "event_date": "2026-03-20T00:00:00Z",
        "created_at": "2026-03-23T15:30:00Z"
    },
    "code": 201
}
```

---

## 🔩 Spare Parts API Endpoints

### 1️⃣ Get Spare Parts List

**Endpoint Information**

```
Method: GET
URL: /api/v1/spare-parts
Description: Retrieve all spare parts in inventory
Permission: view_spare_parts
```

**Request Parameters** (Query Parameters)

```
page              [integer] Optional  Page number
per_page          [integer] Optional  Items per page
search            [string]  Optional  Search keywords
stock_status      [string]  Optional  Stock status (low|normal|full)
```

**Request Example**

```
GET /api/v1/spare-parts?page=1&stock_status=low
Authorization: Bearer {token}
```

**Successful Response** (HTTP 200)

```json
{
    "success": true,
    "message": "Spare parts list retrieved successfully",
    "data": {
        "items": [
            {
                "id": 1,
                "name": "iPhone Connector",
                "part_number": "PART123",
                "quantity_in_stock": 5,
                "unit_cost": 45.99,
                "supplier": "Tech Supplier Inc",
                "stock_status": "normal",
                "created_at": "2026-03-15T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 30,
            "total_pages": 2
        }
    },
    "code": 200
}
```

---

### 2️⃣ Create Spare Part

**Endpoint Information**

```
Method: POST
URL: /api/v1/spare-parts
Description: Add a new spare part to inventory
Permission: create_spare_parts
```

**Request Parameters** (Request Body - JSON)

```json
{
    "name": "iPhone Connector", // Required: Spare part name
    "part_number": "PART123", // Required: Part number
    "quantity_in_stock": 10, // Required: Stock quantity
    "unit_cost": 45.99, // Required: Unit cost
    "supplier": "Tech Supplier Inc" // Optional: Supplier
}
```

**Successful Response** (HTTP 201)

```json
{
    "success": true,
    "message": "Spare part created successfully",
    "data": {
        "id": 1,
        "name": "iPhone Connector",
        "part_number": "PART123",
        "quantity_in_stock": 10,
        "unit_cost": 45.99,
        "supplier": "Tech Supplier Inc",
        "created_at": "2026-03-23T15:30:00Z"
    },
    "code": 201
}
```

---

## 📝 Generic Error Response Format

All error responses follow the format below:

### 400 Bad Request

```json
{
    "success": false,
    "message": "Invalid request parameters",
    "error": "BadRequestException",
    "code": 400
}
```

### 401 Unauthorized

```json
{
    "success": false,
    "message": "Unauthorized, please log in first",
    "error": "UnauthorizedException",
    "code": 401
}
```

### 403 Forbidden

```json
{
    "success": false,
    "message": "Access forbidden, insufficient permissions",
    "error": "ForbiddenException",
    "code": 403
}
```

### 404 Not Found

```json
{
    "success": false,
    "message": "Resource not found",
    "error": "NotFoundException",
    "code": 404
}
```

### 422 Unprocessable Entity

```json
{
    "success": false,
    "message": "Validation failed",
    "error": "ValidationException",
    "data": {
        "field_name": ["Error message"]
    },
    "code": 422
}
```

### 500 Internal Server Error

```json
{
    "success": false,
    "message": "Server error",
    "error": "InternalServerError",
    "code": 500
}
```

---

## 📊 API Design Summary Table

| Entity              | List | Create | Details  | Update   | Delete     | Other              |
| ------------------- | ---- | ------ | -------- | -------- | ---------- | ------------------ |
| **Customers**       | GET  | POST   | GET/{id} | PUT/{id} | DELETE/{id} | -                  |
| **Testers**         | GET  | POST   | GET/{id} | PUT/{id} | DELETE/{id} | PATCH/{id}/status  |
| **Fixtures**        | GET  | POST   | GET/{id} | PUT/{id} | DELETE/{id} | -                  |
| **Maintenance**     | GET  | POST   | GET/{id} | PUT/{id} | DELETE/{id} | POST/{id}/complete |
| **Calibration**     | GET  | POST   | GET/{id} | PUT/{id} | DELETE/{id} | POST/{id}/complete |
| **Event Logs**      | GET  | POST   | GET/{id} | -        | -          | -                  |
| **Spare Parts**     | GET  | POST   | GET/{id} | PUT/{id} | DELETE/{id} | -                  |

---

Complete API design specification has been defined! Next steps: implement these interfaces.