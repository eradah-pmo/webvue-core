<?php

namespace Tests\Unit\Services\Settings;

use Tests\TestCase;
use App\Modules\Settings\Models\Settings;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SettingsService $settingsService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->settingsService = app(SettingsService::class);
        
        // تنظيف الكاش قبل كل اختبار
        Cache::flush();
        
        // تهيئة التخزين الوهمي
        Storage::fake('public');
    }

    /** @test */
    public function it_can_get_setting_value()
    {
        // إنشاء إعداد اختبار
        Settings::create([
            'key' => 'test_key',
            'category' => 'general',
            'value' => 'test_value',
            'type' => 'string',
            'active' => true,
        ]);

        // اختبار الحصول على القيمة
        $value = $this->settingsService->get('test_key');
        $this->assertEquals('test_value', $value);
        
        // اختبار القيمة الافتراضية
        $defaultValue = $this->settingsService->get('non_existent', 'default_value');
        $this->assertEquals('default_value', $defaultValue);
    }

    /** @test */
    public function it_caches_setting_values()
    {
        // إنشاء إعداد اختبار
        Settings::create([
            'key' => 'cached_key',
            'category' => 'general',
            'value' => 'original_value',
            'type' => 'string',
            'active' => true,
        ]);

        // الاستدعاء الأول - يجب أن يخزن في الكاش
        $value1 = $this->settingsService->get('cached_key');
        $this->assertEquals('original_value', $value1);
        
        // تغيير القيمة مباشرة في قاعدة البيانات (بدون استخدام الخدمة)
        Settings::where('key', 'cached_key')->update(['value' => 'updated_value']);
        
        // الاستدعاء الثاني - يجب أن يأتي من الكاش
        $value2 = $this->settingsService->get('cached_key');
        $this->assertEquals('original_value', $value2); // لا يزال يعرض القيمة القديمة من الكاش
        
        // تنظيف الكاش
        Cache::flush();
        
        // الاستدعاء الثالث - يجب أن يحصل على القيمة المحدثة
        $value3 = $this->settingsService->get('cached_key');
        $this->assertEquals('updated_value', $value3);
    }

    /** @test */
    public function it_can_set_setting_value()
    {
        // اختبار تعيين قيمة جديدة
        $setting = $this->settingsService->set('new_key', 'new_value');
        
        // التحقق من وجود الإعداد في قاعدة البيانات
        $this->assertDatabaseHas('settings', [
            'key' => 'new_key',
            'value' => 'new_value',
        ]);
        
        // التحقق من أن الكاش تم تنظيفه
        $this->assertFalse(Cache::has('settings:new_key'));
        
        // اختبار تحديث قيمة موجودة
        $this->settingsService->set('new_key', 'updated_value');
        
        // التحقق من تحديث القيمة في قاعدة البيانات
        $this->assertDatabaseHas('settings', [
            'key' => 'new_key',
            'value' => 'updated_value',
        ]);
    }

    /** @test */
    public function it_can_get_settings_by_category()
    {
        // إنشاء إعدادات في فئة معينة
        Settings::create([
            'key' => 'general_key1',
            'category' => 'general',
            'value' => 'value1',
            'type' => 'string',
            'active' => true,
            'sort_order' => 1,
        ]);
        
        Settings::create([
            'key' => 'general_key2',
            'category' => 'general',
            'value' => 'value2',
            'type' => 'string',
            'active' => true,
            'sort_order' => 2,
        ]);
        
        Settings::create([
            'key' => 'security_key',
            'category' => 'security',
            'value' => 'security_value',
            'type' => 'string',
            'active' => true,
        ]);

        // اختبار الحصول على الإعدادات حسب الفئة
        $generalSettings = $this->settingsService->getByCategory('general');
        
        $this->assertIsArray($generalSettings);
        $this->assertCount(2, $generalSettings);
        $this->assertEquals('value1', $generalSettings['general_key1']);
        $this->assertEquals('value2', $generalSettings['general_key2']);
    }

    /** @test */
    public function it_can_get_public_settings()
    {
        // إنشاء إعدادات عامة وخاصة
        Settings::create([
            'key' => 'public_key1',
            'category' => 'general',
            'value' => 'public_value1',
            'type' => 'string',
            'is_public' => true,
            'active' => true,
        ]);
        
        Settings::create([
            'key' => 'public_key2',
            'category' => 'security',
            'value' => 'public_value2',
            'type' => 'string',
            'is_public' => true,
            'active' => true,
        ]);
        
        Settings::create([
            'key' => 'private_key',
            'category' => 'general',
            'value' => 'private_value',
            'type' => 'string',
            'is_public' => false,
            'active' => true,
        ]);

        // اختبار الحصول على الإعدادات العامة
        $publicSettings = $this->settingsService->getPublicSettings();
        
        $this->assertIsArray($publicSettings);
        $this->assertCount(2, $publicSettings); // عدد الفئات
        $this->assertArrayHasKey('general', $publicSettings);
        $this->assertArrayHasKey('security', $publicSettings);
        $this->assertEquals('public_value1', $publicSettings['general']['public_key1']);
        $this->assertEquals('public_value2', $publicSettings['security']['public_key2']);
        
        // التأكد من عدم وجود الإعدادات الخاصة
        $this->assertArrayNotHasKey('private_key', $publicSettings['general']);
    }

    /** @test */
    public function it_can_update_multiple_settings()
    {
        // إنشاء إعدادات للاختبار
        Settings::create([
            'key' => 'bulk_key1',
            'category' => 'general',
            'value' => 'old_value1',
            'type' => 'string',
            'active' => true,
        ]);
        
        Settings::create([
            'key' => 'bulk_key2',
            'category' => 'general',
            'value' => 'old_value2',
            'type' => 'string',
            'active' => true,
        ]);

        // اختبار تحديث متعدد
        $result = $this->settingsService->updateMultiple([
            'bulk_key1' => 'new_value1',
            'bulk_key2' => 'new_value2',
            'bulk_key3' => 'new_value3', // إعداد جديد
        ]);
        
        $this->assertTrue($result);
        
        // التحقق من تحديث القيم في قاعدة البيانات
        $this->assertDatabaseHas('settings', [
            'key' => 'bulk_key1',
            'value' => 'new_value1',
        ]);
        
        $this->assertDatabaseHas('settings', [
            'key' => 'bulk_key2',
            'value' => 'new_value2',
        ]);
        
        $this->assertDatabaseHas('settings', [
            'key' => 'bulk_key3',
            'value' => 'new_value3',
        ]);
    }

    /** @test */
    public function it_can_upload_file()
    {
        // إنشاء ملف وهمي
        $file = UploadedFile::fake()->image('test_image.jpg');
        
        // اختبار تحميل الملف
        $path = $this->settingsService->uploadFile($file, 'logo');
        
        // التحقق من وجود الملف في التخزين
        Storage::disk('public')->assertExists($path);
        
        // التحقق من وجود الإعداد في قاعدة البيانات
        $this->assertDatabaseHas('settings', [
            'key' => 'logo',
            'value' => $path,
        ]);
    }

    /** @test */
    public function it_can_create_or_update_setting()
    {
        // اختبار إنشاء إعداد جديد
        $data = [
            'key' => 'full_setting',
            'category' => 'general',
            'value' => 'full_value',
            'type' => 'string',
            'description' => 'Full setting description',
            'is_public' => true,
            'is_encrypted' => false,
            'sort_order' => 5,
            'active' => true,
        ];
        
        $setting = $this->settingsService->createOrUpdate($data);
        
        // التحقق من وجود الإعداد في قاعدة البيانات
        $this->assertDatabaseHas('settings', [
            'key' => 'full_setting',
            'category' => 'general',
            'description' => 'Full setting description',
        ]);
        
        // اختبار تحديث الإعداد
        $data['value'] = 'updated_value';
        $data['description'] = 'Updated description';
        
        $updatedSetting = $this->settingsService->createOrUpdate($data);
        
        // التحقق من تحديث الإعداد في قاعدة البيانات
        $this->assertDatabaseHas('settings', [
            'key' => 'full_setting',
            'value' => 'updated_value',
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function it_can_delete_setting()
    {
        // إنشاء إعداد للحذف
        Settings::create([
            'key' => 'delete_key',
            'category' => 'general',
            'value' => 'delete_value',
            'type' => 'string',
            'active' => true,
        ]);
        
        // اختبار حذف الإعداد
        $result = $this->settingsService->delete('delete_key');
        
        $this->assertTrue($result);
        
        // التحقق من حذف الإعداد من قاعدة البيانات
        $this->assertDatabaseMissing('settings', [
            'key' => 'delete_key',
        ]);
    }

    /** @test */
    public function it_deletes_file_when_deleting_file_setting()
    {
        // إنشاء ملف وهمي
        $file = UploadedFile::fake()->image('test_file.jpg');
        $path = $file->store('settings', 'public');
        
        // إنشاء إعداد ملف
        Settings::create([
            'key' => 'file_key',
            'category' => 'files',
            'value' => $path,
            'type' => 'file',
            'active' => true,
        ]);
        
        // التحقق من وجود الملف
        Storage::disk('public')->assertExists($path);
        
        // حذف الإعداد
        $result = $this->settingsService->delete('file_key');
        
        $this->assertTrue($result);
        
        // التحقق من حذف الملف
        Storage::disk('public')->assertMissing($path);
    }

    /** @test */
    public function it_can_get_settings_for_admin()
    {
        // إنشاء إعدادات متنوعة
        Settings::create([
            'key' => 'admin_key1',
            'category' => 'general',
            'value' => 'admin_value1',
            'type' => 'string',
            'active' => true,
            'sort_order' => 1,
        ]);
        
        Settings::create([
            'key' => 'admin_key2',
            'category' => 'security',
            'value' => 'admin_value2',
            'type' => 'string',
            'active' => true,
            'sort_order' => 1,
        ]);
        
        Settings::create([
            'key' => 'search_key',
            'category' => 'general',
            'value' => 'search_value',
            'description' => 'Searchable description',
            'type' => 'string',
            'active' => true,
        ]);

        // اختبار الحصول على الإعدادات بدون تصفية
        $allSettings = $this->settingsService->getForAdmin();
        $this->assertEquals(3, $allSettings->count());
        
        // اختبار التصفية حسب الفئة
        $generalSettings = $this->settingsService->getForAdmin(['category' => 'general']);
        $this->assertEquals(2, $generalSettings->count());
        
        // اختبار البحث
        $searchResults = $this->settingsService->getForAdmin(['search' => 'search']);
        $this->assertEquals(1, $searchResults->count());
        $this->assertEquals('search_key', $searchResults->first()->key);
    }

    /** @test */
    public function it_can_get_categories()
    {
        // إنشاء إعدادات في فئات مختلفة
        Settings::create([
            'key' => 'cat_key1',
            'category' => 'general',
            'value' => 'value1',
            'type' => 'string',
            'active' => true,
        ]);
        
        Settings::create([
            'key' => 'cat_key2',
            'category' => 'security',
            'value' => 'value2',
            'type' => 'string',
            'active' => true,
        ]);
        
        Settings::create([
            'key' => 'cat_key3',
            'category' => 'mail',
            'value' => 'value3',
            'type' => 'string',
            'active' => true,
        ]);

        // اختبار الحصول على الفئات
        $categories = $this->settingsService->getCategories();
        
        $this->assertIsArray($categories);
        $this->assertCount(3, $categories);
        $this->assertContains('general', $categories);
        $this->assertContains('security', $categories);
        $this->assertContains('mail', $categories);
    }

    /** @test */
    public function it_can_clear_cache()
    {
        // إنشاء إعداد وتخزينه في الكاش
        Settings::create([
            'key' => 'cache_key',
            'category' => 'cache_category',
            'value' => 'cache_value',
            'type' => 'string',
            'active' => true,
        ]);
        
        // تخزين القيمة في الكاش
        $this->settingsService->get('cache_key');
        
        // اختبار تنظيف الكاش
        $reflectionClass = new \ReflectionClass(SettingsService::class);
        $method = $reflectionClass->getMethod('clearCache');
        $method->setAccessible(true);
        $method->invoke($this->settingsService, 'cache_key', 'cache_category');
        
        // التحقق من تنظيف الكاش
        $this->assertFalse(Cache::has('settings:cache_key'));
        $this->assertFalse(Cache::has('settings:category:cache_category'));
    }

    /** @test */
    public function it_can_clear_all_cache()
    {
        // إنشاء إعدادات وتخزينها في الكاش
        Settings::create([
            'key' => 'all_cache_key1',
            'category' => 'general',
            'value' => 'value1',
            'type' => 'string',
            'active' => true,
        ]);
        
        Settings::create([
            'key' => 'all_cache_key2',
            'category' => 'security',
            'value' => 'value2',
            'type' => 'string',
            'active' => true,
        ]);
        
        // تخزين القيم في الكاش
        $this->settingsService->get('all_cache_key1');
        $this->settingsService->get('all_cache_key2');
        $this->settingsService->getByCategory('general');
        
        // اختبار تنظيف كل الكاش
        $this->settingsService->clearAllCache();
        
        // التحقق من تنظيف الكاش
        $this->assertFalse(Cache::has('settings:all_cache_key1'));
        $this->assertFalse(Cache::has('settings:all_cache_key2'));
        $this->assertFalse(Cache::has('settings:category:general'));
    }
}
