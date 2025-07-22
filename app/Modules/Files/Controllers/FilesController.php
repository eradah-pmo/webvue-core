<?php

namespace App\Modules\Files\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Files\Models\File;
use App\Modules\Files\Requests\FileUploadRequest;
use App\Modules\Files\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilesController extends Controller
{
    protected FileUploadService $fileUploadService;
    
    /**
     * إنشاء مثيل جديد من وحدة التحكم.
     */
    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    
    /**
     * عرض قائمة الملفات.
     */
    public function index(Request $request): JsonResponse
    {
        $query = File::query();
        
        // فلترة حسب الموديول
        if ($request->has('module')) {
            $query->where('module', $request->module);
        }
        
        // فلترة حسب معرف الموديول
        if ($request->has('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        
        // فلترة حسب المستخدم
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $files = $query->latest()->paginate(15);
        
        return response()->json($files);
    }
    
    /**
     * رفع ملف جديد.
     */
    public function store(FileUploadRequest $request): JsonResponse
    {
        try {
            $file = $this->fileUploadService->upload(
                $request->file('file'),
                $request->user(),
                $request->input('module'),
                $request->input('module_id'),
                $request->boolean('is_public', false)
            );
            
            return response()->json([
                'success' => true,
                'message' => 'تم رفع الملف بنجاح',
                'file' => $file
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * عرض معلومات ملف محدد.
     */
    public function show(File $file): JsonResponse
    {
        return response()->json($file);
    }
    
    /**
     * تنزيل ملف.
     */
    public function download(File $file): StreamedResponse|JsonResponse
    {
        // التحقق من صلاحية الوصول
        if (!$file->is_public && $file->user_id !== auth()->id() && !auth()->user()->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتنزيل هذا الملف'
            ], 403);
        }
        
        if (!Storage::disk($file->disk)->exists($file->path)) {
            return response()->json([
                'success' => false,
                'message' => 'الملف غير موجود'
            ], 404);
        }
        
        return Storage::disk($file->disk)->download(
            $file->path, 
            $file->original_name
        );
    }
    
    /**
     * حذف ملف.
     */
    public function destroy(File $file): JsonResponse
    {
        // التحقق من صلاحية الحذف
        if ($file->user_id !== auth()->id() && !auth()->user()->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا الملف'
            ], 403);
        }
        
        try {
            $this->fileUploadService->delete($file);
            
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الملف بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
