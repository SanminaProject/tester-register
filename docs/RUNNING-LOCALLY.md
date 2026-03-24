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
3. Install Node.js dependencies
```
npm install
```
4. Set up your environment file
- Create a .env file to root and add the contents of .env.example there
- Update the .env file with your database settings
```
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=
```
6. Generate Laravel application key
```
php artisan key:generate
```
7. Build frontend assets
```
npm run build
```
8. Start Apache and MySQL from XAMPP
9. Create a database in phpMyAdmin
10. If needed, run database migrations
```
php artisan migrate
```
11. Start the Laravel development server
```
php artisan serve
```
12. Run frontend
```
npm run dev
```
13. Access the application from: http://127.0.0.1:8000