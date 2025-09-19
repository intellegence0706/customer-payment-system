# Customer Payment System - Operation Manual
## Complete Guide for Beginners

---

## 📋 Table of Contents

1. [What is This System?](#what-is-this-system)
2. [System Requirements](#system-requirements)
3. [Installation Guide](#installation-guide)
4. [First Time Setup](#first-time-setup)
5. [Daily Operations](#daily-operations)
6. [Backup and Restore](#backup-and-restore)
7. [User Management](#user-management)
8. [Troubleshooting](#troubleshooting)
9. [Maintenance](#maintenance)
10. [Security Best Practices](#security-best-practices)

---

## 🎯 What is This System?

The **Customer Payment System** is a web-based application that helps businesses:

- **Manage customer information** (names, contact details, bank information)
- **Track payments** from customers
- **Generate reports** for business analysis
- **Handle bulk data** through CSV uploads
- **Maintain secure user access** with different permission levels

Think of it as a digital filing cabinet that organizes all your customer and payment information in one place!

---

## 💻 System Requirements

### Minimum Requirements
- **Operating System**: Linux, Windows, or macOS
- **PHP**: Version 8.1 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache or Nginx (or PHP built-in server for testing)
- **Memory**: At least 512MB RAM
- **Storage**: At least 1GB free space

### Recommended Requirements
- **PHP**: Version 8.2 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Memory**: 2GB RAM or more
- **Storage**: 5GB+ free space
- **Web Server**: Apache with mod_rewrite enabled

---

## 🚀 Installation Guide

### Step 1: Download the System

```bash
# Navigate to your development directory
cd /home/ksb/dev

# Clone or download the project files
# (You should already have the files in customer-payment-system folder)
```

### Step 2: Install PHP and Required Software

#### On Ubuntu/Debian:
```bash
sudo apt update
sudo apt install php8.1 php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath
sudo apt install mysql-server composer
```

#### On CentOS/RHEL:
```bash
sudo yum install epel-release
sudo yum install php php-mysql php-mbstring php-xml php-curl php-zip php-gd php-bcmath
sudo yum install mysql-server composer
```

### Step 3: Install Dependencies

```bash
# Navigate to the project directory
cd customer-payment-system

# Install PHP dependencies
composer install
```

### Step 4: Database Setup

```bash
# Start MySQL service
sudo systemctl start mysql
sudo systemctl enable mysql

# Secure MySQL installation
sudo mysql_secure_installation

# Create database
mysql -u root -p
CREATE DATABASE customer_management;
CREATE USER 'your_username'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON customer_management.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 5: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Edit the .env file with your database details
nano .env
```

**Important settings in .env file:**
```env
APP_NAME="Customer Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=customer_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 6: Final Setup

```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Create storage links
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache

# Clear caches
php artisan config:clear
php artisan cache:clear
```

### Step 7: Test the Installation

```bash
# Start the development server
php artisan serve
```

Open your web browser and go to: `http://localhost:8000`

---

## 🎯 First Time Setup

### Creating Your First User

1. **Access the registration page**: Go to `http://localhost:8000/register`
2. **Fill in your details**:
   - Name: Your full name
   - Email: Your email address
   - Password: Choose a strong password
   - Role: Select "Admin" for full access
3. **Click Register**
4. **Log in** with your new account

### Initial Configuration

1. **Set up backup directory**:
   ```bash
   mkdir -p /home/ksb/backups
   chmod 755 /home/ksb/backups
   ```

2. **Make backup scripts executable**:
   ```bash
   chmod +x backup.sh restore.sh auto_backup.sh setup_cron.sh
   ```

3. **Configure automated backups** (optional):
   ```bash
   ./setup_cron.sh
   ```

---

## 🔄 Daily Operations

### Starting the System

#### Option 1: Development Mode (for testing)
```bash
cd /home/ksb/dev/customer-payment-system
php artisan serve
```

#### Option 2: Production Mode (recommended for daily use)
```bash
# Set up your web server (Apache/Nginx) to point to the public/ directory
# The system will start automatically when accessed via web browser
```

### Daily Tasks

#### 1. **Customer Management**
- **Add new customers**: Go to Customers → Create
- **Update customer info**: Go to Customers → Edit
- **Search customers**: Use the search bar on the Customers page
- **Export customer data**: Click "Export to CSV" button

#### 2. **Payment Processing**
- **Record payments**: Go to Payments → Create
- **Bulk upload payments**: Go to Payments → Upload CSV
- **View payment history**: Go to Payments → List

#### 3. **Report Generation**
- **Customer reports**: Go to Reports → Customer Reports
- **Payment reports**: Go to Reports → Payment Reports
- **Export reports**: Download as CSV or PDF

### Weekly Tasks

- **Review backup logs**: Check `/home/ksb/backups/backup.log`
- **Verify data integrity**: Run system health checks
- **Update user permissions** if needed

### Monthly Tasks

- **Archive old data** (if needed)
- **Review system performance**
- **Update system documentation**

---

## 💾 Backup and Restore

### Understanding Backup Types

1. **Manual Backup** (`backup.sh`): Creates a complete backup when you run it
2. **Automated Backup** (`auto_backup.sh`): Runs automatically via cron jobs
3. **Restore** (`restore.sh`): Restores your system from a backup

### Creating a Manual Backup

```bash
# Navigate to project directory
cd /home/ksb/dev/customer-payment-system

# Run backup script
./backup.sh
```

**What gets backed up:**
- ✅ Database (all customer and payment data)
- ✅ Application code
- ✅ Uploaded files
- ✅ Configuration files (without sensitive data)
- ❌ Vendor files (can be reinstalled)
- ❌ Log files (not needed for restore)

### Setting Up Automated Backups

```bash
# Run the cron setup script
./setup_cron.sh

# Choose your preferred schedule:
# 1. Daily at 2:00 AM
# 2. Weekly on Sunday at 3:00 AM
# 3. Monthly on 1st day at 4:00 AM
# 4. Custom schedule
```

### Restoring from Backup

```bash
# List available backups
ls -la /home/ksb/backups/

# Restore from a specific backup
./restore.sh /home/ksb/backups/customer-payment-system_backup_20241201_143022.tar.gz
```

**⚠️ Important**: Restoring will overwrite your current system. Always create a backup before restoring!

### Backup File Locations

- **Backup files**: `/home/ksb/backups/`
- **Backup logs**: `/home/ksb/backups/backup.log`
- **Temporary files**: `/tmp/` (automatically cleaned up)

---

## 👥 User Management

### User Roles

#### **Admin** (Full Access)
- ✅ Create, edit, and delete users
- ✅ Access all system features
- ✅ Generate all reports
- ✅ System configuration
- ✅ Backup and restore operations

#### **Manager** (Limited Admin Access)
- ✅ Customer management
- ✅ Payment processing
- ✅ Report generation
- ✅ Limited administrative functions
- ❌ User management
- ❌ System configuration

#### **User** (Basic Access)
- ✅ View customer information
- ✅ Enter payment data
- ✅ Basic reporting
- ❌ Customer management
- ❌ Administrative functions

### Adding New Users

1. **Log in as Admin**
2. **Go to User Management** (if available)
3. **Click "Add User"**
4. **Fill in user details**:
   - Name
   - Email
   - Password
   - Role selection
5. **Save the user**

### Managing User Permissions

- **Change roles**: Edit user profile and select new role
- **Deactivate users**: Set account status to inactive
- **Reset passwords**: Users can reset via email or admin can set new password

---

## 🛠️ Troubleshooting

### Common Problems and Solutions

#### 1. **"Database Connection Failed" Error**

**Symptoms**: System shows database connection error
**Causes**: MySQL not running, wrong credentials, database doesn't exist

**Solutions**:
```bash
# Check if MySQL is running
sudo systemctl status mysql

# Start MySQL if stopped
sudo systemctl start mysql

# Verify database exists
mysql -u your_username -p
SHOW DATABASES;

# Check .env file configuration
cat .env | grep DB_
```

#### 2. **"Permission Denied" Error**

**Symptoms**: Can't write to storage, uploads fail
**Causes**: Incorrect file permissions

**Solutions**:
```bash
# Fix storage permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Fix ownership (if needed)
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/
```

#### 3. **"Page Not Found" Error**

**Symptoms**: 404 errors on all pages
**Causes**: Web server not configured properly, missing .htaccess

**Solutions**:
```bash
# Check if .htaccess exists
ls -la public/

# Recreate storage links
php artisan storage:link

# Clear route cache
php artisan route:clear
```

#### 4. **"Backup Failed" Error**

**Symptoms**: Backup script shows errors
**Causes**: Insufficient disk space, wrong paths, permission issues

**Solutions**:
```bash
# Check available disk space
df -h

# Verify backup directory exists
ls -la /home/ksb/backups/

# Check script permissions
ls -la *.sh

# Make scripts executable
chmod +x *.sh
```

#### 5. **"Composer Install Failed" Error**

**Symptoms**: Can't install PHP dependencies
**Causes**: PHP version too old, missing extensions

**Solutions**:
```bash
# Check PHP version
php -v

# Install missing PHP extensions
sudo apt install php8.1-mysql php8.1-mbstring php8.1-xml

# Clear composer cache
composer clear-cache
```

### Getting Help

1. **Check the logs**: Look in `storage/logs/laravel.log`
2. **Review error messages**: They often contain specific solutions
3. **Check system status**: Verify all services are running
4. **Search online**: Many Laravel errors have documented solutions

---

## 🔧 Maintenance

### Regular Maintenance Tasks

#### **Daily**
- Check system is running
- Verify backups completed successfully
- Monitor error logs

#### **Weekly**
- Review backup logs
- Check disk space usage
- Verify database integrity

#### **Monthly**
- Update system packages
- Review user access permissions
- Archive old data if needed

### System Updates

```bash
# Update PHP dependencies
composer update

# Update Laravel framework
composer update laravel/framework

# Clear caches after updates
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Performance Optimization

```bash
# Optimize autoloader
composer dump-autoload --optimize

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

---

## 🔒 Security Best Practices

### Password Security

- **Use strong passwords**: At least 12 characters with mixed case, numbers, and symbols
- **Change passwords regularly**: Every 90 days
- **Don't share passwords**: Each user should have unique credentials

### Access Control

- **Limit admin access**: Only give admin role to trusted users
- **Regular access review**: Review user permissions monthly
- **Remove inactive users**: Deactivate accounts for users who leave

### Data Protection

- **Regular backups**: Automated daily backups
- **Secure backup storage**: Store backups in secure location
- **Test restore procedures**: Verify backups work monthly

### System Security

- **Keep software updated**: Regular security updates
- **Monitor access logs**: Check for unusual activity
- **Use HTTPS**: Enable SSL in production
- **Firewall protection**: Restrict access to necessary ports only

---

## 📞 Emergency Procedures

### System Down - What to Do

1. **Don't panic** - Most issues can be resolved quickly
2. **Check basic services**:
   ```bash
   sudo systemctl status mysql
   sudo systemctl status apache2  # or nginx
   ```
3. **Check error logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```
4. **Restart services if needed**:
   ```bash
   sudo systemctl restart mysql
   sudo systemctl restart apache2
   ```

### Data Loss - Recovery Steps

1. **Stop using the system** to prevent further data loss
2. **Locate your latest backup**:
   ```bash
   ls -la /home/ksb/backups/
   ```
3. **Restore from backup**:
   ```bash
   ./restore.sh /path/to/backup/file.tar.gz
   ```
4. **Verify data integrity** after restore

### Security Breach - Immediate Actions

1. **Change all passwords** immediately
2. **Review access logs** for suspicious activity
3. **Restore from clean backup** if system compromised
4. **Contact security team** if available

---

## 📚 Additional Resources

### Useful Commands

```bash
# Check system status
php artisan about

# View all routes
php artisan route:list

# Check database status
php artisan migrate:status

# Clear all caches
php artisan optimize:clear

# View system logs
tail -f storage/logs/laravel.log
```

### File Locations

- **Application code**: `/home/ksb/dev/customer-payment-system/`
- **Configuration**: `.env` file
- **Database**: MySQL database `customer_management`
- **Uploads**: `storage/app/public/`
- **Logs**: `storage/logs/`
- **Backups**: `/home/ksb/backups/`

### Getting More Help

1. **Laravel Documentation**: https://laravel.com/docs
2. **PHP Documentation**: https://php.net/manual
3. **MySQL Documentation**: https://dev.mysql.com/doc
4. **System Administrator**: Contact your IT team

---

## 🎉 Congratulations!

You've completed the **Customer Payment System Operation Manual**! 

This system is designed to be:
- ✅ **Easy to use** - Simple web interface
- ✅ **Reliable** - Automated backups and error handling
- ✅ **Secure** - Role-based access control
- ✅ **Maintainable** - Clear procedures and troubleshooting

### Quick Start Checklist

- [ ] System installed and running
- [ ] First user account created
- [ ] Database configured
- [ ] Backup system set up
- [ ] Daily operations understood
- [ ] Emergency procedures reviewed

### Remember

- **Always backup before major changes**
- **Keep your passwords secure**
- **Monitor the system regularly**
- **Don't hesitate to ask for help**

---

**Happy Operating! 🚀**

*This manual was created to help you successfully operate the Customer Payment System. If you have questions or need clarification, refer to the troubleshooting section or contact your system administrator.*
