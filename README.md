# ShopCotach — متجر إلكتروني متكامل (Laravel 13)

> **Laravel 13 · PHP 8.3 · MySQL 8 · Redis · Modular Architecture**

متجر إلكتروني احترافي مبني بمعمارية الموديولات (Modular Architecture) مع دعم متعدد اللغات والعملات.

---

## 📋 جدول المحتويات

- [متطلبات النظام](#متطلبات-النظام)
- [بنية الموديولات](#بنية-الموديولات)
- [التثبيت المحلي](#التثبيت-المحلي)
- [إعداد قاعدة البيانات](#إعداد-قاعدة-البيانات)
- [الرفع على الاستضافة المشتركة](#الرفع-على-الاستضافة-المشتركة)
- [الرفع على VPS/Cloud](#الرفع-على-vpscloud)
- [المتغيرات البيئية المهمة](#المتغيرات-البيئية-المهمة)
- [الحسابات التجريبية](#الحسابات-التجريبية)
- [واجهة برمجية API](#واجهة-برمجية-api)

---

## متطلبات النظام

| المكوّن | الإصدار المطلوب |
|---------|----------------|
| PHP | 8.3+ |
| Laravel | 13.x |
| MySQL | 8.0+ |
| Redis | 6.0+ (اختياري لكن مُوصى به) |
| Composer | 2.x |
| Node.js | 18+ |
| NPM | 9+ |

**امتدادات PHP المطلوبة:** `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd`, `zip`, `intl`

---

## بنية الموديولات

```
app/
├── Modules/
│   ├── InstantBuy/          # الشراء الفوري (express checkout)
│   ├── Coupons/             # نظام كوبونات الخصم
│   ├── Wishlist/            # قائمة الأمنيات
│   ├── Reviews/             # تقييمات ومراجعات المنتجات
│   ├── Shipping/            # نظام الشحن المتقدم (شركات، مناطق، طرق)
│   ├── Cart/                # سلة التسوق
│   ├── Checkout/            # إتمام الطلب
│   ├── Orders/              # إدارة الطلبات
│   ├── Payments/            # المدفوعات والإشعارات
│   ├── Catalog/             # المنتجات والأقسام والعلامات
│   ├── Users/               # المستخدمون والصلاحيات
│   ├── Dashboard/           # لوحة الإدارة والتقارير
│   └── CMS/                 # الصفحات، السلايدر، الفوتر، الإعدادات
├── Models/                  # Backward-compatible wrappers
├── Http/Controllers/        # Legacy controllers (يرث من الموديولات)
├── Services/                # Legacy services wrappers
└── Providers/
    └── ModulesServiceProvider.php  # تحميل الموديولات تلقائياً
```

كل موديول يحتوي على:
```
Modules/OrderName/
├── Http/
│   └── Controllers/
│       ├── Admin/
│       └── Api/
├── Models/
├── Services/
└── routes/ (اختياري — مسارات مستقلة)
```

---

## التثبيت المحلي

### الخطوة 1 — استنساخ المشروع

```bash
git clone https://github.com/YOUR_USERNAME/shopCotachFouad.git
cd shopCotachFouad
```

### الخطوة 2 — تثبيت اعتماديات PHP

```bash
composer install
```

### الخطوة 3 — تثبيت اعتماديات Node.js

```bash
npm install
```

### الخطوة 4 — إعداد ملف البيئة

```bash
cp .env.example .env
php artisan key:generate
```

ثم عدّل `.env`:

```env
APP_NAME="ShopCotach"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopcotachfouad
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=redis        # أو file إذا لم يكن Redis متاحاً
SESSION_DRIVER=database
QUEUE_CONNECTION=database

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# إعدادات المتجر
STORE_CURRENCY=DZD
STORE_CURRENCY_SYMBOL=دج
STORE_DEFAULT_COUNTRY=DZ
```

### الخطوة 5 — إنشاء قاعدة البيانات وتشغيل الـ Migrations

```bash
# إنشاء قاعدة البيانات (MySQL)
mysql -u root -p -e "CREATE DATABASE shopcotachfouad CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# تشغيل الـ Migrations
php artisan migrate

# تشغيل الـ Seeders (البيانات التجريبية)
php artisan db:seed
```

### الخطوة 6 — إعداد Storage

```bash
php artisan storage:link
```

### الخطوة 7 — بناء الأصول (CSS/JS)

```bash
# للتطوير
npm run dev

# للإنتاج
npm run build
```

### الخطوة 8 — تشغيل الخادم المحلي

```bash
php artisan serve
```

افتح المتصفح على:
- **المتجر:** http://localhost:8000
- **الإدارة:** http://localhost:8000/admin

---

## إعداد قاعدة البيانات

### إنشاء قاعدة البيانات من الصفر

```sql
-- اتصل بـ MySQL
mysql -u root -p

-- أنشئ قاعدة البيانات
CREATE DATABASE shopcotachfouad
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- إنشاء مستخدم مخصص (اختياري لكن مُوصى به)
CREATE USER 'shopuser'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON shopcotachfouad.* TO 'shopuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### تشغيل الـ Migrations

```bash
# تشغيل كل الـ Migrations
php artisan migrate

# إذا احتجت إعادة ضبط كاملة (احذر: يحذف كل البيانات)
php artisan migrate:fresh --seed
```

### قائمة الجداول الأساسية

| الجدول | الموديول | الوصف |
|--------|---------|-------|
| `users` | Users | المستخدمون |
| `roles`, `permissions` | Users | الصلاحيات |
| `products` | Catalog | المنتجات |
| `categories` | Catalog | الأقسام |
| `tags`, `product_tag` | Catalog | العلامات |
| `product_images` | Catalog | صور المنتجات |
| `product_variants` | Catalog | متغيرات المنتج |
| `orders`, `order_items` | Orders | الطلبات |
| `order_notes` | Orders | ملاحظات الطلبات |
| `order_status_history` | Orders | سجل الحالات |
| `carts`, `cart_items` | Cart | سلة التسوق |
| `wishlists` | Wishlist | قائمة الأمنيات |
| `coupons` | Coupons | كوبونات الخصم |
| `shipping_companies` | Shipping | شركات الشحن |
| `shipping_zones` | Shipping | مناطق الشحن |
| `shipping_methods` | Shipping | طرق الشحن |
| `shipping_addresses` | Shipping | عناوين التوصيل |
| `shipping_labels` | Shipping | بوالص الشحن |
| `payments`, `payment_methods` | Payments | المدفوعات |
| `reviews` | Reviews | التقييمات |
| `slides` | CMS | السلايدر |
| `pages` | CMS | الصفحات الثابتة |
| `settings` | CMS | إعدادات المتجر |
| `languages`, `translations` | CMS | اللغات والترجمات |
| `footer_sections`, `footer_links`, `footer_socials` | CMS | الفوتر |
| `invoice_templates`, `invoices` | Documents | الفواتير |
| `label_templates` | Documents | بوالص الشحن |
| `instant_buy_settings`, `instant_buy_orders` | InstantBuy | الشراء الفوري |

---

## الرفع على الاستضافة المشتركة

### 1. رفع الملفات

رفع ملفات المشروع عبر FTP أو cPanel File Manager:

**هام:** يجب أن يكون مجلد `public/` هو جذر الاستضافة (`public_html`).

```
# الهيكل الصحيح على الاستضافة
/home/youraccount/
├── public_html/         ← مجلد public/ من المشروع
│   ├── index.php
│   ├── .htaccess
│   └── ...
└── shopCotach/          ← باقي ملفات المشروع (خارج public_html)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── routes/
    ├── storage/
    └── vendor/
```

ثم عدّل `public_html/index.php` لتوجيه المسار الصحيح:

```php
// public/index.php — عدّل السطور التالية:
require __DIR__.'/../shopCotach/vendor/autoload.php';
$app = require_once __DIR__.'/../shopCotach/bootstrap/app.php';
```

### 2. إنشاء قاعدة البيانات عبر cPanel

1. اذهب إلى **cPanel → MySQL Databases**
2. أنشئ قاعدة بيانات جديدة: `youraccount_shopcotach`
3. أنشئ مستخدم MySQL وكلمة مرور قوية
4. أضف المستخدم إلى قاعدة البيانات بصلاحيات **ALL PRIVILEGES**

### 3. استيراد قاعدة البيانات

```bash
# صدّر قاعدة البيانات المحلية
mysqldump -u root -p shopcotachfouad > shopcotachfouad_export.sql

# ارفع الملف عبر FTP إلى الخادم
# ثم استورده عبر phpMyAdmin أو:
mysql -u youraccount_user -p youraccount_shopcotach < shopcotachfouad_export.sql
```

### 4. إعداد ملف .env على الاستضافة

```env
APP_NAME="ShopCotach"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=youraccount_shopcotach
DB_USERNAME=youraccount_user
DB_PASSWORD=YourStrongPassword

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

### 5. تهيئة Laravel على الاستضافة

عبر SSH أو terminal الـ cPanel:

```bash
cd /home/youraccount/shopCotach

# تثبيت Composer
composer install --no-dev --optimize-autoloader

# توليد مفتاح التطبيق
php artisan key:generate

# تشغيل الـ Migrations
php artisan migrate --force

# تحسين الأداء
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ربط Storage
php artisan storage:link
```

### 6. إعداد ملف .htaccess

تأكد من وجود الملف التالي في `public_html/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

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
```

---

## الرفع على VPS/Cloud

### إعداد الخادم (Ubuntu 22.04/24.04)

```bash
# تحديث النظام
sudo apt update && sudo apt upgrade -y

# تثبيت PHP 8.3
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-zip php8.3-gd php8.3-curl php8.3-redis \
    php8.3-bcmath php8.3-intl php8.3-json -y

# تثبيت MySQL 8
sudo apt install mysql-server -y
sudo mysql_secure_installation

# تثبيت Nginx
sudo apt install nginx -y

# تثبيت Redis
sudo apt install redis-server -y
sudo systemctl enable redis-server

# تثبيت Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# تثبيت Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y
```

### إعداد MySQL على VPS

```bash
sudo mysql -u root -p

# داخل MySQL
CREATE DATABASE shopcotachfouad CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shopuser'@'localhost' IDENTIFIED BY 'SuperStrongPass!2024';
GRANT ALL PRIVILEGES ON shopcotachfouad.* TO 'shopuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### نشر المشروع

```bash
# إنشاء مجلد المشروع
sudo mkdir -p /var/www/shopCotach
sudo chown -R $USER:$USER /var/www/shopCotach

# استنساخ المشروع
git clone https://github.com/YOUR_USERNAME/shopCotachFouad.git /var/www/shopCotach

cd /var/www/shopCotach

# تثبيت الاعتماديات
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# إعداد الصلاحيات
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# إعداد البيئة
cp .env.example .env
nano .env   # عدّل القيم المطلوبة
php artisan key:generate

# تشغيل الـ Migrations والـ Seeders
php artisan migrate --force
php artisan db:seed --force

# ربط Storage
php artisan storage:link

# تحسين الإنتاج
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### إعداد Nginx

أنشئ ملف `/etc/nginx/sites-available/shopCotach`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/shopCotach/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 50M;
}
```

```bash
# تفعيل الموقع
sudo ln -s /etc/nginx/sites-available/shopCotach /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### إعداد SSL مجاني (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### إعداد Queue Worker (Supervisor)

```bash
sudo apt install supervisor -y

# أنشئ ملف الإعداد
sudo nano /etc/supervisor/conf.d/shopcotach-worker.conf
```

محتوى الملف:
```ini
[program:shopcotach-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/shopCotach/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/shopcotach-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### إعداد Scheduler (Cron)

```bash
sudo crontab -u www-data -e

# أضف السطر التالي:
* * * * * cd /var/www/shopCotach && php artisan schedule:run >> /dev/null 2>&1
```

---

## المتغيرات البيئية المهمة

```env
# ==========================================
# إعدادات التطبيق الأساسية
# ==========================================
APP_NAME="ShopCotach"
APP_ENV=production          # local | production
APP_DEBUG=false             # true فقط في التطوير!
APP_URL=https://yourdomain.com

# ==========================================
# قاعدة البيانات
# ==========================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1           # localhost في الاستضافة المشتركة
DB_PORT=3306
DB_DATABASE=shopcotachfouad
DB_USERNAME=shopuser
DB_PASSWORD=YourStrongPassword

# ==========================================
# Cache & Session
# ==========================================
CACHE_STORE=redis           # redis | file | database
SESSION_DRIVER=database     # database | file | redis
SESSION_LIFETIME=120

# ==========================================
# Redis (للـ Cache والـ Queue)
# ==========================================
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ==========================================
# Queue (معالجة الطوابير في الخلفية)
# ==========================================
QUEUE_CONNECTION=redis      # redis | database

# ==========================================
# إعدادات المتجر
# ==========================================
STORE_CURRENCY=DZD
STORE_CURRENCY_SYMBOL=دج
STORE_DEFAULT_COUNTRY=DZ

# ==========================================
# البريد الإلكتروني
# ==========================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=yourmail@gmail.com
MAIL_PASSWORD=yourapppassword
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="ShopCotach"
```

---

## الحسابات التجريبية

| الدور | البريد الإلكتروني | كلمة المرور |
|-------|-------------------|-------------|
| **Admin** | `admin@amarstore.com` | `password` |
| **Manager** | `manager@amarstore.com` | `password` |
| **Customer** | `customer@test.com` | `password` |

---

## واجهة برمجية API

الـ API تستخدم **Laravel Sanctum** للمصادقة.

### المصادقة

```bash
# تسجيل مستخدم جديد
POST /api/register
{
    "name": "Ahmed",
    "email": "ahmed@example.com",
    "phone": "+213555000000",
    "password": "password123"
}

# تسجيل الدخول
POST /api/login
{
    "email": "ahmed@example.com",
    "password": "password123"
}
# الاستجابة: { "token": "..." }

# تسجيل الخروج
POST /api/logout
Authorization: Bearer {token}
```

### نقاط النهاية الأساسية

```
# المنتجات
GET  /api/products              # قائمة المنتجات
GET  /api/products/{slug}       # تفاصيل منتج

# السلة
GET  /api/cart                  # محتوى السلة
POST /api/cart                  # إضافة للسلة

# الطلبات
GET  /api/orders                # طلباتي
POST /api/orders                # إنشاء طلب

# قائمة الأمنيات
GET  /api/wishlist              # قائمة الأمنيات
POST /api/wishlist              # إضافة منتج

# الشحن
GET  /api/shipping/companies    # شركات الشحن
```

---

## الأوامر المفيدة

```bash
# تحديث بعد pull من Git
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

# إعادة ضبط Cache الموقع
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# إنشاء حساب Admin يدوياً
php artisan create:admin

# فحص حالة الـ Queues
php artisan queue:monitor

# عرض السجلات
tail -f storage/logs/laravel.log
```

---

## هيكل الاستضافة الموصى به

```
المتطلبات الدنيا للإنتاج:
- RAM: 1 GB (2 GB مُوصى به)
- CPU: 1 vCore
- Storage: 20 GB SSD
- PHP: 8.3-FPM
- MySQL: 8.0
- Redis: 7.x (اختياري)
- Nginx أو Apache

مزودو الاستضافة الموصى بهم:
✅ DigitalOcean (Droplet $12/شهر)
✅ Hetzner Cloud (CX22 €5/شهر)  ← أفضل قيمة
✅ Vultr (Cloud Compute $12/شهر)
✅ Contabo (VPS S $7/شهر)
```

---

*آخر تحديث: 2026-08-19 — المعمارية الجديدة: Modular Architecture*
