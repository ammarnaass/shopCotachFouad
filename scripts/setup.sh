#!/bin/bash
# Setup Script for ShopCotachFouad
# This script runs to install PHP extensions

set -e

echo "========================================="
echo "  ShopCotachFouad - Setup"
echo "========================================="

# Install PHP extensions
echo "[1/3] Installing PHP extensions..."
docker exec shopcotachfouad-app bash -c "
    apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip xml intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
"

# Install Composer
echo "[2/3] Installing Composer..."
docker exec shopcotachfouad-app bash -c "
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
"

# Install dependencies
echo "[3/3] Installing PHP dependencies..."
docker exec shopcotachfouad-app bash -c "
    cd /var/www/html && composer install --no-dev --prefer-dist
"

echo ""
echo "========================================="
echo "  ✓ Setup complete!"
echo "========================================="
