<?php

namespace Tests\Unit\Services\Settings;

use Tests\TestCase;
use App\Modules\Settings\Models\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_a_setting()
    {
        $setting = Settings::create([
            'key' => 'test_key',
            'category' => 'general',
            'value' => 'test_value',
            'type' => 'string',
            'description' => 'Test setting',
            'is_public' => true,
            'is_encrypted' => false,
            'sort_order' => 1,
            'active' => true,
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'test_key',
            'category' => 'general',
            'type' => 'string',
        ]);

        $this->assertEquals('test_value', $setting->value);
    }

    /** @test */
    public function it_casts_boolean_values_correctly()
    {
        $setting = Settings::create([
            'key' => 'test_boolean',
            'category' => 'general',
            'value' => 'true',
            'type' => 'boolean',
            'description' => 'Test boolean setting',
            'is_public' => true,
            'is_encrypted' => false,
            'active' => true,
        ]);

        $this->assertTrue($setting->value);

        $setting->update(['value' => 'false']);
        $setting->refresh();
        
        $this->assertFalse($setting->value);
    }

    /** @test */
    public function it_casts_number_values_correctly()
    {
        $setting = Settings::create([
            'key' => 'test_number',
            'category' => 'general',
            'value' => '123.45',
            'type' => 'number',
            'description' => 'Test number setting',
            'is_public' => true,
            'is_encrypted' => false,
            'active' => true,
        ]);

        $this->assertEquals(123.45, $setting->value);
        $this->assertIsFloat($setting->value);
    }

    /** @test */
    public function it_casts_json_values_correctly()
    {
        $jsonData = ['key1' => 'value1', 'key2' => 'value2'];
        $jsonString = json_encode($jsonData);

        $setting = Settings::create([
            'key' => 'test_json',
            'category' => 'general',
            'value' => $jsonString,
            'type' => 'json',
            'description' => 'Test JSON setting',
            'is_public' => true,
            'is_encrypted' => false,
            'active' => true,
        ]);

        $this->assertEquals($jsonData, $setting->value);
        $this->assertIsArray($setting->value);
        $this->assertEquals('value1', $setting->value['key1']);
    }

    /** @test */
    public function it_encrypts_and_decrypts_values_correctly()
    {
        $originalValue = 'sensitive_data';

        $setting = Settings::create([
            'key' => 'test_encrypted',
            'category' => 'security',
            'value' => $originalValue,
            'type' => 'string',
            'description' => 'Test encrypted setting',
            'is_public' => false,
            'is_encrypted' => true,
            'active' => true,
        ]);

        // Verify the value is encrypted in the database
        $encryptedValueInDb = $this->getDatabaseValue('settings', 'value', [
            'key' => 'test_encrypted'
        ]);
        
        $this->assertNotEquals($originalValue, $encryptedValueInDb);
        
        // Verify the model decrypts it automatically
        $this->assertEquals($originalValue, $setting->value);
        
        // Test updating encrypted value
        $newValue = 'new_sensitive_data';
        $setting->value = $newValue;
        $setting->save();
        
        $setting->refresh();
        $this->assertEquals($newValue, $setting->value);
    }

    /** @test */
    public function it_filters_by_active_scope()
    {
        // Create active and inactive settings
        Settings::create([
            'key' => 'active_setting',
            'category' => 'general',
            'value' => 'active_value',
            'type' => 'string',
            'active' => true,
        ]);

        Settings::create([
            'key' => 'inactive_setting',
            'category' => 'general',
            'value' => 'inactive_value',
            'type' => 'string',
            'active' => false,
        ]);

        $activeSettings = Settings::active()->get();
        
        $this->assertEquals(1, $activeSettings->count());
        $this->assertEquals('active_setting', $activeSettings->first()->key);
    }

    /** @test */
    public function it_filters_by_category_scope()
    {
        // Create settings in different categories
        Settings::create([
            'key' => 'general_setting',
            'category' => 'general',
            'value' => 'general_value',
            'type' => 'string',
            'active' => true,
        ]);

        Settings::create([
            'key' => 'security_setting',
            'category' => 'security',
            'value' => 'security_value',
            'type' => 'string',
            'active' => true,
        ]);

        $generalSettings = Settings::byCategory('general')->get();
        
        $this->assertEquals(1, $generalSettings->count());
        $this->assertEquals('general_setting', $generalSettings->first()->key);
    }

    /** @test */
    public function it_filters_by_public_scope()
    {
        // Create public and private settings
        Settings::create([
            'key' => 'public_setting',
            'category' => 'general',
            'value' => 'public_value',
            'type' => 'string',
            'is_public' => true,
            'active' => true,
        ]);

        Settings::create([
            'key' => 'private_setting',
            'category' => 'general',
            'value' => 'private_value',
            'type' => 'string',
            'is_public' => false,
            'active' => true,
        ]);

        $publicSettings = Settings::public()->get();
        
        $this->assertEquals(1, $publicSettings->count());
        $this->assertEquals('public_setting', $publicSettings->first()->key);
    }

    /** @test */
    public function it_can_get_and_set_values_statically()
    {
        // Test static getValue method
        Settings::create([
            'key' => 'static_test',
            'category' => 'general',
            'value' => 'static_value',
            'type' => 'string',
            'active' => true,
        ]);

        $value = Settings::getValue('static_test');
        $this->assertEquals('static_value', $value);
        
        // Test default value
        $defaultValue = Settings::getValue('non_existent', 'default');
        $this->assertEquals('default', $defaultValue);
        
        // Test static setValue method
        Settings::setValue('new_static_key', 'new_static_value');
        
        $this->assertDatabaseHas('settings', [
            'key' => 'new_static_key',
            'value' => 'new_static_value',
        ]);
        
        $newValue = Settings::getValue('new_static_key');
        $this->assertEquals('new_static_value', $newValue);
    }

    /**
     * Helper method to get raw database value
     */
    private function getDatabaseValue($table, $column, $where)
    {
        return \DB::table($table)
            ->where($where)
            ->value($column);
    }
}
