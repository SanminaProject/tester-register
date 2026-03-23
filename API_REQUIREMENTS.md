# Test Device Management System API Requirements Document

**Project**: Tester Register  
**Author**: Beginner Learner  
**Date**: 2026-03-23  
**Version**: 1.0

---

## 📋 API Overview

This API provides management interfaces for test devices, fixtures, customers, and related information.

**API Base URL**: `http://localhost:8000/api`  
**Authentication Method**: Bearer Token (obtained from user login)

---

## 🔐 Authentication Related API

### 1. User Login

- **Requirement**: Users log in with username/email and password to obtain an access token
- **Input**: Email, Password
- **Output**: Access token, User information

### 2. User Logout

- **Requirement**: Deactivate the current user's session
- **Input**: None
- **Output**: Success message

---

## 👥 Customers API

### 1. Get All Customers List

- **Requirement**: Get a paginated list of customers, including search functionality
- **Input**: Page number, Items per page, Search keywords
- **Output**: Customer list, Total count, Current page

### 2. Create Customer

- **Requirement**: Add a new customer company
- **Input**: Company name, Address, Contact person, Phone, Email
- **Output**: Created customer information, ID

### 3. Get Single Customer Details

- **Requirement**: Get detailed information based on customer ID
- **Input**: Customer ID
- **Output**: Complete customer information

### 4. Modify Customer Information

- **Requirement**: Update any customer information
- **Input**: Customer ID, Fields to be modified
- **Output**: Updated customer information

### 5. Delete Customer

- **Requirement**: Delete a customer (if no associated assets)
- **Input**: Customer ID
- **Output**: Success message or error notification

---

## 🔧 Test Devices (Testers) API

### 1. Get All Test Devices List

- **Requirement**: Get a paginated list of devices, supporting search by customer, status, and model
- **Input**: Page number, Items per page, Filter conditions (Customer ID, Status, Search keywords)
- **Output**: Device list, Total count, Current page

### 2. Create Test Device

- **Requirement**: Add a new test device
- **Input**:
    - Device model (required)
    - Serial number (required, unique)
    - Customer ID (required)
    - Purchase date (required)
    - Status (active/inactive/maintenance)
    - Location information (optional)
- **Output**: Created device information

### 3. Get Single Device Details

- **Requirement**: Get complete information based on device ID
- **Input**: Device ID
- **Output**: Complete device information, associated fixtures, recent maintenance records

### 4. Modify Device Information

- **Requirement**: Update any device field
- **Input**: Device ID, Fields to be modified
- **Output**: Updated device information

### 5. Delete Device

- **Requirement**: Delete a device record
- **Input**: Device ID
- **Output**: Success message

### 6. Modify Device Status

- **Requirement**: Quickly modify device status to active/inactive/maintenance
- **Input**: Device ID, New status
- **Output**: Updated status

---

## 🎯 Test Fixtures API

### 1. Get All Fixtures List

- **Requirement**: Get a paginated list of fixtures, supporting search by associated device and status
- **Input**: Page number, Items per page, Filter conditions
- **Output**: Fixtures list, Total count, Current page

### 2. Create Fixture

- **Requirement**: Add a new test fixture
- **Input**:
    - Fixture name (required)
    - Serial number (required, unique)
    - Associated device ID (required)
    - Purchase date (required)
    - Status (active/inactive)
- **Output**: Created fixture information

### 3. Get Single Fixture Details

- **Requirement**: Get complete information based on fixture ID
- **Input**: Fixture ID
- **Output**: Complete fixture information, associated device information, maintenance history

### 4. Modify Fixture Information

- **Requirement**: Update any fixture field
- **Input**: Fixture ID, Fields to be modified
- **Output**: Updated fixture information

### 5. Delete Fixture

- **Requirement**: Delete fixture record
- **Input**: Fixture ID
- **Output**: Success message

---

## 📅 Maintenance Schedules API

### 1. Get Maintenance Schedules List

- **Requirement**: Get all maintenance schedules, filtered by device or date
- **Input**: Page number, Device ID, Start date, End date
- **Output**: Schedule list, Total count

### 2. Create Maintenance Schedule

- **Requirement**: Create a new maintenance task for a device
- **Input**:
    - Device ID (required)
    - Planned maintenance date (required)
    - Maintenance procedure ID (required)
    - Notes
- **Output**: Created schedule information

### 3. Modify Maintenance Schedule

- **Requirement**: Update maintenance schedule
- **Input**: Schedule ID, Fields to be modified
- **Output**: Updated schedule

### 4. Complete Maintenance

- **Requirement**: Mark a maintenance task as completed
- **Input**: Schedule ID, Completion date, Executor, Notes
- **Output**: Updated schedule information and event log

### 5. Delete Maintenance Schedule

- **Requirement**: Delete an incomplete schedule
- **Input**: Schedule ID
- **Output**: Success message

---

## 📊 Calibration Schedules API

Similar to maintenance schedules, including:

- Get list
- Create schedule
- Modify schedule
- Complete calibration
- Delete schedule

---

## 📝 Event Logs API

### 1. Get Event Logs List

- **Requirement**: Get all event logs, supporting filtering by device, date, and event type
- **Input**: Page number, Device ID, Event type, Date range
- **Output**: Event list, Total count

### 2. Create Event Log

- **Requirement**: Manually record an event (failure, maintenance, calibration, etc.)
- **Input**:
    - Device ID (required)
    - Event type (required)
    - Event description
    - Occurrence date
- **Output**: Created event record

### 3. Get Event History for Specific Device

- **Requirement**: View all historical events for a specific device
- **Input**: Device ID, Date range
- **Output**: All events for that device

---

## 🔩 Spare Parts API

### 1. Get Spare Parts List

- **Requirement**: Get all spare parts in inventory, supporting search and inventory filtering
- **Input**: Page number, Search keywords, Inventory status (low stock, normal, abundant)
- **Output**: Spare parts list, Inventory quantity

### 2. Create Spare Part

- **Requirement**: Add a new spare part to inventory
- **Input**: Spare part name, Number, Inventory quantity, Cost, Notes
- **Output**: Created spare part information

### 3. Modify Spare Part Information

- **Requirement**: Update spare part information or inventory
- **Input**: Spare part ID, Fields to be modified
- **Output**: Updated spare part information

### 4. Delete Spare Part

- **Requirement**: Delete unnecessary spare parts
- **Input**: Spare part ID
- **Output**: Success message

---

## 🔐 Permissions and Access Control

### Permissions to Check:

| Role           | What they can do                                                |
| -------------- | --------------------------------------------------------------- |
| **Admin**      | Can perform all operations                                      |
| **Manager**    | Can view and modify all content, but cannot delete              |
| **Technician** | Can only view devices, create maintenance records, add event logs |
| **Guest**      | Can only view device list                                       |

Each API needs to check whether the user has permission to perform that operation.

---

## 📊 Data Validation Rules

Input data for each API needs to be validated:

```
Example: When creating a device
- Model name: Required, up to 100 characters
- Serial number: Required, unique, up to 50 characters
- Customer ID: Required, must exist
- Purchase date: Required, cannot be a future date
- Status: Required, can only be 'active', 'inactive', 'maintenance'
```

---

## ✅ API Response Format Specification

All API responses should have a unified format:

```json
// Success response
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Actual data
  },
  "code": 200
}

// Error response
{
  "success": false,
  "message": "Error reason description",
  "error": "NotFoundException",
  "code": 404
}
```

---

## 🚦 Summary of APIs to be Implemented

**Total API endpoints to be implemented for related entities: approximately 30-40**

- ✅ Authentication: 2
- ✅ Customers: 5
- ✅ Test Devices: 6
- ✅ Fixtures: 5
- ✅ Maintenance Schedules: 5
- ✅ Calibration Schedules: 5
- ✅ Event Logs: 3
- ✅ Spare Parts: 4

---

## 📅 Recommended Development Plan

### Phase One (Fundamentals)

- Design API interface specifications
- Implement basic CRUD operations for customers, devices, and fixtures

### Phase Two (Features)

- Implement maintenance and calibration schedule functionality
- Implement event logs functionality

### Phase Three (Enhancement)

- Implement spare parts management
- Add statistical and reporting features
- Improve permission checks

---

## 📞 Next Steps

After completing this requirements document, proceed to "Phase Two: Interface Design" to specifically define each API's:

- HTTP request method (GET, POST, PUT, DELETE)
- URL path
- Request parameter format
- Response data format
- Possible error responses