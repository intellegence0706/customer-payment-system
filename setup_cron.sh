#!/bin/bash

# Setup Cron Jobs for Automated Backups
# This script helps you configure automated backups

echo "=== Laravel Customer Payment System - Cron Setup ==="
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo "❌ Please do not run this script as root"
    echo "Run as your regular user: ksb"
    exit 1
fi

echo "Setting up automated backup cron jobs..."
echo ""

# Create backup directory if it doesn't exist
mkdir -p /home/ksb/backups

# Make backup scripts executable
chmod +x /home/ksb/dev/customer-payment-system/auto_backup.sh

echo "Available cron job options:"
echo "1. Daily backup at 2:00 AM"
echo "2. Weekly backup on Sunday at 3:00 AM"
echo "3. Monthly backup on 1st day at 4:00 AM"
echo "4. Custom schedule"
echo "5. View current cron jobs"
echo "6. Remove all backup cron jobs"
echo ""

read -p "Select an option (1-6): " choice

case $choice in
    1)
        echo "Adding daily backup at 2:00 AM..."
        (crontab -l 2>/dev/null; echo "0 2 * * * /home/ksb/dev/customer-payment-system/auto_backup.sh") | crontab -
        echo "✓ Daily backup scheduled"
        ;;
    2)
        echo "Adding weekly backup on Sunday at 3:00 AM..."
        (crontab -l 2>/dev/null; echo "0 3 * * 0 /home/ksb/dev/customer-payment-system/auto_backup.sh") | crontab -
        echo "✓ Weekly backup scheduled"
        ;;
    3)
        echo "Adding monthly backup on 1st day at 4:00 AM..."
        (crontab -l 2>/dev/null; echo "0 4 1 * * /home/ksb/dev/customer-payment-system/auto_backup.sh") | crontab -
        echo "✓ Monthly backup scheduled"
        ;;
    4)
        echo "Enter custom cron schedule (e.g., '0 1 * * *' for daily at 1:00 AM):"
        read -p "Cron schedule: " cron_schedule
        if [[ $cron_schedule =~ ^[0-9*\/\-\s]+$ ]]; then
            (crontab -l 2>/dev/null; echo "$cron_schedule /home/ksb/dev/customer-payment-system/auto_backup.sh") | crontab -
            echo "✓ Custom backup scheduled: $cron_schedule"
        else
            echo "❌ Invalid cron schedule format"
            exit 1
        fi
        ;;
    5)
        echo "Current cron jobs:"
        crontab -l 2>/dev/null | grep -E "(auto_backup|backup)" || echo "No backup cron jobs found"
        ;;
    6)
        echo "Removing all backup cron jobs..."
        crontab -l 2>/dev/null | grep -v "auto_backup" | crontab -
        echo "✓ All backup cron jobs removed"
        ;;
    *)
        echo "❌ Invalid option"
        exit 1
        ;;
esac

echo ""
echo "=== Cron Setup Complete ==="
echo ""
echo "To view your cron jobs: crontab -l"
echo "To edit cron jobs manually: crontab -e"
echo ""
echo "Backup logs will be saved to: /home/ksb/backups/backup.log"
echo "Backup files will be saved to: /home/ksb/backups/"
echo ""
echo "To test the backup system manually:"
echo "  cd /home/ksb/dev/customer-payment-system"
echo "  ./auto_backup.sh"
echo ""
echo "To view backup logs:"
echo "  tail -f /home/ksb/backups/backup.log"
