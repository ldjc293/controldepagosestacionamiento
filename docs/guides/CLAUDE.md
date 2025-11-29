# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Database Operations
```bash
# Connect to MySQL database
"C:\xampp\mysql\bin\mysql.exe" -u root estacionamiento_db

# Execute SQL queries
"C:\xampp\mysql\bin\mysql.exe" -u root estacionamiento_db -e "SELECT * FROM usuarios LIMIT 5;"

# Import database schema
mysql -u root estacionamiento_db < database/schema.sql
```

### PHP Operations
```bash
# Run PHP files
"C:\xampp\php\php.exe" -f "path/to/file.php"

# Check PHP syntax
"C:\xampp\php\php.exe" -l "path/to/file.php"

# Run cron jobs manually
"C:\xampp\php\php.exe" "c:\xampp\htdocs\controldepagosestacionamiento\cron\generar_mensualidades.php"
```

### Web Server
```bash
# Open application in browser
start http://localhost/controldepagosestacionamiento

# Access specific pages
start http://localhost/controldepagosestacionamiento/admin/dashboard
start http://localhost/controldepagosestacionamiento/auth/login
```

## Architecture Overview

### MVC Pattern with Custom Routing
This is a custom PHP MVC application with a front controller pattern:

- **Front Controller**: `public/index.php` handles all requests via `.htaccess` rewriting
- **Custom Routing**: URL-based routing with role-based access control
- **Controllers**: Handle HTTP requests and business logic in `app/controllers/`
- **Models**: Data layer using PDO for database operations in `app/models/`
- **Views**: PHP templates with Bootstrap 5 in `app/views/`

### Authentication & Authorization
- **Role-based system**: cliente, operador, consultor, administrador
- **Session management**: PHP sessions with security configurations
- **Route protection**: Public routes vs authenticated routes with role checking
- **CSRF protection**: Tokens generated for forms

### Database Design
Core entities: usuarios, apartamentos, controles_estacionamiento, mensualidades, pagos
- **Controls System**: 250 positions × 2 receivers (A/B) = 500 total controls
- **Multi-currency**: USD base with Bs conversion using BCV rates
- **Monthly billing**: Auto-generated on day 5 of each month
- **Payment workflow**: Upload proofs → approval → receipt generation

### Key Business Logic
- **Control States**: activo, bloqueado, suspendido, desactivado, perdido, vacio
- **Automatic blocking**: 4+ months unpaid = controls blocked
- **Multi-currency payments**: USD cash, Bs transfer, Bs cash
- **Receipt generation**: PDF with QR codes and Google Sheets sync

## Important Implementation Details

### URL Structure & Method Names
**Critical**: URL routing uses kebab-case but PHP method names must be camelCase
- URL: `auth/process-login` → Method: `processLogin()`
- URL: `admin/cambiar-estado` → Method: `cambiarEstado()`

### AJAX Implementation
- **Request headers**: Must include `X-Requested-With: XMLHttpRequest`
- **JSON responses**: Use `jsonResponse()` helper method in controllers
- **CSRF tokens**: Required for all POST requests

### Security Considerations
- **Password hashing**: Uses `password_hash()` with BCRYPT
- **SQL injection**: All queries use PDO prepared statements
- **File uploads**: Validated by MIME type and extension
- **Session security**: Configured with httpOnly, secure, and sameSite settings

### Control Management System
The parking control system manages 500 physical controls:
- **Position numbering**: 1-250 (each with A/B receivers)
- **Example**: Control "15A" = Position 15, Receiver A
- **State changes**: Via AJAX dropdowns with confirmation dialogs
- **Assignment**: Controls can be assigned/unassigned to apartment users

### Configuration Management
- **Environment variables**: Stored in `.env` file
- **Constants**: Defined in `config/config.php`
- **Database settings**: Separate `config/database.php` file
- **UTF-8 encoding**: Configured at application level

## File Structure Key Points

### Controllers Pattern
All controllers follow this structure:
```php
class ControllerName {
    private function checkAuth(): ?User  // Authentication check
    public function method(): void       // Handle request
    private function isAjaxRequest(): bool  // AJAX detection
    private function jsonResponse(): void   // JSON responses
}
```

### Model Conventions
- **Static methods**: `findById()`, `findAll()`, `create()`, `update()`, `delete()`
- **Database operations**: Use `Database::fetch*()` and `Database::execute()`
- **Data validation**: Input sanitization and validation in controllers

### View Organization
- **Role-based views**: Separate folders for cliente/, operador/, consultor/, admin/
- **Shared layouts**: header.php, footer.php, sidebar.php in layouts/
- **Bootstrap integration**: Custom CSS with Bootstrap 5 components

## Common Development Tasks

### Adding New AJAX Endpoints
1. Add method to appropriate controller
2. Implement `isAjaxRequest()` check
3. Use `jsonResponse()` for responses
4. Include CSRF token validation
5. Add `X-Requested-With: XMLHttpRequest` header in JavaScript

### Modifying Control States
1. Update `Control::cambiarEstado()` method if needed
2. Modify state dropdown options in view
3. Update JavaScript `actualizarVistaControl()` function
4. Test with AJAX requests

### Database Schema Changes
1. Create migration SQL files in `database/` folder
2. Update model properties and methods
3. Modify related views and controllers
4. Test with sample data

## Development Environment Setup

### Required Dependencies
Run `composer install` to install:
- phpmailer/phpmailer (Email functionality)
- dompdf/dompdf (PDF generation)
- phpoffice/phpspreadsheet (Excel import/export)
- chillerlan/php-qrcode (QR code generation)
- vlucas/phpdotenv (Environment variables)

### File Permissions
Ensure write permissions for:
- `public/uploads/` (comprobantes, recibos)
- `logs/` (application logs)

### Cron Jobs
Key scheduled tasks:
- `cron/generar_mensualidades.php` (Monthly, day 5)
- `cron/verificar_bloqueos.php` (Daily)
- `cron/enviar_notificaciones.php` (Daily)