# توثيق واجهة برمجة التطبيقات (API) لموديول الإعدادات

## نظرة عامة

موديول الإعدادات يوفر واجهة برمجة تطبيقات (API) لإدارة إعدادات النظام. يمكن استخدام هذه الواجهة للحصول على الإعدادات وتحديثها وإنشاء إعدادات جديدة وحذفها. كما يوفر الموديول إمكانية الحصول على الإعدادات العامة للواجهة الأمامية.

## نقاط النهاية (Endpoints)

### الحصول على قائمة الإعدادات

```
GET /api/settings
```

**الوصف**: يقوم بإرجاع قائمة بجميع الإعدادات المتاحة في النظام مع إمكانية التصفية حسب الفئة والبحث.

**المعلمات**:
- `category` (اختياري): تصفية الإعدادات حسب الفئة
- `search` (اختياري): البحث في الإعدادات
- `per_page` (اختياري): عدد العناصر في الصفحة الواحدة

**الصلاحيات المطلوبة**: `settings.view`

**الاستجابة**:
```json
{
  "settings": {
    "data": [
      {
        "id": 1,
        "key": "site_name",
        "category": "general",
        "value": "اسم الموقع",
        "type": "string",
        "description": "اسم الموقع الرسمي",
        "is_public": true,
        "is_encrypted": false,
        "sort_order": 1,
        "active": true,
        "created_at": "2023-07-01T12:00:00.000000Z",
        "updated_at": "2023-07-01T12:00:00.000000Z"
      }
    ],
    "links": {
      "first": "http://example.com/api/settings?page=1",
      "last": "http://example.com/api/settings?page=1",
      "prev": null,
      "next": null
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 1,
      "path": "http://example.com/api/settings",
      "per_page": 15,
      "to": 1,
      "total": 1
    }
  },
  "categories": [
    "general",
    "mail",
    "security"
  ],
  "filters": {
    "category": null,
    "search": null,
    "per_page": 15
  }
}
```

### إنشاء إعداد جديد

```
POST /api/settings
```

**الوصف**: يقوم بإنشاء إعداد جديد في النظام.

**المعلمات**:
- `key` (مطلوب): مفتاح الإعداد (يجب أن يكون فريدًا)
- `category` (مطلوب): فئة الإعداد
- `value` (مطلوب): قيمة الإعداد
- `type` (مطلوب): نوع الإعداد (string, boolean, number, json, file)
- `description` (مطلوب): وصف الإعداد
- `is_public` (اختياري): هل الإعداد عام أم لا (افتراضي: false)
- `is_encrypted` (اختياري): هل يجب تشفير قيمة الإعداد (افتراضي: false)
- `sort_order` (اختياري): ترتيب الإعداد
- `active` (اختياري): هل الإعداد نشط أم لا (افتراضي: true)
- `file` (اختياري): ملف للتحميل (مطلوب إذا كان النوع "file")

**الصلاحيات المطلوبة**: `settings.create`

**الاستجابة**:
```json
{
  "success": true,
  "message": "تم إنشاء الإعداد بنجاح",
  "setting": {
    "id": 1,
    "key": "site_name",
    "category": "general",
    "value": "اسم الموقع",
    "type": "string",
    "description": "اسم الموقع الرسمي",
    "is_public": true,
    "is_encrypted": false,
    "sort_order": 1,
    "active": true,
    "created_at": "2023-07-01T12:00:00.000000Z",
    "updated_at": "2023-07-01T12:00:00.000000Z"
  }
}
```

### تحديث إعداد موجود

```
PUT /api/settings/{setting}
```

**الوصف**: يقوم بتحديث إعداد موجود في النظام.

**المعلمات**:
- `key` (مطلوب): مفتاح الإعداد
- `category` (مطلوب): فئة الإعداد
- `value` (مطلوب): قيمة الإعداد
- `type` (مطلوب): نوع الإعداد (string, boolean, number, json, file)
- `description` (مطلوب): وصف الإعداد
- `is_public` (اختياري): هل الإعداد عام أم لا
- `is_encrypted` (اختياري): هل يجب تشفير قيمة الإعداد
- `sort_order` (اختياري): ترتيب الإعداد
- `active` (اختياري): هل الإعداد نشط أم لا
- `file` (اختياري): ملف للتحميل (مطلوب إذا كان النوع "file")

**الصلاحيات المطلوبة**: `settings.edit`

**الاستجابة**:
```json
{
  "success": true,
  "message": "تم تحديث الإعداد بنجاح",
  "setting": {
    "id": 1,
    "key": "site_name",
    "category": "general",
    "value": "اسم الموقع المحدث",
    "type": "string",
    "description": "اسم الموقع الرسمي",
    "is_public": true,
    "is_encrypted": false,
    "sort_order": 1,
    "active": true,
    "created_at": "2023-07-01T12:00:00.000000Z",
    "updated_at": "2023-07-01T12:30:00.000000Z"
  }
}
```

### حذف إعداد

```
DELETE /api/settings/{setting}
```

**الوصف**: يقوم بحذف إعداد من النظام.

**الصلاحيات المطلوبة**: `settings.delete`

**الاستجابة**:
```json
{
  "success": true,
  "message": "تم حذف الإعداد بنجاح"
}
```

### تحديث إعدادات متعددة دفعة واحدة

```
POST /api/settings/update-multiple
```

**الوصف**: يقوم بتحديث عدة إعدادات دفعة واحدة.

**المعلمات**:
- `settings` (مطلوب): مصفوفة من الإعدادات بتنسيق {key: value}

**الصلاحيات المطلوبة**: `settings.edit`

**الاستجابة**:
```json
{
  "success": true,
  "message": "تم تحديث الإعدادات بنجاح"
}
```

### الحصول على الإعدادات العامة

```
GET /api/settings/public
```

**الوصف**: يقوم بإرجاع جميع الإعدادات العامة المتاحة للواجهة الأمامية.

**الصلاحيات المطلوبة**: لا يوجد (متاح للجميع)

**الاستجابة**:
```json
{
  "success": true,
  "settings": {
    "general": {
      "site_name": "اسم الموقع",
      "site_description": "وصف الموقع"
    },
    "appearance": {
      "primary_color": "#007bff",
      "logo": "/storage/settings/logo.png"
    }
  }
}
```

### مسح ذاكرة التخزين المؤقت للإعدادات

```
POST /api/settings/clear-cache
```

**الوصف**: يقوم بمسح ذاكرة التخزين المؤقت للإعدادات.

**الصلاحيات المطلوبة**: `settings.edit`

**الاستجابة**:
```json
{
  "success": true,
  "message": "تم مسح ذاكرة التخزين المؤقت بنجاح"
}
```

## أمثلة على الاستخدام

### الحصول على إعداد محدد باستخدام المفتاح

```php
$settingsService = app(SettingsService::class);
$siteName = $settingsService->get('site_name');
```

### تحديث إعداد محدد

```php
$settingsService = app(SettingsService::class);
$settingsService->set('site_name', 'اسم الموقع الجديد');
```

### التحقق من وجود إعداد

```php
$settingsService = app(SettingsService::class);
if ($settingsService->has('site_name')) {
    // الإعداد موجود
}
```

### الحصول على جميع الإعدادات في فئة محددة

```php
$settingsService = app(SettingsService::class);
$generalSettings = $settingsService->getByCategory('general');
```

### الحصول على الإعدادات العامة

```php
$settingsService = app(SettingsService::class);
$publicSettings = $settingsService->getPublicSettings();
```

## ملاحظات هامة

1. **التشفير**: الإعدادات التي تم تعيينها كـ `is_encrypted = true` سيتم تشفيرها تلقائيًا عند الحفظ وفك تشفيرها عند الاسترجاع.

2. **التخزين المؤقت**: يتم تخزين الإعدادات في ذاكرة التخزين المؤقت لتحسين الأداء. يمكن مسح ذاكرة التخزين المؤقت باستخدام `clearCache()` أو `clearAllCache()`.

3. **الملفات**: عند تحميل ملف، سيتم تخزينه في مجلد `storage/app/public/settings` ويتم تخزين المسار النسبي في قيمة الإعداد.

4. **الصلاحيات**: تأكد من أن المستخدم لديه الصلاحيات المناسبة قبل الوصول إلى نقاط النهاية.

5. **التحقق من الصحة**: يتم التحقق من صحة جميع البيانات المدخلة باستخدام `StoreSettingsRequest` للتأكد من أنها تلبي المتطلبات.

## الاستخدام في الموديولات الأخرى

يمكن استخدام خدمة الإعدادات في أي موديول آخر عن طريق حقن الخدمة في الكونتروللر أو الخدمة:

```php
use App\Modules\Settings\Services\SettingsService;

class YourController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService
    ) {
        // ...
    }
    
    public function yourMethod()
    {
        $setting = $this->settingsService->get('your_setting_key');
        // ...
    }
}
```

أو باستخدام الدالة المساعدة العامة:

```php
$setting = Settings::getValue('your_setting_key');
```

## التعامل مع الأخطاء

جميع نقاط النهاية تقوم بإرجاع رسائل خطأ مناسبة في حالة حدوث خطأ. يمكن التعامل مع هذه الأخطاء في الواجهة الأمامية عن طريق التحقق من حقل `success` في الاستجابة.

```javascript
axios.post('/api/settings', formData)
  .then(response => {
    if (response.data.success) {
      // تم بنجاح
    } else {
      // فشل
    }
  })
  .catch(error => {
    // خطأ في الطلب
  });
```
