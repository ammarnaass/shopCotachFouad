#!/bin/bash
# Docker Start Script for ShopCotachFouad
# This script runs inside the Laravel container

set -e

echo "========================================="
echo "  ShopCotachFouad - Docker Start"
echo "========================================="

# Wait for MySQL
echo "[1/5] Waiting for MySQL to be ready..."
max_attempts=30
attempt=0
while [ $attempt -lt $max_attempts ]; do
    if php artisan db:show > /dev/null 2>&1; then
        echo "  ✓ MySQL is ready!"
        break
    fi
    attempt=$((attempt + 1))
    echo "  Attempt $attempt/$max_attempts - waiting..."
    sleep 2
done

if [ $attempt -eq $max_attempts ]; then
    echo "  ✗ Failed to connect to MySQL after $max_attempts attempts"
    exit 1
fi

# Run migrations
echo "[2/5] Running migrations..."
php artisan migrate --force

# Run seeders (only if users table is empty)
user_count=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null || echo "0")
if [ "$user_count" = "0" ]; then
    echo "[3/5] Running seeders (database is empty)..."
    php artisan db:seed --force
else
    echo "[3/5] Skipping seeders (database has $user_count users)"
fi

# Clear and cache config
echo "[4/5] Optimizing..."
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Create storage link
echo "[5/5] Final setup..."
php artisan storage:link --force 2>/dev/null || true

echo ""
echo "========================================="
echo "  ✓ Setup complete!"
echo "  App: http://localhost"
echo "========================================="

# Start PHP-FPM
exec php-fpm
