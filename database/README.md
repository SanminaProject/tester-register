# Database Folder Structure
The `database` folder contains all files related to the database setup, schema, and seeding for this project.

```database/
├── docs/
│ ├── events.sql # SQL queries related to events
│ ├── schema.sql # SQL statements to create all tables
│ └── ER_diagram.png # Entity-Relationship diagram (diagram coming soon)
├── factories/ # Scripts to generate fake or test data
├── migrations/ # Database migration files
├── seeders/ # Scripts to seed initial data into tables
├── .gitignore # Git ignore file specific to database folder
├── README.md # This file
└── database.sqlite # SQLite database file```