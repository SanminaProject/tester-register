-- TODO: add tester_documents table later if needed (would store PDFs etc. with info related to the tester)

-- holds all essential information about testers
CREATE TABLE testers (
    tester_id INT PRIMARY KEY AUTO_INCREMENT,
    tester_name VARCHAR(100) NOT NULL, -- name of the tester
    tester_name_en VARCHAR(100), -- tester name in English
    tester_description TEXT, -- detailed description of the tester
    tester_running_number INT, -- running number for the tester
    customer_id VARCHAR(50), -- customer ID associated with the tester
    operating_system VARCHAR(50), -- operating system used by the tester
    tester_type VARCHAR(50), -- type of tester
    tester_status ENUM('active','inactive','maintenance'), -- current status of the tester
    location VARCHAR(100),  -- physical location of the tester
    product_family VARCHAR(100), -- product family associated with the tester
    manufacturer VARCHAR(100), -- manufacturer of the tester
    implementation_date DATE, -- date when the tester was implemented
    owner_name VARCHAR(100), -- name of the person responsible for the tester
    asset_no1 VARCHAR(20), 
    -- asset_no2 VARCHAR(20),
    -- asset_no3 VARCHAR(20),
    -- asset_no4 VARCHAR(20),
    -- asset_no5 VARCHAR(20), (were all asset numbers needed?)
    purchase_price DECIMAL(12,2), 
    net_price_value DECIMAL(12,2),
    additional_info TEXT -- any additional information about the tester
);

-- holds all essential information about spare parts associated with testers
CREATE TABLE tester_spare_parts (
    part_id INT PRIMARY KEY AUTO_INCREMENT,
    part_name VARCHAR(255) NOT NULL,
    manufacturer_part_number VARCHAR(255),
    quantity_in_stock INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL, -- alarm level of when to reorder
    supplier VARCHAR(255),
    last_order_date DATE,
    unit_price DECIMAL(10,2),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- index for faster lookups of spare parts by tester
    INDEX idx_tester_spare_parts_tester (tester_id),

    -- references
    tester_id INT NOT NULL,

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id)
);

-- holds all essential information about fixtures associated with testers
CREATE TABLE fixtures (
    fixture_id INT PRIMARY KEY AUTO_INCREMENT,
    fixture_name VARCHAR(100) NOT NULL, -- name of the fixture
    fixture_description TEXT, -- detailed description of the fixture
    version VARCHAR(50) NOT NULL, -- version of the fixture
    last_used_date DATE, -- date when the fixture was last used
    fixture_manufacturer VARCHAR(100), -- manufacturer of the fixture
    fixture_location VARCHAR(100), -- physical location of the fixture
    fixture_status ENUM('active','inactive','maintenance'), -- current status of the fixture
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- index for faster lookups of fixtures by tester
    INDEX idx_fixtures_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- reference to the tester this fixture is associated with

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id)
);

-- holds all information on changes made to testers
CREATE TABLE tester_changes (
    change_id INT PRIMARY KEY AUTO_INCREMENT,
    change_date DATETIME NOT NULL, -- when the change was made
    explanation TEXT NOT NULL, -- explanation of the change
    changed_by VARCHAR(100) NOT NULL, -- who made the change

    -- index for faster lookups of changes by tester
    INDEX idx_tester_changes_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- which tester this change is related to
    fixture_id INT, -- fixture ID if the change is related to a fixture

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (fixture_id) REFERENCES fixtures(fixture_id)
);

-- holds all information about issues and solutions related to testers
CREATE TABLE tester_issues (
    issue_id INT PRIMARY KEY AUTO_INCREMENT,
    issue_status ENUM('open', 'closed') DEFAULT 'open' NOT NULL, -- status of the issue

    -- regarding the issue
    reported_date DATETIME NOT NULL, -- when the issue was recorded
    issue_description TEXT NOT NULL, -- description of the tester issue
    detected_by VARCHAR(100) NOT NULL, -- person who detected the issue

    -- regarding the solution
    solved_date DATETIME, -- date when the issue was resolved
    solution_description TEXT, -- solution to the issue
    solved_by VARCHAR(100), -- person who solved the issue

    -- index for faster lookups of issues by tester
    INDEX idx_tester_issues_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- which tester has the issue

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id)
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
    last_maintenance_by VARCHAR(100), -- who performed the last maintenance
    next_maintenance_due DATETIME, -- calculated next maintenance date (USING EVENT!)
    next_maintenance_by VARCHAR(100), -- who is scheduled to perform the next maintenance
    maintenance_status ENUM('Scheduled', 'Overdue') DEFAULT 'Scheduled', -- status of the maintenance (Scheduled, Overdue)

    -- index for faster lookups of maintenance schedules by tester
    INDEX idx_tester_maintenance_schedules_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- the tester that needs maintenance
    maintenance_id INT NOT NULL, -- the maintenance procedure used

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (maintenance_id) REFERENCES tester_maintenance_procedures(maintenance_id)
);

-- links testers to calibration procedures
CREATE TABLE tester_calibration_schedules (
    calibration_schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    schedule_created_date DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP), -- when this schedule was created
    last_calibration_date DATETIME, -- date when calibration was last performed
    last_calibration_by VARCHAR(100), -- who performed the last calibration
    next_calibration_due DATETIME, -- calculated next calibration date (USING EVENT!)
    next_calibration_by VARCHAR(100), -- who is scheduled to perform the next calibration
    calibration_status ENUM('Scheduled', 'Overdue') DEFAULT 'Scheduled', -- status of the calibration (Scheduled, Overdue)

    -- index for faster lookups of calibration schedules by tester
    INDEX idx_tester_calibration_schedules_tester (tester_id),

    -- references
    tester_id INT NOT NULL, -- reference to the tester
    calibration_id INT NOT NULL, -- reference to the calibration procedure

    FOREIGN KEY (tester_id) REFERENCES testers(tester_id),
    FOREIGN KEY (calibration_id) REFERENCES tester_calibration_procedures(calibration_id)
);

-- holds information about personnel responsible for tester maintenance
CREATE TABLE tester_maintenance_personnel (
    maintenance_personnel_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role_title VARCHAR(150) NOT NULL, -- role or title of the personnel
    email VARCHAR(255) UNIQUE NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    maintenance_responsibilities TEXT, -- description of the maintenance tasks they are responsible for
    qualifications_certifications TEXT, -- any relevant qualifications or certifications they hold
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- links maintenance personnel to testers they are responsible for
CREATE TABLE tester_maintenance_personnel_assignments (
    maintenance_personnel_id INT NOT NULL,
    tester_id INT NOT NULL,

    PRIMARY KEY (maintenance_personnel_id, tester_id),

    FOREIGN KEY (maintenance_personnel_id) REFERENCES tester_maintenance_personnel(maintenance_personnel_id),
    FOREIGN KEY (tester_id) REFERENCES testers(tester_id)
);