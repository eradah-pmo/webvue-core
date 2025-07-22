<?php

namespace App\Modules\Settings\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingsFileService
{
    /**
     * Handle file upload for settings.
     */
    public function uploadFile(UploadedFile $file, string $key): ?string
    {
        try {
            $path = $file->store('settings', 'public');
            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Delete file from storage.
     */
    public function deleteFile(string $path): bool
    {
        try {
            return Storage::disk('public')->delete($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if file exists.
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }

    /**
     * Get file URL.
     */
    public function getFileUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}
