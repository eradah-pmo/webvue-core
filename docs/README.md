# نظام لوحة التحكم الإدارية المعيارية
## Modular Admin Dashboard System

### 📋 نظرة عامة | Overview

تم بناء نظام لوحة تحكم إدارية معيارية متكاملة باستخدام Laravel 11 + Inertia.js + React مع دعم كامل للغة العربية والإنجليزية ونظام صلاحيات متقدم.

A comprehensive modular admin dashboard system built with Laravel 11 + Inertia.js + React featuring full Arabic/English support and advanced RBAC system.

---

## 🏗️ المعمارية التقنية | Technical Architecture

### Backend (Laravel 11)
- **نظام معياري**: `app/Modules/*` مع التسجيل التلقائي
- **نظام RBAC متقدم**: صلاحيات أفقية + عمودية باستخدام spatie/laravel-permission
- **نظام تسجيل شامل**: Spatie Activitylog + audit_logs مخصص
- **قاعدة بيانات متكاملة**: جداول المستخدمين، الأدوار، الأقسام، الوحدات، سجلات التدقيق
- **معمارية الخدمات**: مع العقود والسمات المشتركة

### Frontend (React + Inertia.js)
- **معمارية SPA**: مع `DashboardLayout.jsx`
- **شريط جانبي متجاوب**: مع تسجيل الوحدات التلقائي
- **رأس صفحة حديث**: مع تبديل اللغة، الوضع المظلم، قائمة المستخدم
- **دعم i18n كامل**: إنجليزي/عربي مع تخطيط RTL
- **خط IBM Plex Sans**: مع تصميم حديث وخفيف
- **Tailwind CSS**: مع مكونات وأدوات مخصصة

---

## 🔐 نظام الصلاحيات RBAC

### الأدوار الأفقية | Horizontal Roles
```php
'super-admin' => 'مدير النظام الأعلى',    // Full system access
'admin'       => 'مدير النظام',         // Administrative access
'manager'     => 'مدير القسم',          // Department-level management
'user'        => 'مستخدم عادي',         // Basic dashboard access
```

### النطاقات العمودية | Vertical Scopes
```php
'department'    => 'مستوى القسم',       // Department Level
'business_unit' => 'مستوى وحدة العمل',  // Business Unit Level
'project'       => 'مستوى المشروع',     // Project Level
```

### استخدام الصلاحيات | Permission Usage
```php
// التحقق من الصلاحية الأساسية
$user->hasPermissionTo('users.view');

// التحقق من الصلاحية مع النطاق
$user->hasAccess('users.view', 'department', $departmentId);

// الحصول على النطاقات المتاحة
$accessibleDepartments = $user->getAccessibleDepartments();
```

---

## 🧱 نظام الوحدات المعيارية

### إنشاء وحدة جديدة | Creating New Module
```bash
php artisan make:module اسم_الوحدة
php artisan make:module ModuleName
```

### هيكل الوحدة | Module Structure
```
app/Modules/ModuleName/
├── Controllers/           # تحكم
├── Models/               # نماذج البيانات
├── Services/             # منطق الأعمال
├── Policies/             # سياسات الأمان
├── Requests/             # طلبات التحقق
├── Resources/            # موارد API
├── database/
│   ├── migrations/       # هجرات قاعدة البيانات
│   └── seeders/         # بذور البيانات
├── resources/
│   ├── views/           # عروض Blade
│   ├── lang/            # ملفات الترجمة
│   └── js/
│       ├── Components/  # مكونات React
│       └── Pages/       # صفحات Inertia
├── routes.php           # مسارات الوحدة
└── module.json          # تكوين الوحدة
```

### تكوين الوحدة | Module Configuration
```json
{
  "name": "Users",
  "display_name": "إدارة المستخدمين",
  "description": "إدارة مستخدمي النظام والملفات الشخصية",
  "version": "1.0.0",
  "active": true,
  "critical": true,
  "dependencies": [],
  "permissions": [
    "users.view",
    "users.create", 
    "users.edit",
    "users.delete"
  ],
  "navigation": {
    "name": "users",
    "href": "/users",
    "icon": "UsersIcon",
    "order": 10
  }
}
```

---

## 🌐 نظام التدويل i18n

### Backend (Laravel)
```php
// ملفات اللغة
resources/lang/ar/
resources/lang/en/

// حفظ تفضيل اللغة
$user->update(['locale' => 'ar']);
```

### Frontend (React)
```javascript
// استخدام الترجمة
const { t, i18n } = useTranslation();

// تغيير اللغة
i18n.changeLanguage('ar');

// الترجمة مع المساحات
t('common:navigation.dashboard')
t('users:create')
```

### دعم RTL
```css
/* تلقائي حسب اللغة */
.rtl { direction: rtl; }
.ltr { direction: ltr; }

/* مكونات مخصصة */
.glass { backdrop-filter: blur(10px); }
```

---

## 📊 لوحة التحكم الرئيسية

### المكونات | Components
- **بطاقات الإحصائيات**: المستخدمين، الأدوار، الأقسام، الوحدات
- **الأنشطة الأخيرة**: عرض سجل الأنشطة مع صور المستخدمين
- **الوحدات النشطة**: نظرة عامة على حالة الوحدات مع معلومات الإصدار
- **الإجراءات السريعة**: روابط مباشرة لإنشاء المستخدمين والأدوار والأقسام
- **تصميم متجاوب**: يعمل على جميع أحجام الشاشات

---

## 🛠️ أدوات التطوير

### أوامر CLI
```bash
# إنشاء وحدة جديدة
php artisan make:module اسم_الوحدة

# تفعيل/إلغاء تفعيل الوحدات
php artisan module:enable اسم_الوحدة
php artisan module:disable اسم_الوحدة

# مسح ذاكرة التخزين المؤقت
php artisan module:cache-clear
```

### إعداد الاختبارات
```xml
<!-- phpunit.xml -->
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Modules">
        <directory>app/Modules/*/Tests</directory>
    </testsuite>
</testsuites>
```

### دعم Docker
```yaml
# docker-compose.yml
services:
  app:        # تطبيق Laravel
  webserver:  # خادم Nginx
  database:   # قاعدة بيانات MySQL
  redis:      # ذاكرة التخزين المؤقت
  node:       # بناء الأصول
```

---

## 🚀 التثبيت والتشغيل

### 1. تثبيت التبعيات
```bash
composer install
npm install
```

### 2. إعداد البيئة
```bash
cp .env.example .env
php artisan key:generate
```

### 3. إعداد قاعدة البيانات
```bash
php artisan migrate
php artisan db:seed
```

### 4. بناء الأصول
```bash
npm run dev
# أو للإنتاج
npm run build
```

### 5. تشغيل الخادم
```bash
php artisan serve
# أو باستخدام Docker
docker-compose up -d
```

---

## 🔑 بيانات الدخول الافتراضية

| الدور | البريد الإلكتروني | كلمة المرور |
|-------|------------------|-------------|
| مدير النظام الأعلى | admin@example.com | password |
| مدير النظام | sysadmin@example.com | password |
| مدير القسم | manager@example.com | password |
| مستخدم عادي | user@example.com | password |

---

## 📁 هيكل الملفات الكامل

```
webvue-core/
├── app/
│   ├── Core/
│   │   ├── Services/
│   │   │   ├── ModuleService.php      # خدمة الوحدات
│   │   │   └── RBACService.php        # خدمة الصلاحيات
│   │   ├── Contracts/
│   │   │   └── ModuleServiceInterface.php
│   │   └── Traits/
│   │       └── HasAuditLog.php        # سمة تسجيل التدقيق
│   ├── Models/
│   │   ├── User.php                   # نموذج المستخدم
│   │   ├── Department.php             # نموذج القسم
│   │   ├── Module.php                 # نموذج الوحدة
│   │   └── AuditLog.php              # نموذج سجل التدقيق
│   ├── Http/Controllers/
│   │   ├── DashboardController.php    # تحكم لوحة التحكم
│   │   └── ModuleController.php       # تحكم الوحدات
│   ├── Console/Commands/
│   │   └── MakeModuleCommand.php      # أمر إنشاء الوحدات
│   └── Providers/
│       └── ModuleServiceProvider.php  # مزود خدمة الوحدات
├── database/
│   ├── migrations/                    # هجرات قاعدة البيانات
│   │   ├── create_modules_table.php
│   │   ├── create_departments_table.php
│   │   ├── create_audit_logs_table.php
│   │   └── extend_users_table.php
│   └── seeders/                       # بذور البيانات
│       ├── DatabaseSeeder.php
│       ├── RoleAndPermissionSeeder.php
│       ├── DepartmentSeeder.php
│       ├── UserSeeder.php
│       └── ModuleSeeder.php
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   │   └── Layout/
│   │   │       ├── Sidebar.jsx        # الشريط الجانبي
│   │   │       └── Header.jsx         # رأس الصفحة
│   │   ├── Layouts/
│   │   │   └── DashboardLayout.jsx    # تخطيط لوحة التحكم
│   │   ├── Pages/
│   │   │   └── Dashboard.jsx          # صفحة لوحة التحكم
│   │   ├── i18n/
│   │   │   ├── config.js              # تكوين i18n
│   │   │   └── locales/
│   │   │       ├── en/                # الترجمات الإنجليزية
│   │   │       └── ar/                # الترجمات العربية
│   │   ├── app.jsx                    # تطبيق React الرئيسي
│   │   └── bootstrap.js               # إعداد Axios
│   └── css/
│       └── app.css                    # الأنماط المخصصة
├── config/
│   ├── app.php                        # تكوين التطبيق
│   └── modules.php                    # تكوين الوحدات
├── routes/
│   └── web.php                        # مسارات الويب
├── docker-compose.yml                 # تكوين Docker
├── phpunit.xml                        # تكوين الاختبارات
├── tailwind.config.js                 # تكوين Tailwind
├── vite.config.js                     # تكوين Vite
├── package.json                       # تبعيات Node.js
└── composer.json                      # تبعيات PHP
```

---

## ✅ الميزات المنجزة

### الأساسيات
- ✅ **جاهز للمؤسسات**: الوضع الآمن، فحص التبعيات، إدارة الإصدارات
- ✅ **مترجم بالكامل**: إنجليزي/عربي مع دعم RTL
- ✅ **واجهة حديثة**: خط IBM، تأثيرات زجاجية، تصميم متجاوب
- ✅ **RBAC شامل**: صلاحيات أفقية + عمودية
- ✅ **مسار التدقيق**: تسجيل كامل للأنشطة والتدقيق

### النظام المعياري
- ✅ **التسجيل التلقائي**: تسجيل المسارات والقوائم تلقائياً
- ✅ **CLI للإنشاء**: أمر شامل لإنشاء الوحدات
- ✅ **تجربة SPA**: بدون إعادة تحميل الصفحات، تنقل سلس
- ✅ **جاهز للاختبار**: تكوين PHPUnit مُضمن
- ✅ **دعم Docker**: إعداد كامل للحاويات

---

## 🔧 التخصيص والتطوير

### إضافة وحدة جديدة
```bash
# إنشاء وحدة المنتجات
php artisan make:module Products

# تشغيل الهجرات
php artisan migrate

# تشغيل البذور
php artisan db:seed --class=ProductsSeeder
```

### تخصيص الصلاحيات
```php
// إضافة صلاحية جديدة
Permission::create(['name' => 'products.export']);

// منح صلاحية لدور
$role = Role::findByName('manager');
$role->givePermissionTo('products.export');

// منح صلاحية نطاق للمستخدم
$rbacService->grantScopedAccess($user, 'products.view', 'department', $deptId);
```

### إضافة ترجمات جديدة
```json
// resources/js/i18n/locales/ar/products.json
{
  "title": "المنتجات",
  "create": "إنشاء منتج",
  "edit": "تعديل منتج",
  "delete": "حذف منتج"
}
```

---

## 📞 الدعم والمساعدة

### الأوامر المفيدة
```bash
# مسح جميع ذاكرات التخزين المؤقت
php artisan optimize:clear

# إعادة بناء الفهارس
php artisan route:cache
php artisan config:cache
php artisan view:cache

# تشغيل الاختبارات
php artisan test
```

### استكشاف الأخطاء
```bash
# عرض السجلات
tail -f storage/logs/laravel.log

# فحص حالة قاعدة البيانات
php artisan migrate:status

# فحص الوحدات
php artisan module:list
```

---

## 📈 الخطوات التالية

### التحسينات المقترحة
1. **إضافة المزيد من الوحدات**: المنتجات، الطلبات، التقارير
2. **تحسين الأداء**: تخزين مؤقت متقدم، فهرسة قاعدة البيانات
3. **الأمان المتقدم**: 2FA، تسجيل الدخول الموحد
4. **التقارير والتحليلات**: لوحات معلومات تفاعلية
5. **API للجوال**: واجهات برمجة تطبيقات RESTful

### النشر للإنتاج
```bash
# تحسين للإنتاج
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

**تم إنجاز المشروع بنجاح! 🎉**

النظام جاهز للاستخدام والتطوير مع جميع الميزات المطلوبة مُنفذة وفقاً لأفضل الممارسات في Laravel و React.
