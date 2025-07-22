# دليل استخدام خدمة الإعدادات في الموديولات الأخرى

## مقدمة

يوفر موديول الإعدادات طريقة مركزية وفعالة لإدارة إعدادات التطبيق. يمكن للموديولات الأخرى الاستفادة من هذه الخدمة للوصول إلى الإعدادات وتخزينها بطريقة آمنة ومنظمة. هذا الدليل يشرح كيفية دمج واستخدام خدمة الإعدادات في الموديولات الأخرى.

## طرق الوصول إلى الإعدادات

هناك عدة طرق للوصول إلى الإعدادات في موديولات أخرى:

### 1. استخدام حقن التبعية (Dependency Injection)

الطريقة المفضلة هي حقن خدمة الإعدادات في الكونتروللر أو الخدمة التي تحتاج إلى الوصول إلى الإعدادات:

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
        // الحصول على إعداد محدد
        $siteName = $this->settingsService->get('site_name');
        
        // التحقق من وجود إعداد
        if ($this->settingsService->has('feature_enabled')) {
            // ...
        }
        
        // الحصول على إعدادات فئة محددة
        $mailSettings = $this->settingsService->getByCategory('mail');
        
        return view('your-view', [
            'siteName' => $siteName,
            'mailSettings' => $mailSettings,
        ]);
    }
}
```

### 2. استخدام الدالة المساعدة الثابتة (Static Helper)

لاستخدام سريع، يمكن استخدام الدالة المساعدة الثابتة المتوفرة في نموذج `Settings`:

```php
use App\Modules\Settings\Models\Settings;

class YourClass
{
    public function yourMethod()
    {
        // الحصول على إعداد محدد
        $siteName = Settings::getValue('site_name');
        
        // تعيين قيمة إعداد
        Settings::setValue('site_name', 'اسم الموقع الجديد');
        
        // الحصول على إعداد مع قيمة افتراضية
        $maxUploadSize = Settings::getValue('max_upload_size', 10);
    }
}
```

### 3. استخدام واجهة برمجة التطبيقات (API)

يمكن للواجهة الأمامية (Frontend) الوصول إلى الإعدادات العامة من خلال نقطة النهاية API:

```javascript
// React/Inertia.js
import axios from 'axios';

const fetchSettings = async () => {
  try {
    const response = await axios.get('/api/settings/public');
    if (response.data.success) {
      return response.data.settings;
    }
    return {};
  } catch (error) {
    console.error('Failed to fetch settings:', error);
    return {};
  }
};

// استخدام الإعدادات في المكون
const YourComponent = () => {
  const [settings, setSettings] = useState({});
  
  useEffect(() => {
    fetchSettings().then(data => setSettings(data));
  }, []);
  
  return (
    <div>
      <h1>{settings.general?.site_name || 'Default Site Name'}</h1>
      {settings.general?.logo && (
        <img src={settings.general.logo} alt="Logo" />
      )}
    </div>
  );
};
```

## أنماط الاستخدام الشائعة

### 1. التكوين المركزي للموديول

استخدم الإعدادات لتخزين تكوين الموديول الخاص بك:

```php
// في خدمة الموديول الخاص بك
use App\Modules\Settings\Services\SettingsService;

class YourModuleService
{
    public function __construct(
        private readonly SettingsService $settingsService
    ) {
        // ...
    }
    
    public function initialize()
    {
        // التحقق من وجود الإعدادات الافتراضية وإنشاؤها إذا لم تكن موجودة
        if (!$this->settingsService->has('your_module.enabled')) {
            $this->settingsService->createOrUpdate([
                'key' => 'your_module.enabled',
                'category' => 'your_module',
                'value' => true,
                'type' => 'boolean',
                'description' => 'تفعيل/تعطيل الموديول',
                'is_public' => false,
                'is_encrypted' => false,
                'active' => true,
            ]);
        }
    }
    
    public function isEnabled()
    {
        return $this->settingsService->get('your_module.enabled', false);
    }
}
```

### 2. إعدادات المستخدم المخصصة

يمكن استخدام الإعدادات لتخزين تفضيلات المستخدم:

```php
// في خدمة المستخدم
use App\Modules\Settings\Services\SettingsService;

class UserPreferencesService
{
    public function __construct(
        private readonly SettingsService $settingsService
    ) {
        // ...
    }
    
    public function getUserPreference(int $userId, string $key, $default = null)
    {
        $settingKey = "user.{$userId}.{$key}";
        return $this->settingsService->get($settingKey, $default);
    }
    
    public function setUserPreference(int $userId, string $key, $value)
    {
        $settingKey = "user.{$userId}.{$key}";
        
        if ($this->settingsService->has($settingKey)) {
            $this->settingsService->set($settingKey, $value);
        } else {
            $this->settingsService->createOrUpdate([
                'key' => $settingKey,
                'category' => 'user_preferences',
                'value' => $value,
                'type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'string'),
                'description' => "تفضيل المستخدم {$userId} - {$key}",
                'is_public' => false,
                'is_encrypted' => false,
                'active' => true,
            ]);
        }
    }
}
```

### 3. تكوين الميزات المشروطة (Feature Flags)

استخدم الإعدادات لتفعيل/تعطيل ميزات معينة:

```php
// في خدمة الميزات
use App\Modules\Settings\Services\SettingsService;

class FeatureFlagService
{
    public function __construct(
        private readonly SettingsService $settingsService
    ) {
        // ...
    }
    
    public function isFeatureEnabled(string $featureName, bool $default = false)
    {
        $settingKey = "feature.{$featureName}";
        return $this->settingsService->get($settingKey, $default);
    }
    
    public function enableFeature(string $featureName)
    {
        $this->setFeatureStatus($featureName, true);
    }
    
    public function disableFeature(string $featureName)
    {
        $this->setFeatureStatus($featureName, false);
    }
    
    private function setFeatureStatus(string $featureName, bool $status)
    {
        $settingKey = "feature.{$featureName}";
        
        if ($this->settingsService->has($settingKey)) {
            $this->settingsService->set($settingKey, $status);
        } else {
            $this->settingsService->createOrUpdate([
                'key' => $settingKey,
                'category' => 'features',
                'value' => $status,
                'type' => 'boolean',
                'description' => "تفعيل/تعطيل ميزة {$featureName}",
                'is_public' => true,
                'is_encrypted' => false,
                'active' => true,
            ]);
        }
    }
}
```

## أفضل الممارسات

### 1. استخدام بادئة للمفاتيح

استخدم بادئة لمفاتيح الإعدادات الخاصة بالموديول الخاص بك لتجنب تضارب المفاتيح:

```php
// بدلاً من
$settingsService->get('timeout');

// استخدم
$settingsService->get('your_module.timeout');
```

### 2. استخدام التخزين المؤقت بحكمة

خدمة الإعدادات تستخدم التخزين المؤقت تلقائيًا، ولكن تأكد من مسح ذاكرة التخزين المؤقت عند تحديث الإعدادات:

```php
// بعد تحديث الإعدادات
$settingsService->set('your_module.setting', $value);
$settingsService->clearCache('your_module.setting');

// أو مسح جميع الإعدادات المخزنة مؤقتًا
$settingsService->clearAllCache();
```

### 3. التعامل مع الإعدادات المشفرة

إذا كنت تتعامل مع بيانات حساسة، استخدم خيار التشفير:

```php
$settingsService->createOrUpdate([
    'key' => 'your_module.api_key',
    'category' => 'your_module',
    'value' => 'api_secret_key_123',
    'type' => 'string',
    'description' => 'مفتاح API للخدمة الخارجية',
    'is_public' => false,
    'is_encrypted' => true, // سيتم تشفير القيمة تلقائيًا
    'active' => true,
]);
```

### 4. استخدام القيم الافتراضية

دائمًا قم بتوفير قيمة افتراضية عند استرجاع الإعدادات لتجنب الأخطاء:

```php
$timeout = $settingsService->get('your_module.timeout', 30);
```

### 5. التحقق من وجود الإعداد

تحقق من وجود الإعداد قبل محاولة استخدامه:

```php
if ($settingsService->has('your_module.feature_enabled')) {
    // استخدم الإعداد
} else {
    // إنشاء الإعداد أو استخدام القيمة الافتراضية
}
```

## مثال كامل: دمج خدمة الإعدادات في موديول جديد

فيما يلي مثال كامل لكيفية دمج خدمة الإعدادات في موديول جديد:

### 1. إنشاء خدمة الموديول

```php
<?php

namespace App\Modules\YourModule\Services;

use App\Modules\Settings\Services\SettingsService;
use Illuminate\Support\Facades\Log;

class YourModuleService
{
    private const SETTINGS_PREFIX = 'your_module';
    
    public function __construct(
        private readonly SettingsService $settingsService
    ) {
        // ...
    }
    
    public function initialize()
    {
        // إنشاء الإعدادات الافتراضية للموديول
        $this->ensureDefaultSettings();
    }
    
    private function ensureDefaultSettings()
    {
        $defaultSettings = [
            [
                'key' => $this->getSettingKey('enabled'),
                'category' => self::SETTINGS_PREFIX,
                'value' => true,
                'type' => 'boolean',
                'description' => 'تفعيل/تعطيل الموديول',
                'is_public' => true,
                'is_encrypted' => false,
                'active' => true,
            ],
            [
                'key' => $this->getSettingKey('api_url'),
                'category' => self::SETTINGS_PREFIX,
                'value' => 'https://api.example.com',
                'type' => 'string',
                'description' => 'عنوان URL للـ API',
                'is_public' => false,
                'is_encrypted' => false,
                'active' => true,
            ],
            [
                'key' => $this->getSettingKey('api_key'),
                'category' => self::SETTINGS_PREFIX,
                'value' => '',
                'type' => 'string',
                'description' => 'مفتاح API',
                'is_public' => false,
                'is_encrypted' => true,
                'active' => true,
            ],
        ];
        
        foreach ($defaultSettings as $setting) {
            if (!$this->settingsService->has($setting['key'])) {
                try {
                    $this->settingsService->createOrUpdate($setting);
                } catch (\Exception $e) {
                    Log::error("Failed to create default setting: {$setting['key']}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
    
    public function isEnabled()
    {
        return $this->settingsService->get($this->getSettingKey('enabled'), false);
    }
    
    public function getApiUrl()
    {
        return $this->settingsService->get($this->getSettingKey('api_url'), '');
    }
    
    public function getApiKey()
    {
        return $this->settingsService->get($this->getSettingKey('api_key'), '');
    }
    
    public function setApiKey(string $apiKey)
    {
        $this->settingsService->set($this->getSettingKey('api_key'), $apiKey);
    }
    
    private function getSettingKey(string $key)
    {
        return self::SETTINGS_PREFIX . '.' . $key;
    }
}
```

### 2. استخدام الخدمة في الكونتروللر

```php
<?php

namespace App\Modules\YourModule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\YourModule\Services\YourModuleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YourModuleController extends Controller
{
    public function __construct(
        private readonly YourModuleService $yourModuleService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:your_module.view')->only(['index', 'show']);
        $this->middleware('permission:your_module.edit')->only(['edit', 'update']);
    }
    
    public function index(): Response
    {
        // التحقق من تفعيل الموديول
        if (!$this->yourModuleService->isEnabled()) {
            return Inertia::render('Errors/ModuleDisabled');
        }
        
        $apiUrl = $this->yourModuleService->getApiUrl();
        
        return Inertia::render('YourModule/Index', [
            'apiUrl' => $apiUrl,
            'isConfigured' => !empty($this->yourModuleService->getApiKey()),
        ]);
    }
    
    public function updateSettings(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
        ]);
        
        $this->yourModuleService->setApiKey($request->input('api_key'));
        
        return redirect()->route('your_module.index')
            ->with('success', 'تم تحديث الإعدادات بنجاح');
    }
}
```

### 3. استخدام الإعدادات في الواجهة الأمامية

```jsx
// resources/js/Pages/YourModule/Index.jsx
import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';

export default function Index({ apiUrl, isConfigured }) {
  const { data, setData, post, processing, errors } = useForm({
    api_key: '',
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    post(route('your_module.update-settings'));
  };

  return (
    <AppLayout>
      <Head title="Your Module" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 bg-white border-b border-gray-200">
              <h1 className="text-2xl font-bold mb-4">Your Module</h1>
              
              {isConfigured ? (
                <div className="mb-4 p-4 bg-green-100 rounded">
                  <p>الموديول مكوّن بشكل صحيح ويعمل.</p>
                  <p>عنوان API: {apiUrl}</p>
                </div>
              ) : (
                <div className="mb-4 p-4 bg-yellow-100 rounded">
                  <p>الموديول يحتاج إلى تكوين.</p>
                </div>
              )}
              
              <form onSubmit={handleSubmit} className="mt-6">
                <div className="mb-4">
                  <InputLabel htmlFor="api_key" value="مفتاح API" />
                  <TextInput
                    id="api_key"
                    type="password"
                    className="mt-1 block w-full"
                    value={data.api_key}
                    onChange={(e) => setData('api_key', e.target.value)}
                    required
                  />
                  {errors.api_key && (
                    <p className="text-red-500 text-sm mt-1">{errors.api_key}</p>
                  )}
                </div>
                
                <PrimaryButton disabled={processing}>
                  حفظ الإعدادات
                </PrimaryButton>
              </form>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
```

## الخلاصة

خدمة الإعدادات توفر طريقة مرنة وقوية لإدارة إعدادات التطبيق. باتباع الأنماط والممارسات الموضحة في هذا الدليل، يمكنك دمج هذه الخدمة بسهولة في الموديولات الخاصة بك وتحقيق الاستفادة القصوى منها.

تذكر دائمًا:
- استخدم بادئة للمفاتيح لتجنب التضارب
- استفد من التخزين المؤقت لتحسين الأداء
- استخدم التشفير للبيانات الحساسة
- قم بتوفير قيم افتراضية عند استرجاع الإعدادات
- تحقق من وجود الإعدادات قبل استخدامها

باتباع هذه الإرشادات، ستتمكن من إنشاء تطبيق مرن وقابل للتكوين بسهولة.
