# Customer Management System

A comprehensive Laravel-based customer management system with user authentication, role-based access control, customer management, payment tracking, and reporting capabilities.

## 🚀 Features

### Authentication & Authorization

- **User Registration & Login**: Secure authentication system with role-based access
- **Role Management**: Three user roles (Admin, Manager, User) with different permissions
- **Session Management**: Remember me functionality and secure logout

### Customer Management

- **Customer CRUD**: Create, Read, Update, Delete customer information
- **Customer Search**: Advanced search and filtering capabilities
- **Data Export**: Export customer data to CSV format
- **Bank Integration**: API endpoints for bank and branch name lookup

### Payment Management

- **Payment Tracking**: Record and manage customer payments
- **Bulk Upload**: Upload month-end payment data via CSV
- **Payment Reports**: Generate detailed payment reports
- **Postcard Generation**: Create and export postcard data

### Reporting System

- **Customer Reports**: Generate comprehensive customer reports
- **Payment Reports**: Detailed payment analysis and reporting
- **Role-based Access**: Reports available to Admin and Manager roles only

### Additional Features

- **Modern UI**: Bootstrap 5 with responsive design
- **Data Validation**: Comprehensive form validation
- **Error Handling**: User-friendly error messages
- **Security**: CSRF protection, input sanitization, and secure authentication

## 📋 Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7 or MariaDB >= 10.2
- Web server (Apache/Nginx) or PHP built-in server

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd laravel-customer-management
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
```

Edit the `.env` file with your database credentials:

```env
APP_NAME="Customer Management System"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=customer_management
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=array
SESSION_DRIVER=file
FILESYSTEM_DISK=local
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE customer_management;"

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### 6. Storage Setup

```bash
# Create storage links
php artisan storage:link

# Set proper permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
```

### 7. Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## 🚀 Running the Application

### Development Server

```bash
php artisan serve
```

Access the application at: `http://localhost:8000`

### Production Setup

For production deployment, configure your web server to point to the `public` directory and ensure proper file permissions.

## 👥 User Roles & Permissions

### Admin

- Full access to all features
- User management
- System configuration
- All reports and analytics

### Manager

- Customer management
- Payment processing
- Report generation
- Limited administrative functions

### User

- Basic customer viewing
- Payment entry
- Limited access to features

## 📊 Database Structure

### Users Table

- `id` - Primary key
- `name` - User's full name
- `email` - Email address (unique)
- `password` - Hashed password
- `role` - User role (admin/manager/user)
- `is_active` - Account status
- `email_verified_at` - Email verification timestamp
- `remember_token` - Remember me token
- `created_at` / `updated_at` - Timestamps

### Customers Table

- `id` - Primary key
- `name` - Customer name
- `email` - Email address
- `phone` - Phone number
- `address` - Physical address
- `bank_name` - Bank information
- `branch_name` - Branch information
- `account_number` - Account details
- `created_at` / `updated_at` - Timestamps

### Payments Table

- `id` - Primary key
- `customer_id` - Foreign key to customers
- `amount` - Payment amount
- `payment_date` - Date of payment
- `payment_method` - Method of payment
- `reference_number` - Payment reference
- `notes` - Additional notes
- `created_at` / `updated_at` - Timestamps

## 🔧 Configuration Files

### Essential Config Files Created

- `config/app.php` - Main application configuration
- `config/auth.php` - Authentication settings
- `config/database.php` - Database connections
- `config/cache.php` - Cache configuration
- `config/session.php` - Session management
- `config/view.php` - View configuration
- `config/filesystems.php` - File system settings

## 🎨 Frontend Features

### Authentication Pages

- **Login Page**: Modern design with gradient background
- **Register Page**: User registration with role selection
- **Responsive Design**: Works on all device sizes

### Dashboard

- **Overview Cards**: Key metrics and statistics
- **Recent Activity**: Latest customer and payment updates
- **Quick Actions**: Fast access to common functions

### Customer Management

- **Customer List**: Paginated table with search and filters
- **Customer Form**: Add/edit customer information
- **Export Functionality**: Download customer data as CSV

### Payment Management

- **Payment Entry**: Record new payments
- **Bulk Upload**: Import payment data via CSV
- **Payment History**: View all payment records

## 🔒 Security Features

- **CSRF Protection**: All forms protected against CSRF attacks
- **Input Validation**: Comprehensive server-side validation
- **SQL Injection Protection**: Eloquent ORM with parameter binding
- **XSS Protection**: Output escaping and sanitization
- **Authentication**: Secure login/logout with session management
- **Authorization**: Role-based access control

## 📝 API Endpoints

### Authentication

- `GET /login` - Show login form
- `POST /login` - Process login
- `POST /logout` - User logout
- `GET /register` - Show registration form
- `POST /register` - Process registration

### Customers

- `GET /customers` - List all customers
- `GET /customers/create` - Show create form
- `POST /customers` - Store new customer
- `GET /customers/{id}` - Show customer details
- `GET /customers/{id}/edit` - Show edit form
- `PUT/PATCH /customers/{id}` - Update customer
- `DELETE /customers/{id}` - Delete customer
- `GET /customers-export-csv` - Export customers to CSV

### Payments

- `GET /payments` - List all payments
- `GET /payments/create` - Show create form
- `POST /payments` - Store new payment
- `GET /payments-upload` - Show upload form
- `POST /payments-upload` - Process bulk upload

### Reports

- `GET /reports` - Reports dashboard
- `GET /reports/create` - Create new report
- `POST /reports/customers` - Generate customer report
- `POST /reports/payments` - Generate payment report

## 🐛 Troubleshooting

### Common Issues

#### 1. Database Connection Error

```
SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it
```

**Solution:**

- Ensure MySQL server is running
- Check database credentials in `.env`
- Verify database exists
- Check if MySQL is running on the correct port (3306)

#### 2. Cache Store Error

```
Cache store [file3e] is not defined
```

**Solution:**

- Set `CACHE_DRIVER=array` in `.env`
- Clear all caches: `php artisan config:clear`

#### 3. Missing Configuration Files

```
View path not found
```

**Solution:**

- Ensure all config files are present
- Run `composer dump-autoload`
- Clear configuration cache

#### 4. Permission Denied

```
Permission denied on storage directory
```

**Solution:**

```bash
chmod -R 775 storage bootstrap/cache
```

## 📦 Dependencies

### Laravel Framework

- Laravel 10.x
- PHP 8.1+

### Key Packages

- `laravel/framework` - Core Laravel framework
- `barryvdh/laravel-dompdf` - PDF generation
- `laravel/sanctum` - API authentication
- `laravel/tinker` - REPL for Laravel

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 📞 Support

For support and questions:

- Create an issue in the repository
- Contact the development team
- Check the troubleshooting section above

## 🔄 Updates

### Version 1.0.0

- Initial release
- Basic customer management
- Payment tracking
- User authentication
- Role-based access control
- Reporting system

---

**Happy Coding! 🎉**
