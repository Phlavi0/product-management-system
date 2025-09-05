# Laravel Backend API

RESTful API for Product Management System built with Laravel 10.x.

## Features

- **Product CRUD Operations**: Create, Read, Update, Delete products
- **Hierarchical Structure**: Support for parent-child product relationships
- **Advanced Filtering**: Filter by type, search by name, parent relationships
- **Request Validation**: Comprehensive validation for all endpoints
- **Database Relationships**: Eloquent relationships for efficient data handling

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
# Create database (if using MySQL)
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

## API Endpoints

### Products

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/products` | Get all products with optional filtering |
| POST | `/api/products` | Create a new product |
| GET | `/api/products/{id}` | Get a specific product |
| PUT | `/api/products/{id}` | Update a product |
| DELETE | `/api/products/{id}` | Delete a product |
| GET | `/api/products/{id}/children` | Get child products of a specific product |
| GET | `/api/products-tree` | Get products in tree structure |

### Query Parameters for GET /api/products

- `search` - Search products by name
- `type` - Filter by product type
- `parent_id` - Filter by parent ID
- `roots_only` - Get only root products (no parent)

### Request/Response Examples

#### Create Product

**POST** `/api/products`

```json
{
  "product_name": "iPhone 15 Pro Max",
  "product_type": "product",
  "product_parent_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "product_id": 12,
    "product_name": "iPhone 15 Pro Max",
    "product_type": "product",
    "product_parent_id": 1,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z",
    "parent": {
      "product_id": 1,
      "product_name": "Mobile Phones",
      "product_type": "subcategory"
    },
    "children": []
  }
}
```

#### Get Products with Filtering

**GET** `/api/products?search=iPhone&type=product`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "product_id": 7,
      "product_name": "iPhone 15 Pro",
      "product_type": "product",
      "product_parent_id": 4,
      "parent": {
        "product_id": 4,
        "product_name": "Mobile Phones",
        "product_type": "subcategory"
      },
      "children": []
    }
  ]
}
```

#### Get Product Tree

**GET** `/api/products-tree`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "product_id": 1,
      "product_name": "Electronics",
      "product_type": "category",
      "product_parent_id": null,
      "descendants": [
        {
          "product_id": 3,
          "product_name": "Computers",
          "product_type": "subcategory",
          "product_parent_id": 1,
          "descendants": [
            {
              "product_id": 5,
              "product_name": "MacBook Pro 16\"",
              "product_type": "product",
              "product_parent_id": 3
            }
          ]
        }
      ]
    }
  ]
}
```

## Database Schema

### Products Table

```sql
CREATE TABLE products (
    product_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    product_type VARCHAR(100) NOT NULL,
    product_parent_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (product_parent_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product_parent_id (product_parent_id),
    INDEX idx_product_type (product_type)
);
```

## Model Relationships

- **Parent**: `belongsTo` - A product can have one parent
- **Children**: `hasMany` - A product can have multiple children
- **Descendants**: Recursive relationship to get all nested children

## Validation Rules

### Create Product
- `product_name`: Required, string, max 255 characters
- `product_type`: Required, string, max 100 characters
- `product_parent_id`: Optional, must exist in products table

### Update Product
- Same as create, plus validation to prevent self-referencing (product cannot be its own parent)

## CORS Configuration

For frontend integration, add CORS configuration:

```bash
# Install Laravel Sanctum for API authentication (optional)
composer require laravel/sanctum

# Publish CORS config
php artisan config:publish cors
```

Edit `config/cors.php` to allow your frontend domain:

```php
'allowed_origins' => ['http://localhost:9000'], // Quasar default port
```

## Testing

Run the built-in tests:

```bash
php artisan test
```

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `.env`
   - Ensure database server is running
   - Check if database exists

2. **Foreign Key Constraint Errors**
   - Ensure parent products exist before creating children
   - Run migrations in correct order

3. **Permission Errors**
   - Check file permissions for storage and bootstrap/cache directories
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

## Production Deployment

1. **Environment Optimization**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. **Security**
- Set `APP_ENV=production` in `.env`
- Use strong `APP_KEY`
- Configure proper database permissions
- Use HTTPS in production

## API Testing

Use tools like Postman, Insomnia, or curl to test the API endpoints. A Postman collection can be created for easy testing.

Example curl command:
```bash
curl -X GET "http://localhost:8000/api/products" \
     -H "Accept: application/json"
```