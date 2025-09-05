# Laravel Backend API

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL 8.0 or PostgreSQL 13+
- Laravel 10.x

## Installation & Setup

### 1. Install Dependencies

```bash
cd backend
composer install
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Configuration

Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=product_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Database Setup

```bash
mysql -u root -p -e "CREATE DATABASE product_management;"

# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed --class=ProductSeeder
```

### 5. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`
