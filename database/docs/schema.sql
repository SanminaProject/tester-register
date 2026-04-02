-- Documents (PDF, Excel) can be downloaded from UI with laravel logic that takes info from these tables and convers it to a document
-- so no need to store documents in the database. If we want to store documents, we can add a table for that and link it to testers or maintenance/calibration schedules.

CREATE TABLE tester_customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(100) NOT NULL
);

-- holds information about locations that can be used for both testers and fixtures
CREATE TABLE tester_and_fixture_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    location_name VARCHAR(100) NOT NULL,
    description TEXT,
    address VARCHAR(255) -- should we delete this column?
);

-- holds information about users responsible for tester maintenance/calibration or HR/administration work
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL, 
    phone_number VARCHAR(50) NOT NULL,
    responsibilities TEXT, -- description of the tasks they are responsible for
    qualifications_certifications TEXT, -- any relevant qualifications or certifications they hold
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- holds statuses for testers and fixtures
-- includes active, inactive, and maintenance
CREATE TABLE asset_statuses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- holds all essential information about testers
CREATE TABLE testers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL, -- name of the tester
    description TEXT, -- detailed description of the tester
    id_number_by_customer VARCHAR(50), -- the ID number given to the tester by the customer
    operating_system VARCHAR(50), -- operating system used by the tester pc or test system
    type VARCHAR(50), -- type of tester
    product_family VARCHAR(100), -- product family associated with the tester
    manufacturer VARCHAR(100), -- manufacturer of the tester
    implementation_date DATE, -- date when the tester was implemented
    additional_info TEXT, -- any additional information about the tester

    -- references
    location_id INT, -- physicallocation of the tester
    owner_id INT, -- owner of the tester, which is usually the customer (NOKIA, HALTIAN etc.)
    status INT, -- status of the tester (active, inactive or maintenance) 

    -- the information on who is responsible for each tester will be stored in the user_tester_assignments table, which links users to testers

    FOREIGN KEY (location_id) REFERENCES tester_and_fixture_locations(id),
    FOREIGN KEY (owner_id) REFERENCES tester_customers(id),
    FOREIGN KEY (status) REFERENCES asset_statuses(id)
);

CREATE TABLE tester_assets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_no VARCHAR(100) NOT NULL,
    tester_id INT NOT NULL,
    
    FOREIGN KEY (tester_id) REFERENCES testers(id)
);

-- holds all essential information about suppliers of spare parts for testers
CREATE TABLE tester_spare_part_suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- holds all essential information about spare parts associated with testers
CREATE TABLE tester_spare_parts (
    part_id INT PRIMARY KEY AUTO_INCREMENT,
    part_name VARCHAR(255) NOT NULL,
    manufacturer_part_number VARCHAR(255),
    quantity_in_stock INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL, -- alarm level of when to reorder (check can be done in laravel when quantity_in_stock goes below this level)
    last_order_date DATE,
    unit_price DECIMAL(10,2),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- index for faster lookups of spare parts by tester
    INDEX idx_tester_spare_parts_tester (tester_id),

    -- references
    tester_id INT NOT NULL,
    supplier_id INT, -- supplier of the spare part

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id), -- testers, users, alarm levels and roles are linked and can be called together with queries 
    FOREIGN KEY (supplier_id) REFERENCES tester_spare_part_suppliers(supplier_id)
);

-- holds all essential information about fixtures associated with testers
CREATE TABLE fixtures (
    fixture_id INT PRIMARY KEY AUTO_INCREMENT,
    fixture_name VARCHAR(100) NOT NULL, -- name of the fixture
    fixture_description TEXT, -- detailed description of the fixture
    fixture_manufacturer VARCHAR(100), -- manufacturer of the fixture
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- index for faster lookups of fixtures by tester
    INDEX idx_fixtures_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- reference to the tester this fixture is associated with
    location_id INT, -- physical location of the fixture
    fixture_status INT, -- current status of the fixture (active, inactive or maintenance)

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (location_id) REFERENCES tester_and_fixture_locations(location_id),
    FOREIGN KEY (fixture_status) REFERENCES asset_statuses(status_id)
);

-- holds all information on data changes made to testers, fixtures, and spare parts
-- like adding a tester, changing tester information in the tester table, activating a spare part etc. 
CREATE TABLE data_change_logs (
    change_id INT PRIMARY KEY AUTO_INCREMENT,
    change_date DATETIME NOT NULL, -- when the change was made
    explanation TEXT NOT NULL, -- explanation of the change

    -- references
    tester_id INT, -- tester ID if the change is related to a tester
    fixture_id INT, -- fixture ID if the change is related to a fixture
    spare_part_id INT, -- spare part ID if the change is related to a spare part
    user_id INT, -- who made the change

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (fixture_id) REFERENCES fixtures(fixture_id),
    FOREIGN KEY (spare_part_id) REFERENCES tester_spare_parts(part_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- holds definitions of different types of events that can be logged into tester_event_logs
-- includes ENUM('issue', 'maintenance', 'calibration', 'software_update', 'hardware_change')
CREATE TABLE event_types (
    event_type_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- holds definitions of different statuses for issues logged into tester_event_logs
-- includes ENUM('open', 'closed')
CREATE TABLE issue_statuses (
    issue_status_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE procedure_interval_units (
    interval_unit_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE -- unit of time (Days, Weeks, Months or Years)
);

-- holds definitions of maintenance procedures for testers
CREATE TABLE tester_maintenance_procedures (
    maintenance_id INT PRIMARY KEY AUTO_INCREMENT,
    maintenance_type VARCHAR(100) NOT NULL, -- e.g., Preventive Maintenance, Routine Check
    maintenance_interval_value INT NOT NULL, -- numerical value of the maintenance interval
    maintenance_description TEXT, -- detailed description of the maintenance activities

    -- references
    maintenance_interval_unit INT NOT NULL, -- unit of time for the maintenance interval (Days, Weeks, Months or Years)

    FOREIGN KEY (maintenance_interval_unit) REFERENCES procedure_interval_units(interval_unit_id)
);

-- holds definitions of calibration procedures for testers
CREATE TABLE tester_calibration_procedures (
    calibration_id INT PRIMARY KEY AUTO_INCREMENT,
    calibration_type VARCHAR(100) NOT NULL, -- e.g., Standard Calibration, Full Calibration
    calibration_interval_value INT NOT NULL, -- numerical value of the calibration interval
    calibration_description TEXT, -- detailed description of the calibration procedures

    -- references
    calibration_interval_unit INT NOT NULL, -- unit of time for the calibration interval (Days, Weeks, Months or Years)

    FOREIGN KEY (calibration_interval_unit) REFERENCES procedure_interval_units(interval_unit_id)
);

-- holds definitions of different statuses for maintenance and calibration schedules
-- includes ENUM('Scheduled', 'Overdue')
CREATE TABLE schedule_statuses (
    schedule_status_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- links testers to maintenance procedures
CREATE TABLE tester_maintenance_schedules (
    maintenance_schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    schedule_created_date DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP), -- when the maintenance schedule was created
    last_maintenance_date DATETIME, -- date when maintenance was last performed
    next_maintenance_due DATETIME, -- calculated next maintenance date (USING EVENT!)

    -- index for faster lookups of maintenance schedules by tester
    INDEX idx_tester_maintenance_schedules_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- the tester that needs maintenance
    maintenance_id INT NOT NULL, -- the maintenance procedure used
    maintenance_status INT, -- status of the maintenance schedule (Scheduled, Overdue)
    last_maintenance_by_user_id INT, -- who performed the last maintenance
    next_maintenance_by_user_id INT, -- who is scheduled to perform the next maintenance

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (maintenance_id) REFERENCES tester_maintenance_procedures(maintenance_id),
    FOREIGN KEY (maintenance_status) REFERENCES schedule_statuses(schedule_status_id),
    FOREIGN KEY (last_maintenance_by_user_id) REFERENCES users(user_id),
    FOREIGN KEY (next_maintenance_by_user_id) REFERENCES users(user_id)
);

-- links testers to calibration procedures
CREATE TABLE tester_calibration_schedules (
    calibration_schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    schedule_created_date DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP), -- when this schedule was created
    last_calibration_date DATETIME, -- date when calibration was last performed
    next_calibration_due DATETIME, -- calculated next calibration date (USING EVENT!)

    -- index for faster lookups of calibration schedules by tester
    INDEX idx_tester_calibration_schedules_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- reference to the tester
    calibration_id INT NOT NULL, -- reference to the calibration procedure
    calibration_status INT, -- status of the calibration schedule (Scheduled, Overdue)
    last_calibration_by_user_id INT, -- who performed the last calibration
    next_calibration_by_user_id INT, -- who is scheduled to perform the next calibration

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (calibration_id) REFERENCES tester_calibration_procedures(calibration_id),
    FOREIGN KEY (calibration_status) REFERENCES schedule_statuses(schedule_status_id),
    FOREIGN KEY (last_calibration_by_user_id) REFERENCES users(user_id),
    FOREIGN KEY (next_calibration_by_user_id) REFERENCES users(user_id)
);

-- holds all information about physical events related to testers
CREATE TABLE tester_event_logs (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    event_date DATETIME NOT NULL, -- when the event occurred
    event_description TEXT NOT NULL, -- detailed description of the event

    -- only for issue/fault/problem events
    resolved_date DATETIME, -- date when the issue was resolved
    resolution_description TEXT, -- solution to the issue

    -- index for faster lookups of events by tester
    INDEX idx_tester_event_logs_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- which tester the event is related to
    event_type INT NOT NULL, -- reference to the type of the event (issue, maintenance, calibration, software update or hardware change)
    created_by_user_id INT NOT NULL, -- who created the event log entry (could be the person who reported the issue or performed the maintenance/calibration)
    resolved_by_user_id INT, -- who resolved the issue
    issue_status INT, -- status of the issue (open or closed)
    maintenance_schedule_id INT, -- reference to the maintenance schedule used
    calibration_schedule_id INT, -- reference to the calibration schedule used

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (event_type) REFERENCES event_types(event_type_id),
    FOREIGN KEY (created_by_user_id) REFERENCES users(user_id),
    FOREIGN KEY (resolved_by_user_id) REFERENCES users(user_id),
    FOREIGN KEY (issue_status) REFERENCES issue_statuses(issue_status_id),
    FOREIGN KEY (maintenance_schedule_id) REFERENCES tester_maintenance_schedules(maintenance_schedule_id),
    FOREIGN KEY (calibration_schedule_id) REFERENCES tester_calibration_schedules(calibration_schedule_id)
);

-- links users to testers they are responsible for
CREATE TABLE user_tester_assignments (
    user_id INT NOT NULL,
    tester_id INT NOT NULL,

    PRIMARY KEY (user_id, tester_id),

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (tester_id) REFERENCES testers(tester_id) ON DELETE CASCADE
);

-- BELOW TABLES BASED ON SPATIE LARAVEL PERMISSION LIBRARY
-- holds info on roles that users can have
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL, -- Admin, Maintenance Technician, or Test Operator
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY name_guard_unique (name, guard_name)
);

-- links a user to roles
CREATE TABLE model_has_roles (
    role_id BIGINT NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id INT NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
