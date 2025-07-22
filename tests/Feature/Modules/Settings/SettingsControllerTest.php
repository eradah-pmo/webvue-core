<?php

namespace Tests\Feature\Modules\Settings;

use Tests\TestCase;
use App\Modules\Settings\Models\Settings;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Modules\Users\Models\User;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $admin;
    protected $user;
    protected $settingsService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions
        $permissions = [
            'settings.view', 'settings.create', 'settings.edit', 'settings.delete',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Create roles
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        
        // Assign permissions
        $superAdminRole->givePermissionTo($permissions);
        $adminRole->givePermissionTo(['settings.view', 'settings.create', 'settings.edit']);
        
        // Create test users
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        
        // Initialize settings service
        $this->settingsService = app(SettingsService::class);
        
        // Create test settings
        Settings::create([
            'key' => 'site_name',
            'category' => 'general',
            'value' => 'Test Site',
            'type' => 'string',
            'description' => 'Site name',
            'is_public' => true,
            'is_encrypted' => false,
            'active' => true,
        ]);
        
        Settings::create([
            'key' => 'admin_email',
            'category' => 'mail',
            'value' => 'admin@example.com',
            'type' => 'string',
            'description' => 'Admin email',
            'is_public' => false,
            'is_encrypted' => false,
            'active' => true,
        ]);
        
        // Clear cache before tests
        Cache::flush();
    }

    /** @test */
    public function super_admin_can_view_settings_index()
    {
        $this->actingAs($this->superAdmin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertInertia(fn($page) => 
                $page->component('Settings/Index')
                    ->has('settings')
                    ->has('categories')
            );
    }

    /** @test */
    public function unauthorized_user_cannot_view_settings_index()
    {
        $unauthorizedUser = User::factory()->create();
        
        $this->actingAs($unauthorizedUser)
            ->get(route('settings.index'))
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_create_new_setting()
    {
        $settingData = [
            'key' => 'new_setting',
            'category' => 'general',
            'value' => 'new_value',
            'type' => 'string',
            'description' => 'New setting description',
            'is_public' => true,
            'is_encrypted' => false,
            'sort_order' => 1,
            'active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('settings.store'), $settingData)
            ->assertJson([
                'success' => true,
                'message' => __('settings.created_successfully'),
            ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'new_setting',
            'category' => 'general',
            'description' => 'New setting description',
        ]);
    }

    /** @test */
    public function setting_creation_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('settings.store'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['key', 'category', 'type']);
    }

    /** @test */
    public function setting_creation_validates_unique_key()
    {
        $this->actingAs($this->admin)
            ->post(route('settings.store'), [
                'key' => 'site_name', // Already exists
                'category' => 'general',
                'value' => 'Duplicate Key',
                'type' => 'string',
                'description' => 'Duplicate key test',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['key']);
    }

    /** @test */
    public function admin_can_update_setting()
    {
        $setting = Settings::where('key', 'site_name')->first();
        
        $updateData = [
            'key' => 'site_name',
            'category' => 'general',
            'value' => 'Updated Site Name',
            'type' => 'string',
            'description' => 'Updated description',
            'is_public' => true,
            'is_encrypted' => false,
            'active' => true,
        ];

        $this->actingAs($this->admin)
            ->put(route('settings.update', $setting), $updateData)
            ->assertJson([
                'success' => true,
                'message' => __('settings.updated_successfully'),
            ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'Updated Site Name',
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function super_admin_can_delete_setting()
    {
        $setting = Settings::where('key', 'site_name')->first();
        
        $this->actingAs($this->superAdmin)
            ->delete(route('settings.destroy', $setting))
            ->assertJson([
                'success' => true,
                'message' => __('settings.deleted_successfully'),
            ]);

        $this->assertDatabaseMissing('settings', [
            'key' => 'site_name',
        ]);
    }

    /** @test */
    public function admin_cannot_delete_setting()
    {
        $setting = Settings::where('key', 'site_name')->first();
        
        $this->actingAs($this->admin)
            ->delete(route('settings.destroy', $setting))
            ->assertForbidden();

        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
        ]);
    }

    /** @test */
    public function file_upload_works_correctly()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('logo.jpg', 100, 100)->size(500);
        
        $settingData = [
            'key' => 'site_logo',
            'category' => 'general',
            'type' => 'file',
            'description' => 'Site logo',
            'is_public' => true,
            'is_encrypted' => false,
            'active' => true,
            'file' => $file,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('settings.store'), $settingData)
            ->assertJson([
                'success' => true,
            ]);
            
        $responseData = json_decode($response->getContent(), true);
        $filePath = $responseData['setting']['value'];
        
        Storage::disk('public')->assertExists($filePath);
    }

    /** @test */
    public function update_multiple_settings_works()
    {
        $settingsData = [
            'settings' => [
                'site_name' => 'Bulk Updated Name',
                'admin_email' => 'bulk@example.com',
            ]
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('settings.update-multiple'), $settingsData)
            ->assertJson([
                'success' => true,
                'message' => __('settings.updated_successfully'),
            ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'Bulk Updated Name',
        ]);
        
        $this->assertDatabaseHas('settings', [
            'key' => 'admin_email',
            'value' => 'bulk@example.com',
        ]);
    }

    /** @test */
    public function get_public_settings_works()
    {
        $response = $this->get(route('settings.public'))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);
            
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('general', $responseData['settings']);
        $this->assertArrayHasKey('site_name', $responseData['settings']['general']);
        $this->assertArrayNotHasKey('mail', $responseData['settings']); // Private settings should not be included
    }

    /** @test */
    public function clear_cache_works()
    {
        // First access to cache the settings
        $this->settingsService->get('site_name');
        
        $this->assertTrue(Cache::has('settings:site_name'));
        
        $this->actingAs($this->superAdmin)
            ->post(route('settings.clear-cache'))
            ->assertJson([
                'success' => true,
                'message' => __('settings.cache_cleared'),
            ]);
            
        $this->assertFalse(Cache::has('settings:site_name'));
    }

    /** @test */
    public function encrypted_settings_are_stored_encrypted()
    {
        $settingData = [
            'key' => 'api_secret',
            'category' => 'security',
            'value' => 'secret-value-123',
            'type' => 'string',
            'description' => 'API Secret Key',
            'is_public' => false,
            'is_encrypted' => true,
            'active' => true,
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('settings.store'), $settingData);
            
        // Get the raw value from database
        $encryptedValue = \DB::table('settings')
            ->where('key', 'api_secret')
            ->value('value');
            
        $this->assertNotEquals('secret-value-123', $encryptedValue);
        
        // But the service should decrypt it
        $decryptedValue = $this->settingsService->get('api_secret');
        $this->assertEquals('secret-value-123', $decryptedValue);
    }

    /** @test */
    public function activity_is_logged_on_setting_creation()
    {
        $settingData = [
            'key' => 'activity_test',
            'category' => 'general',
            'value' => 'test_value',
            'type' => 'string',
            'description' => 'Activity test',
            'is_public' => true,
            'is_encrypted' => false,
            'active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('settings.store'), $settingData);

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $this->admin->id,
            'subject_type' => Settings::class,
            'description' => 'created',
        ]);
    }
}
