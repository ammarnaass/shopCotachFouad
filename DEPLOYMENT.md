# دليل النشر على الاستضافة — Deployment Guide

## 1. الاستضافة المشتركة (Shared Hosting — cPanel)

### متطلبات cPanel
- PHP 8.3+ مع الإضافات اللازمة
- MySQL 8.0+
- SSH Access (مُفضَّل)

### الخطوات

#### أ) تحضير الملفات محلياً
```bash
# تأكد أن الـ .env في وضع Production
APP_ENV=production
APP_DEBUG=false

# بنِ الأصول
npm run build

# حذف الملفات غير الضرورية للإنتاج
composer install --no-dev --optimize-autoloader
```

#### ب) رفع الملفات
ارفع ملفات المشروع باستخدام FTP بهذا الهيكل:

```
public_html/           ← محتويات مجلد public/ فقط
├── index.php         ← عدّل مسارات vendor وbootstrap
├── .htaccess
├── assets/
└── storage/          ← symlink

shopCotach/            ← باقي المشروع (خارج public_html)
├── app/
├── bootstrap/
├── config/
├── database/
├── routes/
├── storage/          ← المجلد الحقيقي
└── vendor/
```

#### ج) تعديل public/index.php
```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance mode check
if (file_exists($maintenance = __DIR__.'/../shopCotach/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../shopCotach/vendor/autoload.php';
$app = require_once __DIR__.'/../shopCotach/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

#### د) إنشاء قاعدة البيانات في cPanel
1. **cPanel → MySQL Databases**
2. Create Database: `youraccount_shop`
3. Create User: `youraccount_shopuser` + كلمة مرور
4. Add User To Database: ALL PRIVILEGES

#### هـ) استيراد قاعدة البيانات
```bash
# تصدير من المحلي
mysqldump -u root -p shopcotachfouad > backup.sql

# رفع backup.sql للخادم ثم استيراده:
# cPanel → phpMyAdmin → Import → اختر backup.sql
```

#### و) إعداد .env في cPanel
عبر File Manager أو SSH:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_HOST=localhost
DB_DATABASE=youraccount_shop
DB_USERNAME=youraccount_shopuser
DB_PASSWORD=YourPassword
CACHE_STORE=file
SESSION_DRIVER=file
```

#### ز) التهيئة النهائية عبر SSH
```bash
cd ~/shopCotach
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

---

## 2. VPS / Cloud Server (Ubuntu)

### إعداد الخادم من الصفر

```bash
# 1. تحديث النظام
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git unzip

# 2. تثبيت PHP 8.3
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-cli \
  php8.3-mysql php8.3-mbstring php8.3-xml php8.3-zip \
  php8.3-gd php8.3-curl php8.3-redis php8.3-bcmath \
  php8.3-intl php8.3-tokenizer

# 3. تثبيت MySQL 8
sudo apt install -y mysql-server
sudo mysql_secure_installation

# 4. تثبيت Nginx
sudo apt install -y nginx
sudo systemctl enable nginx

# 5. تثبيت Redis (اختياري لكن مُوصى به)
sudo apt install -y redis-server
sudo systemctl enable redis-server

# 6. تثبيت Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 7. تثبيت Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### نشر المشروع

```bash
# إنشاء مجلد
sudo mkdir -p /var/www/shopCotach
sudo chown $USER:$USER /var/www/shopCotach

# استنساخ من GitHub
git clone https://github.com/YOUR_USERNAME/shopCotachFouad.git /var/www/shopCotach
cd /var/www/shopCotach

# تثبيت الاعتماديات
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# الصلاحيات
sudo chown -R www-data:www-data /var/www/shopCotach
sudo chmod -R 755 /var/www/shopCotach
sudo chmod -R 775 storage bootstrap/cache

# البيئة
cp .env.example .env
# عدّل .env بالقيم الصحيحة
nano .env

php artisan key:generate
php artisan migrate --force
php artisan db:seed --force    # إذا أردت بيانات تجريبية
php artisan storage:link

# التحسينات
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### إعداد Nginx

أنشئ `/etc/nginx/sites-available/shopCotach`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/shopCotach/public;
    index index.php;
    charset utf-8;

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* { deny all; }

    # Static files cache
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    client_max_body_size 50M;
    error_log /var/log/nginx/shopCotach_error.log;
    access_log /var/log/nginx/shopCotach_access.log;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/shopCotach /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### SSL مجاني

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
# تجديد تلقائي
sudo crontab -e
# أضف:
0 12 * * * certbot renew --quiet
```

### إعداد Queue Worker

```bash
sudo apt install -y supervisor

sudo tee /etc/supervisor/conf.d/shopCotach.conf << 'EOF'
[program:shopCotach-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/shopCotach/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/shopCotach-worker.log
EOF

sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start all
```

### إعداد Cron (Scheduler)

```bash
sudo crontab -u www-data -e
# أضف:
* * * * * cd /var/www/shopCotach && php artisan schedule:run >> /dev/null 2>&1
```

---

## 3. تحديث المشروع (بعد push)

```bash
cd /var/www/shopCotach

# 1. وضع في وضع الصيانة
php artisan down

# 2. سحب التحديثات
git pull origin main

# 3. تحديث الاعتماديات
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 4. تشغيل الـ Migrations
php artisan migrate --force

# 5. تحديث الـ Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. إعادة تشغيل Queue
php artisan queue:restart

# 7. رفع وضع الصيانة
php artisan up
```

---

## 4. النسخ الاحتياطي (Backup)

```bash
# نسخ قاعدة البيانات
mysqldump -u shopuser -p shopcotachfouad | gzip > /backup/db_$(date +%Y%m%d).sql.gz

# نسخ ملفات Storage
tar -czf /backup/storage_$(date +%Y%m%d).tar.gz /var/www/shopCotach/storage/app/public

# سكريبت نسخ تلقائي يومي (في crontab)
0 2 * * * /var/www/shopCotach/scripts/backup.sh
```

---

## 5. مراقبة الأداء

```bash
# فحص السجلات
tail -f /var/www/shopCotach/storage/logs/laravel.log

# فحص Nginx logs
sudo tail -f /var/log/nginx/shopCotach_error.log

# فحص Queue
sudo supervisorctl status

# مراقبة MySQL
sudo mysqladmin -u root -p status

# مراقبة Redis
redis-cli info
```
