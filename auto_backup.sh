#!/bin/bash

# Automated Backup Script for Laravel Customer Payment System
# This script is designed to be run via cron for scheduled backups

# Configuration
PROJECT_NAME="customer-payment-system"
PROJECT_DIR="/home/ksb/dev/customer-payment-system"
BACKUP_DIR="/home/ksb/backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="${PROJECT_NAME}_auto_backup_${DATE}"
LOG_FILE="/home/ksb/backups/backup.log"

# Create backup directory if it doesn't exist
mkdir -p "${BACKUP_DIR}"

# Log function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "${LOG_FILE}"
}

log "Starting automated backup of ${PROJECT_NAME}"

# Change to project directory
cd "${PROJECT_DIR}" || {
    log "ERROR: Could not change to project directory ${PROJECT_DIR}"
    exit 1
}

# Create temporary backup directory
TEMP_BACKUP_DIR="/tmp/${BACKUP_NAME}"
mkdir -p "${TEMP_BACKUP_DIR}"

# 1. Database Backup
log "Backing up database..."
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2 | xargs)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2 | xargs)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2 | xargs)
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2 | xargs)

if [ -n "$DB_PASSWORD" ]; then
    mysqldump -h"${DB_HOST}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" > "${TEMP_BACKUP_DIR}/database.sql" 2>/dev/null
else
    mysqldump -h"${DB_HOST}" -u"${DB_USERNAME}" "${DB_DATABASE}" > "${TEMP_BACKUP_DIR}/database.sql" 2>/dev/null
fi

if [ $? -eq 0 ]; then
    log "✓ Database backup completed"
else
    log "✗ Database backup failed"
    exit 1
fi

# 2. Code Backup
log "Backing up application code..."
rsync -aq --exclude='vendor/' \
         --exclude='node_modules/' \
         --exclude='storage/logs/*' \
         --exclude='storage/framework/cache/*' \
         --exclude='storage/framework/sessions/*' \
         --exclude='storage/framework/views/*' \
         --exclude='.git/' \
         --exclude='.env' \
         --exclude='*.log' \
         --exclude='backup.sh' \
         --exclude='restore.sh' \
         --exclude='auto_backup.sh' \
         . "${TEMP_BACKUP_DIR}/code/" 2>/dev/null

if [ $? -eq 0 ]; then
    log "✓ Code backup completed"
else
    log "✗ Code backup failed"
    exit 1
fi

# 3. Uploaded files backup
log "Backing up uploaded files..."
if [ -d "storage/app/public" ]; then
    rsync -aq storage/app/public/ "${TEMP_BACKUP_DIR}/uploads/" 2>/dev/null
    log "✓ Uploaded files backup completed"
else
    log "! No uploaded files directory found"
fi

# 4. Create backup info file
log "Creating backup information..."
cat > "${TEMP_BACKUP_DIR}/backup_info.txt" << EOF
Automated Backup Information
============================
Project: ${PROJECT_NAME}
Date: $(date)
Type: Automated Backup
Database: ${DB_DATABASE}
Backup Size: $(du -sh "${TEMP_BACKUP_DIR}" | cut -f1)

Contents:
- database.sql: Complete database dump
- code/: Application source code
- uploads/: User uploaded files
- backup_info.txt: This file

This backup was created automatically.
EOF

# 5. Create compressed archive
log "Creating compressed archive..."
cd /tmp
tar -czf "${BACKUP_DIR}/${BACKUP_NAME}.tar.gz" "${BACKUP_NAME}" 2>/dev/null

if [ $? -eq 0 ]; then
    log "✓ Compressed archive created: ${BACKUP_NAME}.tar.gz"
else
    log "✗ Archive creation failed"
    exit 1
fi

# 6. Clean up temporary files
log "Cleaning up temporary files..."
rm -rf "${TEMP_BACKUP_DIR}"

# 7. Set proper permissions
chmod 600 "${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"

# 8. Show backup summary
BACKUP_SIZE=$(du -sh "${BACKUP_DIR}/${BACKUP_NAME}.tar.gz" | cut -f1)
log "=== Backup Summary ==="
log "Backup completed successfully!"
log "Location: ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"
log "Size: ${BACKUP_SIZE}"

# 9. Keep only last 20 automated backups
log "Cleaning old automated backups (keeping last 20)..."
cd "${BACKUP_DIR}"
ls -t ${PROJECT_NAME}_auto_backup_*.tar.gz 2>/dev/null | tail -n +21 | xargs -r rm -f

# 10. Check available disk space
AVAILABLE_SPACE=$(df -h "${BACKUP_DIR}" | tail -1 | awk '{print $4}')
log "Available disk space: ${AVAILABLE_SPACE}"

# 11. Send notification if backup is too large (optional)
BACKUP_SIZE_KB=$(du -k "${BACKUP_DIR}/${BACKUP_NAME}.tar.gz" | cut -f1)
if [ "$BACKUP_SIZE_KB" -gt 1048576 ]; then  # 1GB
    log "WARNING: Backup size is large (${BACKUP_SIZE})"
fi

log "Automated backup process completed!"
log "----------------------------------------"
