#!/bin/bash

# Laravel Customer Payment System Restore Script
# This script restores your system from a backup

# Configuration
PROJECT_NAME="customer-payment-system"
BACKUP_DIR="/home/ksb/backups"

# Check if backup file is provided
if [ $# -eq 0 ]; then
    echo "Usage: $0 <backup_file.tar.gz>"
    echo "Available backups:"
    ls -la "${BACKUP_DIR}/${PROJECT_NAME}_backup_*.tar.gz" 2>/dev/null || echo "No backups found in ${BACKUP_DIR}"
    exit 1
fi

BACKUP_FILE="$1"

# Check if backup file exists
if [ ! -f "${BACKUP_FILE}" ]; then
    echo "Error: Backup file '${BACKUP_FILE}' not found!"
    exit 1
fi

# Check if backup file is in the expected format
if [[ ! "${BACKUP_FILE}" =~ ${PROJECT_NAME}_backup_.*\.tar\.gz$ ]]; then
    echo "Error: Backup file doesn't match expected format: ${PROJECT_NAME}_backup_YYYYMMDD_HHMMSS.tar.gz"
    exit 1
fi

echo "Starting restore from backup: ${BACKUP_FILE}"
echo "WARNING: This will overwrite your current system!"
echo "Make sure you have a backup of your current system before proceeding."
echo ""
read -p "Are you sure you want to continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Restore cancelled."
    exit 0
fi

# Create temporary restore directory
TEMP_RESTORE_DIR="/tmp/restore_${PROJECT_NAME}_$(date +%Y%m%d_%H%M%S)"
mkdir -p "${TEMP_RESTORE_DIR}"

echo "Extracting backup archive..."
cd "${TEMP_RESTORE_DIR}"
tar -xzf "${BACKUP_FILE}"

# Find the extracted directory
EXTRACTED_DIR=$(ls -d */ | head -1)
if [ -z "$EXTRACTED_DIR" ]; then
    echo "Error: Could not find extracted backup directory"
    exit 1
fi

cd "${EXTRACTED_DIR}"

echo "Restoring system from backup..."

# 1. Restore database
if [ -f "database.sql" ]; then
    echo "Restoring database..."
    
    # Get database credentials from current .env
    cd /home/ksb/dev/customer-payment-system
    DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2 | xargs)
    DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2 | xargs)
    DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2 | xargs)
    DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2 | xargs)
    
    if [ -n "$DB_PASSWORD" ]; then
        mysql -h"${DB_HOST}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" < "${TEMP_RESTORE_DIR}/${EXTRACTED_DIR}database.sql"
    else
        mysql -h"${DB_HOST}" -u"${DB_USERNAME}" "${DB_DATABASE}" < "${TEMP_RESTORE_DIR}/${EXTRACTED_DIR}database.sql"
    fi
    
    if [ $? -eq 0 ]; then
        echo "✓ Database restored successfully"
    else
        echo "✗ Database restore failed"
        exit 1
    fi
else
    echo "! No database backup found in archive"
fi

# 2. Restore code (with confirmation)
echo "Restoring application code..."
echo "This will overwrite your current code files."
read -p "Continue with code restore? (yes/no): " code_confirm

if [ "$code_confirm" = "yes" ]; then
    cd /home/ksb/dev/customer-payment-system
    
    # Create backup of current system before overwriting
    CURRENT_BACKUP="/tmp/current_system_backup_$(date +%Y%m%d_%H%M%S).tar.gz"
    echo "Creating backup of current system to: ${CURRENT_BACKUP}"
    tar -czf "${CURRENT_BACKUP}" --exclude='vendor/' --exclude='node_modules/' --exclude='storage/logs/*' --exclude='storage/framework/cache/*' --exclude='storage/framework/sessions/*' --exclude='storage/framework/views/*' --exclude='.git/' --exclude='.env' .
    
    # Restore code files
    rsync -av --delete "${TEMP_RESTORE_DIR}/${EXTRACTED_DIR}code/" ./
    
    if [ $? -eq 0 ]; then
        echo "✓ Code restored successfully"
        echo "Current system backup saved to: ${CURRENT_BACKUP}"
    else
        echo "✗ Code restore failed"
        exit 1
    fi
else
    echo "Code restore skipped"
fi

# 3. Restore uploaded files
if [ -d "${TEMP_RESTORE_DIR}/${EXTRACTED_DIR}uploads" ]; then
    echo "Restoring uploaded files..."
    cd /home/ksb/dev/customer-payment-system
    
    # Create backup of current uploads
    if [ -d "storage/app/public" ]; then
        UPLOADS_BACKUP="/tmp/current_uploads_backup_$(date +%Y%m%d_%H%M%S).tar.gz"
        echo "Creating backup of current uploads to: ${UPLOADS_BACKUP}"
        tar -czf "${UPLOADS_BACKUP}" -C storage/app public/
    fi
    
    # Restore uploads
    rsync -av --delete "${TEMP_RESTORE_DIR}/${EXTRACTED_DIR}uploads/" storage/app/public/
    
    if [ $? -eq 0 ]; then
        echo "✓ Uploaded files restored successfully"
        if [ -n "$UPLOADS_BACKUP" ]; then
            echo "Current uploads backup saved to: ${UPLOADS_BACKUP}"
        fi
    else
        echo "✗ Uploaded files restore failed"
    fi
else
    echo "! No uploaded files found in archive"
fi

# 4. Post-restore tasks
echo "Running post-restore tasks..."
cd /home/ksb/dev/customer-payment-system

# Clear Laravel caches
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run migrations to ensure database is up to date
echo "Running database migrations..."
php artisan migrate --force

# Set proper permissions
echo "Setting proper permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# 5. Cleanup
echo "Cleaning up temporary files..."
rm -rf "${TEMP_RESTORE_DIR}"

echo ""
echo "=== Restore Summary ==="
echo "Restore completed successfully!"
echo "Backup file used: ${BACKUP_FILE}"
echo "Date: $(date)"
echo ""
echo "Next steps:"
echo "1. Verify your application is working correctly"
echo "2. Check that all data has been restored"
echo "3. Test key functionality"
echo "4. If you have issues, you can restore from the current system backup"
echo "====================="
