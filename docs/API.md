# واجهات برمجة التطبيقات | API Documentation

## نظرة عامة | Overview

يوفر النظام واجهات برمجة تطبيقات RESTful شاملة لإدارة جميع مكونات النظام مع دعم المصادقة والتفويض الكامل.

The system provides comprehensive RESTful APIs for managing all system components with full authentication and authorization support.

---

## المصادقة | Authentication

### Laravel Sanctum
```php
// الحصول على رمز الوصول
POST /api/auth/login
{
    "email": "admin@example.com",
    "password": "password"
}

// الاستجابة
{
    "token": "1|abc123...",
    "user": { ... },
    "expires_at": "2024-01-01T00:00:00Z"
}
```

### استخدام الرمز | Using Token
```javascript
// في رؤوس الطلبات
headers: {
    'Authorization': 'Bearer 1|abc123...',
    'Accept': 'application/json',
    'Content-Type': 'application/json'
}
```

---

## واجهات المستخدمين | Users API

### قائمة المستخدمين | List Users
```http
GET /api/users
```

**المعاملات | Parameters:**
- `page` (int): رقم الصفحة
- `per_page` (int): عدد العناصر لكل صفحة (افتراضي: 15)
- `search` (string): البحث في الاسم والبريد الإلكتروني
- `department_id` (int): تصفية حسب القسم
- `active` (boolean): تصفية حسب الحالة

**الاستجابة | Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Super Administrator",
            "first_name": "Super",
            "last_name": "Administrator",
            "email": "admin@example.com",
            "phone": null,
            "avatar": null,
            "locale": "en",
            "timezone": "UTC",
            "department": {
                "id": 1,
                "name": "Information Technology",
                "code": "IT"
            },
            "roles": ["super-admin"],
            "permissions": ["users.view", "users.create", ...],
            "active": true,
            "last_login_at": "2024-01-01T00:00:00Z",
            "created_at": "2024-01-01T00:00:00Z",
            "updated_at": "2024-01-01T00:00:00Z"
        }
    ],
    "links": { ... },
    "meta": { ... }
}
```

### إنشاء مستخدم | Create User
```http
POST /api/users
```

**البيانات المطلوبة | Required Data:**
```json
{
    "first_name": "أحمد",
    "last_name": "محمد",
    "email": "ahmed@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+966501234567",
    "department_id": 1,
    "locale": "ar",
    "timezone": "Asia/Riyadh",
    "active": true,
    "roles": ["user"]
}
```

### تحديث مستخدم | Update User
```http
PUT /api/users/{id}
```

### حذف مستخدم | Delete User
```http
DELETE /api/users/{id}
```

### تفعيل/إلغاء تفعيل | Toggle Status
```http
POST /api/users/{id}/toggle-status
```

---

## واجهات الأدوار | Roles API

### قائمة الأدوار | List Roles
```http
GET /api/roles
```

**الاستجابة | Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "super-admin",
            "display_name": "مدير النظام الأعلى",
            "guard_name": "web",
            "permissions": [
                {
                    "id": 1,
                    "name": "users.view",
                    "display_name": "عرض المستخدمين"
                }
            ],
            "users_count": 1,
            "created_at": "2024-01-01T00:00:00Z"
        }
    ]
}
```

### إنشاء دور | Create Role
```http
POST /api/roles
```

```json
{
    "name": "department-manager",
    "display_name": "مدير القسم",
    "permissions": ["users.view", "departments.edit"]
}
```

### تحديث صلاحيات الدور | Update Role Permissions
```http
PUT /api/roles/{id}/permissions
```

```json
{
    "permissions": ["users.view", "users.create", "departments.view"]
}
```

---

## واجهات الأقسام | Departments API

### قائمة الأقسام | List Departments
```http
GET /api/departments
```

**المعاملات | Parameters:**
- `include_hierarchy` (boolean): تضمين التسلسل الهرمي
- `active_only` (boolean): الأقسام النشطة فقط

**الاستجابة | Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "تقنية المعلومات",
            "code": "IT",
            "description": "مسؤول عن البنية التحتية التقنية والتطوير",
            "parent_id": null,
            "manager": {
                "id": 1,
                "name": "Super Administrator",
                "email": "admin@example.com"
            },
            "children": [
                {
                    "id": 6,
                    "name": "تطوير البرمجيات",
                    "code": "DEV",
                    "parent_id": 1
                }
            ],
            "users_count": 2,
            "hierarchy_path": "تقنية المعلومات",
            "level": 0,
            "active": true,
            "created_at": "2024-01-01T00:00:00Z"
        }
    ]
}
```

### إنشاء قسم | Create Department
```http
POST /api/departments
```

```json
{
    "name": "التسويق الرقمي",
    "code": "DMKT",
    "description": "مسؤول عن التسويق الرقمي ووسائل التواصل الاجتماعي",
    "parent_id": 5,
    "manager_id": 3,
    "active": true
}
```

---

## واجهات الوحدات | Modules API

### قائمة الوحدات | List Modules
```http
GET /api/modules
```

**الاستجابة | Response:**
```json
{
    "data": [
        {
            "name": "Users",
            "display_name": "إدارة المستخدمين",
            "description": "إدارة مستخدمي النظام والملفات الشخصية",
            "version": "1.0.0",
            "active": true,
            "critical": true,
            "dependencies": [],
            "permissions": ["users.view", "users.create", "users.edit", "users.delete"],
            "navigation": {
                "name": "users",
                "href": "/users",
                "icon": "UsersIcon",
                "order": 10
            },
            "config": {
                "per_page": 15,
                "allow_registration": false
            },
            "can_disable": false,
            "installed_at": "2024-01-01T00:00:00Z",
            "last_updated": "2024-01-01T00:00:00Z"
        }
    ]
}
```

### تفعيل وحدة | Enable Module
```http
POST /api/modules/{name}/enable
```

### إلغاء تفعيل وحدة | Disable Module
```http
POST /api/modules/{name}/disable
```

### تكوين وحدة | Configure Module
```http
PUT /api/modules/{name}/configure
```

```json
{
    "config": {
        "per_page": 20,
        "allow_registration": true,
        "require_email_verification": false
    }
}
```

---

## واجهات سجلات النشاط | Activity Logs API

### قائمة الأنشطة | List Activities
```http
GET /api/activity-logs
```

**المعاملات | Parameters:**
- `causer_id` (int): معرف المستخدم المسبب
- `subject_type` (string): نوع الكائن المتأثر
- `event` (string): نوع الحدث
- `date_from` (date): من تاريخ
- `date_to` (date): إلى تاريخ

**الاستجابة | Response:**
```json
{
    "data": [
        {
            "id": 1,
            "log_name": "default",
            "description": "created",
            "subject_type": "App\\Models\\User",
            "subject_id": 2,
            "causer": {
                "id": 1,
                "name": "Super Administrator",
                "email": "admin@example.com"
            },
            "properties": {
                "attributes": {
                    "name": "أحمد محمد",
                    "email": "ahmed@example.com"
                }
            },
            "created_at": "2024-01-01T00:00:00Z"
        }
    ]
}
```

---

## واجهات سجلات التدقيق | Audit Logs API

### قائمة سجلات التدقيق | List Audit Logs
```http
GET /api/audit-logs
```

**الاستجابة | Response:**
```json
{
    "data": [
        {
            "id": 1,
            "event": "updated",
            "auditable_type": "App\\Models\\User",
            "auditable_id": 1,
            "user": {
                "id": 1,
                "name": "Super Administrator"
            },
            "old_values": {
                "name": "Old Name"
            },
            "new_values": {
                "name": "New Name"
            },
            "ip_address": "127.0.0.1",
            "user_agent": "Mozilla/5.0...",
            "metadata": {
                "url": "/users/1",
                "method": "PUT"
            },
            "created_at": "2024-01-01T00:00:00Z"
        }
    ]
}
```

---

## واجهات الإحصائيات | Statistics API

### إحصائيات لوحة التحكم | Dashboard Statistics
```http
GET /api/dashboard/stats
```

**الاستجابة | Response:**
```json
{
    "users": {
        "total": 25,
        "active": 23,
        "inactive": 2,
        "new_this_month": 5
    },
    "departments": {
        "total": 8,
        "active": 7,
        "with_manager": 6
    },
    "roles": {
        "total": 4,
        "custom": 2
    },
    "modules": {
        "total": 5,
        "active": 5,
        "critical": 3
    },
    "activities": {
        "today": 15,
        "this_week": 89,
        "this_month": 234
    }
}
```

---

## معالجة الأخطاء | Error Handling

### رموز الاستجابة | Response Codes
- `200` - نجح الطلب
- `201` - تم الإنشاء بنجاح
- `400` - خطأ في البيانات المرسلة
- `401` - غير مصرح له
- `403` - ممنوع
- `404` - غير موجود
- `422` - خطأ في التحقق من صحة البيانات
- `500` - خطأ في الخادم

### تنسيق الأخطاء | Error Format
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "البريد الإلكتروني مطلوب",
            "البريد الإلكتروني يجب أن يكون صالحاً"
        ]
    }
}
```

---

## التصفية والبحث | Filtering & Search

### معاملات عامة | Common Parameters
- `search` - البحث النصي
- `sort` - ترتيب النتائج (`name`, `-created_at`)
- `filter[field]` - تصفية حسب حقل معين
- `include` - تضمين علاقات (`department`, `roles`, `permissions`)

### أمثلة | Examples
```http
# البحث في المستخدمين
GET /api/users?search=أحمد&filter[active]=true&sort=-created_at

# تضمين العلاقات
GET /api/users?include=department,roles&per_page=25

# تصفية الأقسام النشطة
GET /api/departments?filter[active]=true&include=manager,children
```

---

## التصدير والاستيراد | Export & Import

### تصدير البيانات | Export Data
```http
GET /api/users/export?format=xlsx
GET /api/departments/export?format=csv
```

### استيراد البيانات | Import Data
```http
POST /api/users/import
Content-Type: multipart/form-data

file: users.xlsx
mapping: {
    "الاسم الأول": "first_name",
    "الاسم الأخير": "last_name",
    "البريد الإلكتروني": "email"
}
```

---

## WebHooks

### تسجيل WebHook | Register WebHook
```http
POST /api/webhooks
```

```json
{
    "url": "https://your-app.com/webhook",
    "events": ["user.created", "user.updated", "user.deleted"],
    "secret": "your-secret-key"
}
```

### أحداث متاحة | Available Events
- `user.created` - إنشاء مستخدم
- `user.updated` - تحديث مستخدم
- `user.deleted` - حذف مستخدم
- `department.created` - إنشاء قسم
- `module.enabled` - تفعيل وحدة
- `module.disabled` - إلغاء تفعيل وحدة

---

## أمثلة التطبيق | Implementation Examples

### JavaScript (Axios)
```javascript
const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// الحصول على المستخدمين
const getUsers = async (params = {}) => {
    try {
        const response = await api.get('/users', { params });
        return response.data;
    } catch (error) {
        console.error('Error fetching users:', error.response.data);
        throw error;
    }
};

// إنشاء مستخدم جديد
const createUser = async (userData) => {
    try {
        const response = await api.post('/users', userData);
        return response.data;
    } catch (error) {
        console.error('Error creating user:', error.response.data);
        throw error;
    }
};
```

### PHP (Guzzle)
```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://localhost:8000/api/',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ]
]);

// الحصول على المستخدمين
$response = $client->get('users', [
    'query' => [
        'search' => 'أحمد',
        'per_page' => 20
    ]
]);

$users = json_decode($response->getBody(), true);
```

---

## حدود الاستخدام | Rate Limiting

### الحدود الافتراضية | Default Limits
- **API عام**: 60 طلب/دقيقة
- **المصادقة**: 5 محاولات/دقيقة
- **التصدير**: 10 طلبات/ساعة
- **WebHooks**: 100 طلب/دقيقة

### رؤوس الاستجابة | Response Headers
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1609459200
```

---

**واجهات برمجة التطبيقات جاهزة للاستخدام! 🚀**

جميع الواجهات تدعم المصادقة الآمنة والتفويض المتقدم مع إمكانيات البحث والتصفية الشاملة.
