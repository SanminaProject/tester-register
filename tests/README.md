# Tests Folder Structure

The `tests` folder contains all automated tests for this Laravel project, ensuring code quality and preventing regressions. Tests are organized into different categories based on their purpose.

## Folder Structure

```
tests/
├── TestCase.php              # Base test class with common setup and utilities
├── Feature/                  # Feature/Integration tests
│   ├── Auth/                 # Authentication-related tests
│   ├── ExampleTest.php       # Example feature test
│   ├── ProfileTest.php       # User profile feature tests
│   └── UserRolesTest.php     # User roles feature tests
└── Unit/                     # Unit tests
    └── ExampleTest.php       # Example unit test
```

## Running Tests

Run all tests:
```bash
php artisan test
```

Run only feature tests:
```bash
php artisan test tests/Feature
```

Run only E2E tests
```bash
php artisan test tests/Browser
```

Run only unit tests:
```bash
php artisan test tests/Unit
```

Run a specific test file:
```bash
php artisan test tests/Feature/ProfileTest.php
```

## E2E tests

Chrome version used by dusk must match version installed on current device.

To download the correct ChromDriver to be used by code based on what your current device has, run:
```bash
php artisan dusk:chrome-driver --version
```