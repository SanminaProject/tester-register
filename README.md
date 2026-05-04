# Tester Register

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6)](https://livewire.laravel.com/)
[![License](https://img.shields.io/badge/License-MIT-green)](https://opensource.org/licenses/MIT)

Tester Register is a Laravel + Livewire application for managing tester assets and their operational lifecycle.

It provides both:

- a web UI for daily operations (dashboard, testers, fixtures, services, issues, inventory, admin), and
- a versioned API (`/api/v1`) secured with Laravel Sanctum.

## Why This Project Is Useful

This project helps teams keep tester-related data in one place and track work reliably across roles.

Key capabilities:

- tester and customer management
- fixture tracking tied to testers
- maintenance and calibration schedule workflows
- event log and spare part management
- role-based access control via Spatie Permission
- authenticated API for integration with external tools

## Tech Stack

- Backend: Laravel 12, PHP 8.2+
- Frontend: Livewire 3, Volt, Vite, Tailwind CSS
- Auth/API: Laravel Sanctum
- Permissions: spatie/laravel-permission
- Data import/export support: phpoffice/phpspreadsheet
- Testing: PHPUnit + Laravel Dusk

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL (or compatible)

### 1. Install Dependencies

```bash
composer install
npm install
```

### 2. Configure Environment

Create `.env` from `.env.example`, then set your database values.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=
```

Recommended local defaults:

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### 3. Initialize Application

```bash
php artisan key:generate
php artisan migrate --seed
```

### 4. Run the App

Option A: run all local dev processes (server, queue, logs, Vite) with one command:

```bash
composer run dev
```

Option B: run backend/frontend separately:

```bash
php artisan serve
npm run dev
```

Open: `http://127.0.0.1:8000`

## Quick Usage Examples

### Sign In With Seeded Users

After `php artisan migrate --seed`, the default seeded users include:

- `admin@example.com` / `12345678`
- `manager@example.com` / `12345678`
- `technician@example.com` / `12345678`
- `guest@example.com` / `12345678`
- `test@example.com` / `password123`

### API Login and Authenticated Request

Get a token:

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

Use token on a protected endpoint:

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/testers" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Run Tests

```bash
php artisan test
php artisan test tests/Feature
php artisan test tests/Unit
php artisan dusk
```

## Project Layout

```text
app/                    Application logic (models, controllers, Livewire)
config/                 Framework and package configuration
database/               Migrations, factories, seeders
docs/                   API and local development documentation
resources/              Frontend assets and Blade views
routes/                 Web and API route definitions
tests/                  Unit, feature, and browser tests
```

## Help and Documentation

- Local setup: [docs/RUNNING-LOCALLY.md](docs/RUNNING-LOCALLY.md)
- API design: [docs/API_DESIGN.md](docs/API_DESIGN.md)
- API implementation notes: [docs/API_IMPLEMENTATION_GUIDE.md](docs/API_IMPLEMENTATION_GUIDE.md)
- Current API status: [docs/API_DESIGN_CURRENT_STATE.md](docs/API_DESIGN_CURRENT_STATE.md)
- API migration plan: [docs/API_DESIGN_FUTURE_MIGRATION.md](docs/API_DESIGN_FUTURE_MIGRATION.md)
- Postman collection: [Tester_Register_API.postman_collection.json](Tester_Register_API.postman_collection.json)
- Test structure notes: [tests/README.md](tests/README.md)

If you find a bug or want to request a feature, open an issue in this repository with:

- expected behavior
- actual behavior
- reproduction steps
- relevant logs or screenshots

## Maintainers and Contributing

This project is maintained by the repository owners and active contributors.

Contributions are welcome. A good contribution flow is:

1. Fork and create a feature branch.
2. Keep changes focused and scoped.
3. Run formatting and tests before opening a PR.
4. Include context in the PR description (problem, approach, validation).

Suggested pre-PR checks:

```bash
php artisan test
php artisan dusk
./vendor/bin/pint
```

For code organization and conventions, follow existing patterns in:

- `app/Http/Controllers`
- `app/Livewire`
- `routes/`
- `tests/`

## License

Project/package metadata declares MIT licensing.

Third-party license details are documented in [docs/LICENSES.md](docs/LICENSES.md).
