-- TODO: add tester asset table *if* multiple assets are needed for testers

-- Documents (PDF, Excel) can be downloaded from UI with laravel logic that takes info from these tables and convers it to a document
-- so no need to store documents in the database. If we want to store documents, we can add a table for that and link it to testers or maintenance/calibration schedules.

-- holds all essential information about testers
CREATE TABLE testers (
    tester_id INT PRIMARY KEY AUTO_INCREMENT,
    tester_name VARCHAR(100) NOT NULL, -- name of the tester
    tester_description TEXT, -- detailed description of the tester
    id_number_by_customer VARCHAR(50), -- the ID number given to the tester by the customer
    operating_system VARCHAR(50), -- operating system used by the tester pc or test system
    tester_type VARCHAR(50), -- type of tester
    tester_status ENUM('active','inactive','maintenance'), -- current status of the tester
    product_family VARCHAR(100), -- product family associated with the tester
    manufacturer VARCHAR(100), -- manufacturer of the tester
    implementation_date DATE, -- date when the tester was implemented
    owner VARCHAR(100), -- owner of the tester, which is usually the customer (NOKIA, HALTIAN etc.)
    asset_no VARCHAR(20), 
    additional_info TEXT, -- any additional information about the tester

    -- references
    user_id INT, -- name of the person responsible for the tester
    location_id INT, -- physicallocation of the tester

    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (location_id) REFERENCES tester_and_fixture_locations(location_id)
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

-- holds all essential information about suppliers of spare parts for testers
CREATE TABLE tester_spare_part_suppliers (
    supplier_id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- holds all essential information about fixtures associated with testers
CREATE TABLE fixtures (
    fixture_id INT PRIMARY KEY AUTO_INCREMENT,
    fixture_name VARCHAR(100) NOT NULL, -- name of the fixture
    fixture_description TEXT, -- detailed description of the fixture
    fixture_manufacturer VARCHAR(100), -- manufacturer of the fixture
    fixture_status ENUM('active','inactive','maintenance'), -- current status of the fixture
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- index for faster lookups of fixtures by tester
    INDEX idx_fixtures_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- reference to the tester this fixture is associated with
    location_id INT, -- physical location of the fixture

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (location_id) REFERENCES tester_and_fixture_locations(location_id)
);

-- holds all information on changes made to testers and fixtures
CREATE TABLE tester_and_fixture_changes (
    change_id INT PRIMARY KEY AUTO_INCREMENT,
    change_date DATETIME NOT NULL, -- when the change was made
    explanation TEXT NOT NULL, -- explanation of the change

    -- index for faster lookups of changes by tester
    INDEX idx_tester_changes_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- which tester this change is related to
    fixture_id INT, -- fixture ID if the change is related to a fixture
    user_id INT, -- who made the change

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (fixture_id) REFERENCES fixtures(fixture_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- holds all information about issues and solutions related to testers
CREATE TABLE tester_issues (
    issue_id INT PRIMARY KEY AUTO_INCREMENT,
    issue_status ENUM('open', 'closed') DEFAULT 'open' NOT NULL, -- status of the issue

    -- regarding the issue
    reported_date DATETIME NOT NULL, -- when the issue was recorded
    issue_description TEXT NOT NULL, -- description of the tester issue

    -- regarding the solution
    solved_date DATETIME, -- date when the issue was resolved
    solution_description TEXT, -- solution to the issue

    -- index for faster lookups of issues by tester
    INDEX idx_tester_issues_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- which tester has the issue
    detected_by_user_id INT NOT NULL, -- who detected the issue
    solved_by_user_id INT, -- who solved the issue

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (detected_by_user_id) REFERENCES users(user_id),
    FOREIGN KEY (solved_by_user_id) REFERENCES users(user_id)
);

-- holds information about locations that can be used for both testers and fixtures
CREATE TABLE tester_and_fixture_locations (
    location_id INT PRIMARY KEY AUTO_INCREMENT,
    location_name VARCHAR(100) NOT NULL,
    description TEXT,
    address VARCHAR(255) -- should we delete this column?
);

-- holds definitions of maintenance procedures for testers
CREATE TABLE tester_maintenance_procedures (
    maintenance_id INT PRIMARY KEY AUTO_INCREMENT,
    maintenance_type VARCHAR(100) NOT NULL, -- e.g., Preventive Maintenance, Routine Check
    maintenance_interval_value INT NOT NULL, -- numerical value of the maintenance interval
    maintenance_interval_unit VARCHAR(50) NOT NULL, -- unit of time (Days, Weeks, Months or Years)
    maintenance_description TEXT -- detailed description of the maintenance activities
);

-- holds definitions of calibration procedures for testers
CREATE TABLE tester_calibration_procedures (
    calibration_id INT PRIMARY KEY AUTO_INCREMENT,
    calibration_type VARCHAR(100) NOT NULL, -- e.g., Standard Calibration, Full Calibration
    calibration_interval_value INT NOT NULL, -- numerical value of the calibration interval
    calibration_interval_unit VARCHAR(50) NOT NULL, -- unit of time (Days, Weeks, Months or Years)
    calibration_description TEXT -- detailed description of the calibration procedures
);

-- links testers to maintenance procedures
CREATE TABLE tester_maintenance_schedules (
    maintenance_schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    schedule_created_date DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP), -- when the maintenance schedule was created
    last_maintenance_date DATETIME, -- date when maintenance was last performed
    next_maintenance_due DATETIME, -- calculated next maintenance date (USING EVENT!)
    maintenance_status ENUM('Scheduled', 'Overdue') DEFAULT 'Scheduled', -- status of the maintenance (Scheduled, Overdue)

    -- index for faster lookups of maintenance schedules by tester
    INDEX idx_tester_maintenance_schedules_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- the tester that needs maintenance
    maintenance_id INT NOT NULL, -- the maintenance procedure used
    last_maintenance_by_user_id INT, -- who performed the last maintenance
    next_maintenance_by_user_id INT, -- who is scheduled to perform the next maintenance

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (maintenance_id) REFERENCES tester_maintenance_procedures(maintenance_id),
    FOREIGN KEY (last_maintenance_by_user_id) REFERENCES users(user_id),
    FOREIGN KEY (next_maintenance_by_user_id) REFERENCES users(user_id)
);

-- links testers to calibration procedures
CREATE TABLE tester_calibration_schedules (
    calibration_schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    schedule_created_date DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP), -- when this schedule was created
    last_calibration_date DATETIME, -- date when calibration was last performed
    next_calibration_due DATETIME, -- calculated next calibration date (USING EVENT!)
    calibration_status ENUM('Scheduled', 'Overdue') DEFAULT 'Scheduled', -- status of the calibration (Scheduled, Overdue)

    -- index for faster lookups of calibration schedules by tester
    INDEX idx_tester_calibration_schedules_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- reference to the tester
    calibration_id INT NOT NULL, -- reference to the calibration procedure
    last_calibration_by_user_id INT, -- who performed the last calibration
    next_calibration_by_user_id INT, -- who is scheduled to perform the next calibration

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (calibration_id) REFERENCES tester_calibration_procedures(calibration_id),
    FOREIGN KEY (last_calibration_by_user_id) REFERENCES users(user_id),
    FOREIGN KEY (next_calibration_by_user_id) REFERENCES users(user_id)
);

-- holds information about users responsible for tester maintenance/calibration or HR/administration work
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
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
