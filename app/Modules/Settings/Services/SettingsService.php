<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Settings;
use Illuminate\Http\UploadedFile;

class SettingsService
{
    protected SettingsCacheService $cacheService;
    protected SettingsFileService $fileService;

    public function __construct(
        SettingsCacheService $cacheService,
        SettingsFileService $fileService
    ) {
        $this->cacheService = $cacheService;
        $this->fileService = $fileService;
    }

    /**
     * Get setting value by key
     */
    public function get(string $key, $default = null)
    {
        return $this->cacheService->remember($key, function () use ($key, $default) {
            return Settings::getValue($key, $default);
        });
    }

    /**
     * Set setting value
     */
    public function set(string $key, $value): Settings
    {
        $setting = Settings::setValue($key, $value);
        $this->cacheService->clearSettingCache($key);
        
        return $setting;
    }

    /**
     * Get all settings by category
     */
    public function getByCategory(string $category): array
    {
        return $this->cacheService->remember('category:' . $category, function () use ($category) {
            return Settings::active()
                ->byCategory($category)
                ->orderBy('sort_order')
                ->get()
                ->keyBy('key')
                ->map(fn($setting) => $setting->value)
                ->toArray();
        });
    }

    /**
     * Get all public settings (for frontend)
     */
    public function getPublicSettings(): array
    {
        return $this->cacheService->remember('public', function () {
            return Settings::active()
                ->public()
                ->orderBy('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('category')
                ->map(function ($settings) {
                    return $settings->keyBy('key')->map(fn($setting) => $setting->value);
                })
                ->toArray();
        });
    }

    /**
     * Update multiple settings at once
     */
    public function updateMultiple(array $settings): bool
    {
        try {
            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Handle file upload for settings
     */
    public function uploadFile(UploadedFile $file, string $key): ?string
    {
        $path = $this->fileService->uploadFile($file, $key);
        if ($path) {
            $this->set($key, $path);
        }
        return $path;
    }

    /**
     * Create or update setting with full details
     */
    public function createOrUpdate(array $data): Settings
    {
        $setting = Settings::updateOrCreate(
            ['key' => $data['key']],
            $data
        );

        $this->cacheService->clearSettingCache($setting->key, $setting->category);
        return $setting;
    }

    /**
     * Delete setting
     */
    public function delete(string $key): bool
    {
        $setting = Settings::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }
        
        $this->deleteSettingFile($setting);
        $category = $setting->category;
        $setting->delete();
        
        $this->cacheService->clearSettingCache($key, $category);
        return true;
    }

    /**
     * Delete setting file if exists.
     */
    private function deleteSettingFile(Settings $setting): void
    {
        if ($setting->type === 'file' && $setting->value) {
            $this->fileService->deleteFile($setting->value);
        }
    }

    /**
     * Get settings for admin panel with pagination
     */
    public function getForAdmin(array $filters = [])
    {
        $query = Settings::query();
        
        if (isset($filters['category'])) {
            $query->byCategory($filters['category']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('key', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->orderBy('category')
                    ->orderBy('sort_order')
                    ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get available categories
     */
    public function getCategories(): array
    {
        return Settings::distinct('category')
                      ->pluck('category')
                      ->sort()
                      ->values()
                      ->toArray();
    }

    /**
     * Reset all settings cache
     */
    public function clearAllCache(): void
    {
        $this->cacheService->forgetAll();
    }


}