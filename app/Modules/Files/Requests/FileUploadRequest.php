<?php

namespace App\Modules\Files\Requests;

use App\Modules\Files\Services\FileUploadService;
use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    /**
     * تحديد ما إذا كان المستخدم مصرح له بإجراء هذا الطلب.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * الحصول على قواعد التحقق التي تنطبق على الطلب.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxSize = FileUploadService::MAX_FILE_SIZE / 1024; // تحويل إلى كيلوبايت
        $allowedMimeTypes = implode(',', FileUploadService::ALLOWED_MIME_TYPES);
        
        return [
            'file' => [
                'required',
                'file',
                "max:{$maxSize}",
                "mimes:{$allowedMimeTypes}",
            ],
            'module' => 'nullable|string|max:50',
            'module_id' => 'nullable|integer|min:1',
            'is_public' => 'nullable|boolean',
        ];
    }
    
    /**
     * الحصول على رسائل الخطأ المخصصة لقواعد التحقق.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'يجب تحديد ملف للرفع',
            'file.file' => 'يجب أن يكون المدخل ملفًا صالحًا',
            'file.max' => 'حجم الملف يتجاوز الحد المسموح به (10 ميجابايت)',
            'file.mimes' => 'نوع الملف غير مسموح به',
            'module.string' => 'يجب أن يكون اسم الموديول نصًا',
            'module.max' => 'اسم الموديول طويل جدًا',
            'module_id.integer' => 'معرف الموديول يجب أن يكون رقمًا صحيحًا',
            'module_id.min' => 'معرف الموديول يجب أن يكون أكبر من صفر',
        ];
    }
}
