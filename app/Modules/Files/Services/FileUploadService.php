<?php

namespace App\Modules\Files\Services;

use App\Modules\Files\Models\File;
use App\Modules\Users\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * الحد الأقصى لحجم الملف (10 ميجابايت)
     */
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    
    /**
     * أنواع الملفات المسموح بها
     */
    const ALLOWED_MIME_TYPES = [
        // صور
        'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp',
        // مستندات
        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        // نصوص
        'text/plain', 'text/csv',
        // أخرى
        'application/zip', 'application/x-rar-compressed'
    ];
    
    /**
     * رفع ملف وحفظ معلوماته في قاعدة البيانات
     *
     * @param UploadedFile $file الملف المرفوع
     * @param User $user المستخدم الذي قام بالرفع
     * @param string|null $module اسم الموديول المرتبط (اختياري)
     * @param int|null $moduleId معرف العنصر في الموديول (اختياري)
     * @param bool $isPublic هل الملف عام أم خاص
     * @param string $disk اسم القرص التخزيني
     * @return File|null
     */
    public function upload(
        UploadedFile $file,
        User $user,
        ?string $module = null,
        ?int $moduleId = null,
        bool $isPublic = false,
        string $disk = 'public'
    ): ?File {
        // التحقق من حجم الملف
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('حجم الملف يتجاوز الحد المسموح به (10 ميجابايت)');
        }
        
        // التحقق من نوع الملف
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new \Exception('نوع الملف غير مسموح به');
        }
        
        // إنشاء اسم فريد للملف
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // تحديد المسار
        $path = $module 
            ? "uploads/{$module}/" . ($moduleId ? "{$moduleId}/" : '')
            : "uploads/general/";
            
        // رفع الملف
        $filePath = $file->storeAs($path, $fileName, $disk);
        
        if (!$filePath) {
            throw new \Exception('فشل في رفع الملف');
        }
        
        // حفظ معلومات الملف في قاعدة البيانات
        return File::create([
            'name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'path' => $filePath,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'user_id' => $user->id,
            'module' => $module,
            'module_id' => $moduleId,
            'is_public' => $isPublic,
        ]);
    }
    
    /**
     * حذف ملف من التخزين وقاعدة البيانات
     *
     * @param File $file
     * @return bool
     */
    public function delete(File $file): bool
    {
        // حذف الملف من التخزين
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }
        
        // حذف السجل من قاعدة البيانات
        return $file->delete();
    }
}
