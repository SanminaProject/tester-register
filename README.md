<h1 align="center">Tester Register</h1>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Composer-885630?style=for-the-badge&logo=composer&logoColor=white" alt="Composer" />
</p>

## Running the Project Locally

### Prerequisites
Make sure the following are installed:

- XAMPP
- Composer
- npm

### Setup
1. Clone the repository
2. Install PHP dependencies
```
composer install
```
3. Create a .env file to root and add the contents of .env.example there
4. Update the .env file with your database settings
```
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=
```
5. Create a database in phpMyAdmin
6. Start Apache and MySQL from XAMPP
7. If needed, run database migrations
```
php artisan migrate
```
8. Start the Laravel development server
```
php artisan serve
```
9. Open http://127.0.0.1:8000
