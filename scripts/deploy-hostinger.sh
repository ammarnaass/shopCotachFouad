#!/bin/bash
# Deploy Script for Hostinger / Shared Hosting
# ShopCotachFouad

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  ShopCotachFouad - Hostinger Deploy${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""

# Configuration
PROJECT_NAME="shopcotachfouad"
DEPLOY_DIR="deploy"
ZIP_FILE="${PROJECT_NAME}-$(date +%Y%m%d-%H%M%S).zip"

# Create deploy directory
echo -e "${YELLOW}[1/6] Creating deploy directory...${NC}"
rm -rf $DEPLOY_DIR
mkdir -p $DEPLOY_DIR

# Copy files
echo -e "${YELLOW}[2/6] Copying files...${NC}"

# Copy application files
cp -r app $DEPLOY_DIR/
cp -r bootstrap $DEPLOY_DIR/
cp -r config $DEPLOY_DIR/
cp -r database $DEPLOY_DIR/
cp -r public $DEPLOY_DIR/
cp -r resources $DEPLOY_DIR/
cp -r routes $DEPLOY_DIR/
cp -r storage $DEPLOY_DIR/

# Copy root files
cp artisan $DEPLOY_DIR/
cp composer.json $DEPLOY_DIR/
cp composer.lock $DEPLOY_DIR/

# Create .env file
echo -e "${YELLOW}[3/6] Creating .env file...${NC}"
cat > $DEPLOY_DIR/.env << 'EOF'
APP_NAME="Amar Store"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

APP_LOCALE=ar
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
QUEUE_REDIS_CONNECTION=default

CACHE_STORE=database
CACHE_PREFIX=amar_store

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_DB=3

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"

STORE_CURRENCY="DZD"
STORE_CURRENCY_SYMBOL="د.ج"
STORE_DEFAULT_COUNTRY=DZ
EOF

# Create .htaccess for Apache
echo -e "${YELLOW}[4/6] Creating .htaccess file...${NC}"
cat > $DEPLOY_DIR/public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

<IfModule mod_headers>
    <FilesMatch "\.(css|js|ico|gif|jpe?g|png|svg|webp|woff2?)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>

    <FilesMatch "\.(php)$">
        Header set X-Content-Type-Options "nosniff"
        Header set X-Frame-Options "SAMEORIGIN"
        Header set X-XSS-Protection "1; mode=block"
    </FilesMatch>
</IfModule>

<IfModule mod_env.c>
    SetEnv APP_ENV production
    SetEnv APP_DEBUG false
</IfModule>

<IfModule mod_php.c>
    php_value upload_max_filesize 64M
    php_value post_max_size 64M
    php_value max_execution_time 300
    php_value max_input_time 300
    php_value memory_limit 256M
</IfModule>

# Deny access to sensitive files
<FilesMatch "^(\.env|\.git|\.htaccess|composer\.(json|lock))$">
    Require all denied
</FilesMatch>

# Deny access to storage directory
<Directory "storage">
    Require all denied
</Directory>
EOF

# Create nginx.conf for reference
echo -e "${YELLOW}[5/6] Creating nginx.conf for reference...${NC}"
cp docker/nginx/hostinger.conf $DEPLOY_DIR/nginx.conf.example

# Create README for deployment
echo -e "${YELLOW}[6/6] Creating README...${NC}"
cat > $DEPLOY_DIR/README.md << 'EOF'
# ShopCotachFouad - Deployment Guide

## For Hostinger Shared Hosting

### 1. Upload Files
- Upload all files from this directory to your hosting via File Manager or FTP
- Make sure to upload to the `public_html` directory

### 2. Configure Database
- Create a MySQL database in Hostinger control panel
- Update `.env` file with your database credentials:
  ```
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=your_database
  DB_USERNAME=your_username
  DB_PASSWORD=your_password
  ```

### 3. Set Application URL
- Update `APP_URL` in `.env` file with your domain:
  ```
  APP_URL=https://your-domain.com
  ```

### 4. Run Migrations
- SSH into your hosting or use Terminal in Hostinger control panel
- Run:
  ```bash
  php artisan migrate --force
  php artisan db:seed --force
  ```

### 5. Storage Link
- Run:
  ```bash
  php artisan storage:link
  ```

### 6. Permissions
- Make sure `storage` and `bootstrap/cache` directories are writable (775)

### 7. Optimization
- Run:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

## Troubleshooting

### 500 Internal Server Error
- Check `.env` file exists and is configured correctly
- Check `storage` directory permissions
- Check PHP version (requires 8.3+)

### Files Not Found
- Ensure `.htaccess` is in the `public` directory
- Check if mod_rewrite is enabled

### Database Connection Issues
- Verify database credentials in `.env`
- Ensure database exists and user has permissions

## Support
- Check Hostinger documentation for PHP configuration
- Contact Hostinger support for server-specific issues
EOF

# Create ZIP file
echo -e "${YELLOW}Creating ZIP file...${NC}"
cd $DEPLOY_DIR
zip -r ../$ZIP_FILE . -x "*.git*"
cd ..

echo ""
echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  ✓ Deployment package created!${NC}"
echo -e "${GREEN}  File: ${ZIP_FILE}${NC}"
echo -e "${GREEN}  Size: $(du -h $ZIP_FILE | cut -f1)${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo -e "${YELLOW}Instructions:${NC}"
echo "1. Upload ${ZIP_FILE} to your Hostinger hosting"
echo "2. Extract files in public_html directory"
echo "3. Update .env file with your database credentials"
echo "4. Run php artisan migrate --force"
echo "5. Run php artisan storage:link"
echo ""
