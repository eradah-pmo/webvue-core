<?php

use App\Modules\Files\Controllers\FilesController;
use Illuminate\Support\Facades\Route;

// مسارات واجهة المستخدم (Web)
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    // تنزيل الملفات (يحتاج إلى مصادقة)
    Route::get('files/{file}/download', [FilesController::class, 'download'])
        ->name('files.download');
});

// مسارات API
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('api/v1')->group(function () {
    // مسارات الملفات (تحتاج إلى صلاحية files.view)
    Route::apiResource('files', FilesController::class)
        ->middleware('can:files.view');
        
    // رفع ملف (يحتاج إلى صلاحية files.create)
    Route::post('files/upload', [FilesController::class, 'store'])
        ->name('api.files.upload')
        ->middleware(['can:files.create', 'throttle:20,1']);
});
