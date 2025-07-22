<?php

namespace App\Modules\Settings\Services;

use Illuminate\Support\Facades\Cache;

class SettingsCacheService
{
    private const CACHE_PREFIX = 'settings:';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get cached setting value.
     */
    public function remember(string $key, callable $callback)
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        return Cache::remember($cacheKey, self::CACHE_TTL, $callback);
    }

    /**
     * Clear setting cache.
     */
    public function forget(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Clear multiple cache keys.
     */
    public function forgetMultiple(array $keys): void
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Clear category cache.
     */
    public function forgetCategory(string $category): void
    {
        $this->forget('category:' . $category);
    }

    /**
     * Clear all settings cache.
     */
    public function forgetAll(): void
    {
        $this->forgetMultiple(['all', 'public']);
        
        $keys = Cache::getRedis()->keys(self::CACHE_PREFIX . '*');
        foreach ($keys as $key) {
            Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
        }
    }

    /**
     * Clear cache for setting and related caches.
     */
    public function clearSettingCache(string $key, ?string $category = null): void
    {
        $this->forgetMultiple([$key, 'all', 'public']);
        
        if ($category) {
            $this->forgetCategory($category);
        }
    }
}
