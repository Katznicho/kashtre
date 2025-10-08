#!/bin/bash

# Fix Production File Upload Issues
# Run this script on your production server

echo "Fixing Laravel permissions for file uploads..."

# Set correct ownership
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache

# Set correct permissions
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache

# Create storage link
php artisan storage:link

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Permissions fixed!"
echo ""
echo "Now check PHP upload settings:"
php -i | grep -E 'upload_max_filesize|post_max_size'

