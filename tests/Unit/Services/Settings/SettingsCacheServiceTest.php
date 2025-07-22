<?php

namespace Tests\Unit\Services\Settings;

use Tests\TestCase;
use App\Modules\Settings\Models\Settings;
use App\Modules\Settings\Services\SettingsCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class SettingsCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SettingsCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cacheService = app(SettingsCacheService::class);
        Cache::flush();
    }

    /** @test */
    public function it_can_remember_setting_value()
    {
        // إنشاء إعداد اختبار
        Settings::create([
            'key' => 'test_key',
            'category' => 'general',
            'value' => 'test_value',
            'type' => 'string',
            'active' => true,
        ]);

        $value = $this->cacheService->remember('test_key', function () {
            return Settings::getValue('test_key');
        });

        $this->assertEquals('test_value', $value);
        
        // التحقق من وجود القيمة في الكاش
        $this->assertTrue(Cache::has('settings.test_key'));
    }

    /** @test */
    public function it_can_clear_specific_setting_cache()
    {
        // إضافة قيمة للكاش
        Cache::put('settings.test_key', 'cached_value', 3600);
        $this->assertTrue(Cache::has('settings.test_key'));

        // مسح الكاش
        $this->cacheService->clearSettingCache('test_key');
        
        $this->assertFalse(Cache::has('settings.test_key'));
    }

    /** @test */
    public function it_can_clear_category_cache()
    {
        // إضافة عدة قيم للكاش
        Cache::put('settings.general.key1', 'value1', 3600);
        Cache::put('settings.general.key2', 'value2', 3600);
        Cache::put('settings.other.key3', 'value3', 3600);

        // مسح كاش فئة معينة
        $this->cacheService->clearSettingCache(null, 'general');
        
        $this->assertFalse(Cache::has('settings.general.key1'));
        $this->assertFalse(Cache::has('settings.general.key2'));
        $this->assertTrue(Cache::has('settings.other.key3'));
    }

    /** @test */
    public function it_can_clear_all_settings_cache()
    {
        // إضافة عدة قيم للكاش
        Cache::put('settings.key1', 'value1', 3600);
        Cache::put('settings.key2', 'value2', 3600);
        Cache::put('other.key', 'value', 3600);

        $this->cacheService->clearAllCache();
        
        $this->assertFalse(Cache::has('settings.key1'));
        $this->assertFalse(Cache::has('settings.key2'));
        $this->assertTrue(Cache::has('other.key')); // لا يتأثر بمسح إعدادات
    }

    /** @test */
    public function it_generates_correct_cache_key()
    {
        $key = $this->cacheService->getCacheKey('test_key');
        $this->assertEquals('settings.test_key', $key);
    }

    /** @test */
    public function it_handles_empty_key_gracefully()
    {
        $this->cacheService->clearSettingCache('');
        $this->cacheService->clearSettingCache(null);
        
        // لا يجب أن يحدث خطأ
        $this->assertTrue(true);
    }

    /** @test */
    public function it_respects_cache_ttl()
    {
        Settings::create([
            'key' => 'ttl_test',
            'category' => 'general',
            'value' => 'test_value',
            'type' => 'string',
            'active' => true,
        ]);

        $value = $this->cacheService->remember('ttl_test', function () {
            return Settings::getValue('ttl_test');
        }, 1); // TTL = 1 ثانية

        $this->assertEquals('test_value', $value);
        $this->assertTrue(Cache::has('settings.ttl_test'));

        // انتظار انتهاء صلاحية الكاش
        sleep(2);
        $this->assertFalse(Cache::has('settings.ttl_test'));
    }
}
