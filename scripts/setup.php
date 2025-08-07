<?php

// Laravel Customer Management System Setup Script

echo "Setting up Laravel Customer Management System...\n";

// Run migrations
echo "Running database migrations...\n";
exec('php artisan migrate:fresh --seed');

// Create storage directories
echo "Creating storage directories...\n";
exec('php artisan storage:link');

// Create required directories
$directories = [
    'storage/app/monthly-data',
    'storage/app/monthly-data/archive',
    'storage/app/reports',
    'storage/app/reports/monthly',
    'storage/app/uploads',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "Created directory: {$dir}\n";
    }
}

// Clear and cache config
echo "Optimizing application...\n";
exec('php artisan config:cache');
exec('php artisan route:cache');
exec('php artisan view:cache');

echo "Setup completed successfully!\n";
echo "Default login credentials:\n";
echo "Admin: admin@example.com / password\n";
echo "Manager: manager@example.com / password\n";
echo "User: user@example.com / password\n";
