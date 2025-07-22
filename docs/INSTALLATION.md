# دليل التثبيت | Installation Guide

## متطلبات النظام | System Requirements

### الحد الأدنى | Minimum Requirements
- PHP 8.2+
- Node.js 18+
- MySQL 8.0+ أو PostgreSQL 13+
- Composer 2.0+
- NPM أو Yarn

### الإضافات المطلوبة | Required Extensions
```bash
# PHP Extensions
php-mbstring
php-xml
php-bcmath
php-json
php-tokenizer
php-fileinfo
php-pdo
php-mysql (أو php-pgsql)
```

---

## التثبيت السريع | Quick Installation

### 1. استنساخ المشروع | Clone Project
```bash
git clone <repository-url> webvue-core
cd webvue-core
```

### 2. تثبيت التبعيات | Install Dependencies
```bash
# تبعيات PHP
composer install

# تبعيات JavaScript
npm install
```

### 3. إعداد البيئة | Environment Setup
```bash
# نسخ ملف البيئة
cp .env.example .env

# توليد مفتاح التطبيق
php artisan key:generate
```

### 4. تكوين قاعدة البيانات | Database Configuration
```env
# تحرير ملف .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=modular_admin_dashboard
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. إعداد قاعدة البيانات | Database Setup
```bash
# إنشاء قاعدة البيانات
mysql -u root -p -e "CREATE DATABASE modular_admin_dashboard;"

# تشغيل الهجرات
php artisan migrate

# تشغيل البذور
php artisan db:seed
```

### 6. بناء الأصول | Build Assets
```bash
# للتطوير
npm run dev

# للإنتاج
npm run build
```

### 7. تشغيل الخادم | Start Server
```bash
php artisan serve
```

---

## التثبيت باستخدام Docker

### 1. بناء الحاويات | Build Containers
```bash
docker-compose up -d --build
```

### 2. تثبيت التبعيات داخل الحاوية | Install Dependencies in Container
```bash
# دخول حاوية التطبيق
docker-compose exec app bash

# تثبيت التبعيات
composer install
npm install
```

### 3. إعداد قاعدة البيانات | Database Setup
```bash
# داخل الحاوية
php artisan migrate
php artisan db:seed
```

### 4. الوصول للتطبيق | Access Application
- التطبيق: http://localhost:8080
- قاعدة البيانات: localhost:3306
- Redis: localhost:6379

---

## التكوين المتقدم | Advanced Configuration

### إعدادات الوحدات | Module Settings
```env
# في ملف .env
MODULES_AUTO_REGISTER=true
MODULES_CACHE_ENABLED=true
MODULES_SAFE_MODE=true
```

### إعدادات الأمان | Security Settings
```env
# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1

# Activity Log
ACTIVITY_LOGGER_ENABLED=true
```

### إعدادات اللغة | Language Settings
```env
APP_LOCALE=en
APP_FALLBACK_LOCALE=ar
```

---

## التحقق من التثبيت | Installation Verification

### 1. فحص النظام | System Check
```bash
# فحص متطلبات PHP
php artisan about

# فحص قاعدة البيانات
php artisan migrate:status

# فحص الوحدات
php artisan module:list
```

### 2. تشغيل الاختبارات | Run Tests
```bash
# تشغيل جميع الاختبارات
php artisan test

# اختبارات محددة
php artisan test --filter=UserTest
```

### 3. فحص الأصول | Check Assets
```bash
# التحقق من بناء الأصول
npm run build

# فحص ملفات CSS/JS
ls -la public/build/
```

---

## استكشاف الأخطاء | Troubleshooting

### مشاكل شائعة | Common Issues

#### خطأ في الصلاحيات | Permission Errors
```bash
# إصلاح صلاحيات المجلدات
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### مشاكل Composer
```bash
# مسح ذاكرة Composer المؤقتة
composer clear-cache

# إعادة تثبيت التبعيات
rm -rf vendor
composer install
```

#### مشاكل NPM
```bash
# مسح node_modules
rm -rf node_modules package-lock.json

# إعادة التثبيت
npm install
```

#### مشاكل قاعدة البيانات
```bash
# إعادة تعيين قاعدة البيانات
php artisan migrate:fresh --seed

# فحص الاتصال
php artisan tinker
>>> DB::connection()->getPdo();
```

### السجلات | Logs
```bash
# عرض السجلات
tail -f storage/logs/laravel.log

# مسح السجلات
php artisan log:clear
```

---

## الأداء والتحسين | Performance & Optimization

### تحسين للإنتاج | Production Optimization
```bash
# تخزين التكوين مؤقتاً
php artisan config:cache

# تخزين المسارات مؤقتاً
php artisan route:cache

# تخزين العروض مؤقتاً
php artisan view:cache

# تحسين التحميل التلقائي
composer dump-autoload --optimize
```

### إعدادات الخادم | Server Configuration

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/public;

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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Apache
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

---

## النسخ الاحتياطي | Backup

### نسخ احتياطي لقاعدة البيانات | Database Backup
```bash
# إنشاء نسخة احتياطية
mysqldump -u root -p modular_admin_dashboard > backup.sql

# استعادة النسخة الاحتياطية
mysql -u root -p modular_admin_dashboard < backup.sql
```

### نسخ احتياطي للملفات | File Backup
```bash
# نسخ احتياطي للمشروع
tar -czf backup-$(date +%Y%m%d).tar.gz \
  --exclude=node_modules \
  --exclude=vendor \
  --exclude=storage/logs \
  .
```

---

## التحديث | Updates

### تحديث التبعيات | Update Dependencies
```bash
# تحديث Composer
composer update

# تحديث NPM
npm update

# فحص التبعيات القديمة
composer outdated
npm outdated
```

### تحديث النظام | System Updates
```bash
# تشغيل الهجرات الجديدة
php artisan migrate

# مسح ذاكرة التخزين المؤقت
php artisan optimize:clear

# إعادة بناء الأصول
npm run build
```

---

## الدعم | Support

### الموارد المفيدة | Helpful Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js Documentation](https://inertiajs.com)
- [React Documentation](https://react.dev)
- [Tailwind CSS Documentation](https://tailwindcss.com)

### الأوامر المفيدة | Useful Commands
```bash
# عرض معلومات النظام
php artisan about

# عرض المسارات
php artisan route:list

# عرض الوحدات
php artisan module:list

# فحص الصحة
php artisan health:check
```

---

**تم إكمال التثبيت بنجاح! 🎉**

يمكنك الآن الوصول إلى النظام باستخدام بيانات الدخول الافتراضية المذكورة في الوثائق الرئيسية.
