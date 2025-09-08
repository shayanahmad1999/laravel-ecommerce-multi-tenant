# Laravel E-commerce Multi-Tenant Application

A comprehensive multi-tenant e-commerce platform built with Laravel 12, featuring tenant isolation, role-based permissions, product management, order processing, payment handling, and advanced reporting capabilities.

## Features

### 🏢 Multi-Tenant Architecture
- **Tenant Isolation**: Each tenant has its own domain and isolated data
- **Domain Management**: Unique domains for each tenant
- **Database Separation**: Separate databases or schema isolation per tenant
- **Tenant Administration**: Complete tenant lifecycle management

### 👥 User Management & Authentication
- **Laravel Sanctum**: API authentication
- **Role-Based Access Control**: Admin, Customer roles with granular permissions
- **User Registration & Login**: Complete authentication system
- **Profile Management**: User profile and preferences

### 🛍️ Product Management
- **Product Catalog**: Comprehensive product information with categories
- **Stock Management**: Real-time inventory tracking with low stock alerts
- **Image Upload**: Multiple product images support
- **SKU Management**: Unique product identifiers
- **Pricing**: Cost price, selling price, and markup calculations

### 🛒 Order Processing
- **Shopping Cart**: Session-based cart with AJAX functionality
- **Order Creation**: Complete order lifecycle management
- **Order Status Tracking**: Pending, Processing, Shipped, Delivered, Cancelled
- **Order History**: Complete order tracking for customers

### 💳 Payment System
- **Multiple Payment Types**: Instant payment and installment options
- **Installment Management**: Flexible installment plans (2-60 months)
- **Payment Tracking**: Complete payment history and status
- **Payment Methods**: Credit card, debit card, bank transfer, cash, digital wallet

### 📊 Reporting & Analytics
- **Dashboard**: Real-time statistics and KPIs
- **Sales Analytics**: Daily/weekly/monthly sales tracking
- **Customer Reports**: Customer behavior and purchase history
- **Inventory Reports**: Stock levels and low stock alerts
- **Financial Reports**: Profit & loss, balance sheet, ledger
- **Order Analytics**: Order status and payment type distribution

### 🎨 Frontend
- **Tailwind CSS**: Modern, responsive design
- **Bootstrap**: Additional UI components
- **Vite**: Fast build tool and hot module replacement
- **Blade Templates**: Server-side rendering with Laravel Blade

## Tech Stack

### Backend
- **Laravel 12**: PHP web framework
- **PHP 8.2+**: Server-side scripting
- **MySQL/SQLite**: Database storage
- **Spatie Laravel Multitenancy**: Multi-tenant functionality
- **Spatie Laravel Permission**: Role and permission management

### Frontend
- **Tailwind CSS 4.0**: Utility-first CSS framework
- **Bootstrap 5.2**: CSS framework
- **Vite 7.0**: Build tool
- **Axios**: HTTP client for AJAX requests

### Development Tools
- **Composer**: PHP dependency management
- **NPM**: Node.js package management
- **Laravel Pint**: Code style fixer
- **Laravel Sail**: Docker development environment

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL or SQLite database

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd laravel-ecommerce-multi-tenant
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   ```
   Configure your database and other settings in `.env`

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Build Assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

8. **Start the Application**
   ```bash
   php artisan serve
   ```

### Multi-Tenant Setup

1. **Configure Multitenancy**
   - Update `config/multitenancy.php` for your tenant configuration
   - Set up tenant database connections if using separate databases

2. **Create Tenants**
   ```bash
   php artisan tenant:create {domain} {name}
   ```

## Usage

### For Administrators
- **Tenant Management**: Create and manage tenant accounts
- **User Management**: Assign roles and permissions
- **Product Management**: Add, edit, and manage product inventory
- **Order Management**: Process and track customer orders
- **Reports**: Access comprehensive business analytics

### For Customers
- **Browse Products**: Search and filter products by category
- **Shopping Cart**: Add items and manage cart contents
- **Checkout**: Complete purchases with instant or installment payments
- **Order Tracking**: Monitor order status and history
- **Profile Management**: Update personal information

## API Endpoints

The application provides RESTful API endpoints for:
- Product management
- Order processing
- Payment handling
- User management
- Reporting and analytics

API documentation available at `/api/documentation`

## Database Schema

### Core Tables
- `tenants`: Tenant information and configuration
- `users`: User accounts with role assignments
- `products`: Product catalog with inventory
- `categories`: Product categorization
- `orders`: Customer orders
- `order_items`: Order line items
- `payments`: Payment records
- `installments`: Installment payment schedules

### Permission Tables
- `roles`: User roles
- `permissions`: System permissions
- `role_has_permissions`: Role-permission relationships
- `model_has_roles`: User-role assignments

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Development Server with Hot Reload
```bash
composer run dev
```

This command runs:
- Laravel development server
- Queue worker
- Laravel logs
- Vite development server

## Deployment

### Production Build
```bash
npm run build
```

### Environment Variables
Configure the following for production:
- `APP_ENV=production`
- `APP_DEBUG=false`
- Database credentials
- Mail configuration
- Cache and session drivers

### Queue Processing
```bash
php artisan queue:work
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and code style checks
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Create an issue in the repository
- Contact the development team
- Check the documentation

---

Built with ❤️ using Laravel
