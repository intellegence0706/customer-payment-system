#!/bin/bash

# Laravel Customer Payment System Backup Script
# This script creates a complete backup of your system

# Configuration
PROJECT_NAME="customer-payment-system"
BACKUP_DIR="/home/ksb/backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="${PROJECT_NAME}_backup_${DATE}"

# Database configuration (from .env file)
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2 | xargs)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2 | xargs)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2 | xargs)
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2 | xargs)

# Create backup directory if it doesn't exist
mkdir -p "${BACKUP_DIR}"

echo "Starting backup of ${PROJECT_NAME} at $(date)"
echo "Backup will be saved to: ${BACKUP_DIR}/${BACKUP_NAME}"

# Create temporary backup directory
TEMP_BACKUP_DIR="/tmp/${BACKUP_NAME}"
mkdir -p "${TEMP_BACKUP_DIR}"

# 1. Database Backup
echo "Backing up database..."
if [ -n "$DB_PASSWORD" ]; then
    mysqldump -h"${DB_HOST}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" > "${TEMP_BACKUP_DIR}/database.sql"
else
    mysqldump -h"${DB_HOST}" -u"${DB_USERNAME}" "${DB_DATABASE}" > "${TEMP_BACKUP_DIR}/database.sql"
fi

if [ $? -eq 0 ]; then
    echo "✓ Database backup completed"
else
    echo "✗ Database backup failed"
    exit 1
fi

# 2. Code Backup (excluding vendor, node_modules, and other unnecessary files)
echo "Backing up application code..."
rsync -av --exclude='vendor/' \
         --exclude='node_modules/' \
         --exclude='storage/logs/*' \
         --exclude='storage/framework/cache/*' \
         --exclude='storage/framework/sessions/*' \
         --exclude='storage/framework/views/*' \
         --exclude='.git/' \
         --exclude='.env' \
         --exclude='backup.sh' \
         --exclude='*.log' \
         . "${TEMP_BACKUP_DIR}/code/"

if [ $? -eq 0 ]; then
    echo "✓ Code backup completed"
else
    echo "✗ Code backup failed"
    exit 1
fi

# 3. Environment file backup (without sensitive data)
echo "Backing up environment configuration..."
cp .env "${TEMP_BACKUP_DIR}/env_backup.txt"
# Remove sensitive data from env backup
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=***REMOVED***/' "${TEMP_BACKUP_DIR}/env_backup.txt"
sed -i 's/APP_KEY=.*/APP_KEY=***REMOVED***/' "${TEMP_BACKUP_DIR}/env_backup.txt"
sed -i 's/MAIL_PASSWORD=.*/MAIL_PASSWORD=***REMOVED***/' "${TEMP_BACKUP_DIR}/env_backup.txt"

# 4. Uploaded files backup
echo "Backing up uploaded files..."
if [ -d "storage/app/public" ]; then
    rsync -av storage/app/public/ "${TEMP_BACKUP_DIR}/uploads/"
    echo "✓ Uploaded files backup completed"
else
    echo "! No uploaded files directory found"
fi

# 5. Create backup info file
echo "Creating backup information..."
cat > "${TEMP_BACKUP_DIR}/backup_info.txt" << EOF
Backup Information
==================
Project: ${PROJECT_NAME}
Date: $(date)
System: $(uname -a)
Laravel Version: $(php artisan --version 2>/dev/null || echo "Unknown")
Database: ${DB_DATABASE}
Backup Size: $(du -sh "${TEMP_BACKUP_DIR}" | cut -f1)

Contents:
- database.sql: Complete database dump
- code/: Application source code
- uploads/: User uploaded files
- env_backup.txt: Environment configuration (sensitive data removed)
- backup_info.txt: This file

Restore Instructions:
1. Extract this backup to a temporary location
2. Restore database: mysql -u[username] -p [database] < database.sql
3. Copy code to your Laravel project directory
4. Copy uploads to storage/app/public/
5. Update .env file with your configuration
6. Run: composer install && php artisan migrate
EOF

# 6. Create compressed archive
echo "Creating compressed archive..."
cd /tmp
tar -czf "${BACKUP_DIR}/${BACKUP_NAME}.tar.gz" "${BACKUP_NAME}"

if [ $? -eq 0 ]; then
    echo "✓ Compressed archive created: ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"
else
    echo "✗ Archive creation failed"
    exit 1
fi

# 7. Clean up temporary files
echo "Cleaning up temporary files..."
rm -rf "${TEMP_BACKUP_DIR}"

# 8. Set proper permissions
chmod 600 "${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"

# 9. Show backup summary
BACKUP_SIZE=$(du -sh "${BACKUP_DIR}/${BACKUP_NAME}.tar.gz" | cut -f1)
echo ""
echo "=== Backup Summary ==="
echo "Backup completed successfully!"
echo "Location: ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"
echo "Size: ${BACKUP_SIZE}"
echo "Date: $(date)"
echo "====================="

# 10. Optional: Keep only last 10 backups
echo "Cleaning old backups (keeping last 10)..."
cd "${BACKUP_DIR}"
ls -t ${PROJECT_NAME}_backup_*.tar.gz | tail -n +11 | xargs -r rm -f

echo "Backup process completed!"
