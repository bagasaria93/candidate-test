# CLT Feature Test - Bagas Aria Sativa

## Tech Stack

- PHP 8.3
- Laravel 13
- MySQL
- PHPUnit (automated testing)

## Architecture

- Repository Pattern
- Service Pattern
- Form Request Validation
- Route Model Binding

## Installation

1. Clone repository
2. Install dependencies: composer install
3. Copy environment file: cp .env.example .env
4. Generate application key: php artisan key:generate
5. Configure database in .env:
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=clt_feature_test
   DB_USERNAME=root
   DB_PASSWORD=
6. Run migrations: php artisan migrate
7. Start server: php artisan serve

## API Endpoints

### Suppliers
- GET    /api/suppliers                  - Get all suppliers
- POST   /api/suppliers                  - Create supplier
- GET    /api/suppliers/{id}             - Get supplier by id
- PUT    /api/suppliers/{id}             - Update supplier
- DELETE /api/suppliers/{id}             - Delete supplier

### Layups
- GET    /api/suppliers/{id}/layups              - Get all layups
- POST   /api/suppliers/{id}/layups              - Create layup
- GET    /api/suppliers/{id}/layups/{id}         - Get layup by id
- PUT    /api/suppliers/{id}/layups/{id}         - Update layup
- DELETE /api/suppliers/{id}/layups/{id}         - Delete layup

### Layers
- GET    /api/suppliers/{id}/layups/{id}/layers          - Get all layers
- POST   /api/suppliers/{id}/layups/{id}/layers          - Create layer
- GET    /api/suppliers/{id}/layups/{id}/layers/{id}     - Get layer by id
- PUT    /api/suppliers/{id}/layups/{id}/layers/{id}     - Update layer
- DELETE /api/suppliers/{id}/layups/{id}/layers/{id}     - Delete layer

### Import / Export
- GET    /api/suppliers/{id}/export     - Export supplier with all layups and layers
- POST   /api/suppliers/{id}/import     - Import layups and layers under supplier

## Import Request Format

{
    "data": {
        "layups": [
            {
                "name": "Layup A",
                "description": "Description",
                "layers": [
                    {
                        "layer_order": 1,
                        "thickness": 10.5,
                        "width": 200.0,
                        "angle": 45.0
                    }
                ]
            }
        ]
    },
    "conflict_strategy": "overwrite"
}

## Conflict Resolution Strategies

- overwrite  : Incoming data replaces existing data
- skip       : Keep existing data, ignore incoming change
- duplicate  : Create new layup with suffix (imported)
- reject     : Abort import and return detailed conflict report

## Running Tests

php artisan test

Expected: 26 tests, 85 assertions, all passing.

## Data Structure

Supplier
└── Layups
    └── Layers

### Supplier Fields
- name (required)
- email (optional)
- phone (optional)
- address (optional)

### Layup Fields
- supplier_id (required)
- name (required)
- description (optional)

### Layer Fields
- layup_id (required)
- layer_order (required)
- thickness (required)
- width (required)
- angle (required)