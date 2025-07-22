<?php

namespace Tests\Feature\Modules\Settings;

use Tests\TestCase;
use App\Models\User;
use App\Modules\Settings\Models\Settings;
use App\Modules\Settings\Services\SettingsService;
use App\Modules\Settings\Services\SettingsCacheService;
use App\Modules\Settings\Services\SettingsFileService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SettingsIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $admin;
    protected $user;
    protected SettingsService $settingsService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // إنشاء الصلاحيات
        $permissions = [
            'settings.view', 'settings.edit', 'settings.manage-files'
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // إنشاء الأدوار
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        
        $superAdminRole->givePermissionTo($permissions);
        $adminRole->givePermissionTo(['settings.view', 'settings.edit']);
        
        // إنشاء المستخدمين
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        
        $this->settingsService = app(SettingsService::class);
        Storage::fake('public');
        Cache::flush();
    }

    /** @test */
    public function admin_can_view_settings_page()
    {
        $response = $this->actingAs($this->admin)
            ->get('/settings');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Settings/Index')
        );
    }

    /** @test */
    public function unauthorized_user_cannot_view_settings()
    {
        $response = $this->actingAs($this->user)
            ->get('/settings');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_text_setting()
    {
        $response = $this->actingAs($this->admin)
            ->put('/settings', [
                'site_name' => 'Updated Site Name',
                'site_description' => 'Updated description'
            ]);
        
        $response->assertRedirect('/settings');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'Updated Site Name'
        ]);
    }

    /** @test */
    public function settings_are_cached_after_retrieval()
    {
        Settings::create([
            'key' => 'cached_setting',
            'category' => 'general',
            'value' => 'cached_value',
            'type' => 'string',
            'active' => true
        ]);
        
        // أول استدعاء يجب أن يضع القيمة في الكاش
        $value = $this->settingsService->get('cached_setting');
        $this->assertEquals('cached_value', $value);
        
        // التحقق من وجود القيمة في الكاش
        $this->assertTrue(Cache::has('settings.cached_setting'));
    }

    /** @test */
    public function cache_is_cleared_when_setting_updated()
    {
        Settings::create([
            'key' => 'cache_test',
            'category' => 'general',
            'value' => 'original_value',
            'type' => 'string',
            'active' => true
        ]);
        
        // وضع القيمة في الكاش
        $this->settingsService->get('cache_test');
        $this->assertTrue(Cache::has('settings.cache_test'));
        
        // تحديث الإعداد
        $this->settingsService->set('cache_test', 'updated_value');
        
        // التحقق من مسح الكاش
        $this->assertFalse(Cache::has('settings.cache_test'));
    }

    /** @test */
    public function admin_can_upload_logo_file()
    {
        $logo = UploadedFile::fake()->image('logo.png', 200, 200);
        
        $response = $this->actingAs($this->admin)
            ->post('/settings/upload', [
                'key' => 'site_logo',
                'file' => $logo
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // التحقق من رفع الملف
        $setting = Settings::where('key', 'site_logo')->first();
        $this->assertNotNull($setting);
        $this->assertNotNull($setting->value);
        Storage::disk('public')->assertExists($setting->value);
    }

    /** @test */
    public function file_upload_validates_file_type()
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000);
        
        $response = $this->actingAs($this->admin)
            ->post('/settings/upload', [
                'key' => 'site_logo',
                'file' => $invalidFile
            ]);
        
        $response->assertSessionHasErrors(['file']);
    }

    /** @test */
    public function file_upload_validates_file_size()
    {
        $largeFile = UploadedFile::fake()->image('large.jpg', 2000, 2000)->size(15000); // 15MB
        
        $response = $this->actingAs($this->admin)
            ->post('/settings/upload', [
                'key' => 'site_logo',
                'file' => $largeFile
            ]);
        
        $response->assertSessionHasErrors(['file']);
    }

    /** @test */
    public function old_file_is_deleted_when_new_file_uploaded()
    {
        // رفع ملف أول
        $oldFile = UploadedFile::fake()->image('old_logo.png');
        $oldPath = $this->settingsService->uploadFile($oldFile, 'site_logo');
        
        Storage::disk('public')->assertExists($oldPath);
        
        // رفع ملف جديد
        $newFile = UploadedFile::fake()->image('new_logo.png');
        
        $response = $this->actingAs($this->admin)
            ->post('/settings/upload', [
                'key' => 'site_logo',
                'file' => $newFile
            ]);
        
        $response->assertRedirect();
        
        // التحقق من حذف الملف القديم
        Storage::disk('public')->assertMissing($oldPath);
        
        // التحقق من وجود الملف الجديد
        $setting = Settings::where('key', 'site_logo')->first();
        Storage::disk('public')->assertExists($setting->value);
    }

    /** @test */
    public function settings_can_be_grouped_by_category()
    {
        Settings::create(['key' => 'general_setting', 'category' => 'general', 'value' => 'value1', 'type' => 'string', 'active' => true]);
        Settings::create(['key' => 'email_setting', 'category' => 'email', 'value' => 'value2', 'type' => 'string', 'active' => true]);
        Settings::create(['key' => 'sms_setting', 'category' => 'sms', 'value' => 'value3', 'type' => 'string', 'active' => true]);
        
        $response = $this->actingAs($this->admin)
            ->get('/settings?category=email');
        
        $response->assertStatus(200);
        // التحقق من أن النتائج تحتوي على إعدادات البريد الإلكتروني فقط
    }

    /** @test */
    public function encrypted_settings_are_handled_properly()
    {
        $sensitiveValue = 'secret_api_key_12345';
        
        $this->actingAs($this->admin)
            ->put('/settings', [
                'api_key' => $sensitiveValue
            ]);
        
        // التحقق من أن القيمة مشفرة في قاعدة البيانات
        $setting = Settings::where('key', 'api_key')->first();
        $this->assertNotEquals($sensitiveValue, $setting->getRawOriginal('value'));
        
        // التحقق من أن القيمة تُفك تشفيرها عند الاستدعاء
        $retrievedValue = $this->settingsService->get('api_key');
        $this->assertEquals($sensitiveValue, $retrievedValue);
    }

    /** @test */
    public function boolean_settings_are_converted_properly()
    {
        $this->actingAs($this->admin)
            ->put('/settings', [
                'maintenance_mode' => true,
                'user_registration' => false
            ]);
        
        $this->assertTrue($this->settingsService->get('maintenance_mode'));
        $this->assertFalse($this->settingsService->get('user_registration'));
    }

    /** @test */
    public function json_settings_are_handled_correctly()
    {
        $jsonData = [
            'notifications' => [
                'email' => true,
                'sms' => false,
                'push' => true
            ]
        ];
        
        $this->actingAs($this->admin)
            ->put('/settings', [
                'notification_preferences' => json_encode($jsonData)
            ]);
        
        $retrieved = $this->settingsService->get('notification_preferences');
        $this->assertEquals($jsonData, json_decode($retrieved, true));
    }

    /** @test */
    public function settings_can_be_exported()
    {
        Settings::factory()->count(10)->create();
        
        $response = $this->actingAs($this->superAdmin)
            ->get('/settings/export');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    public function settings_can_be_imported()
    {
        $importData = [
            'site_name' => 'Imported Site Name',
            'site_description' => 'Imported Description',
            'contact_email' => 'imported@example.com'
        ];
        
        $response = $this->actingAs($this->superAdmin)
            ->post('/settings/import', [
                'settings' => json_encode($importData)
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        foreach ($importData as $key => $value) {
            $this->assertEquals($value, $this->settingsService->get($key));
        }
    }

    /** @test */
    public function settings_validation_works()
    {
        $response = $this->actingAs($this->admin)
            ->put('/settings', [
                'contact_email' => 'invalid-email',
                'max_upload_size' => 'not-a-number'
            ]);
        
        $response->assertSessionHasErrors(['contact_email', 'max_upload_size']);
    }

    /** @test */
    public function settings_activity_is_logged()
    {
        $this->actingAs($this->admin)
            ->put('/settings', [
                'site_name' => 'Logged Site Name'
            ]);
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Settings::class,
            'causer_id' => $this->admin->id,
            'description' => 'updated'
        ]);
    }

    /** @test */
    public function can_reset_settings_to_default()
    {
        // تعديل إعداد
        $this->settingsService->set('site_name', 'Modified Name');
        
        $response = $this->actingAs($this->superAdmin)
            ->post('/settings/reset', [
                'keys' => ['site_name']
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // التحقق من إعادة تعيين القيمة الافتراضية
        $defaultValue = $this->settingsService->get('site_name');
        $this->assertNotEquals('Modified Name', $defaultValue);
    }

    /** @test */
    public function settings_backup_can_be_created()
    {
        Settings::factory()->count(5)->create();
        
        $response = $this->actingAs($this->superAdmin)
            ->post('/settings/backup');
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // التحقق من إنشاء ملف النسخ الاحتياطي
        Storage::disk('local')->assertExists('backups/settings/' . now()->format('Y-m-d') . '.json');
    }
}
