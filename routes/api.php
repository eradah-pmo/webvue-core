<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes for modules
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('v1')->group(function () {
    // System status
    Route::get('status', function () {
        return response()->json(['status' => 'API is working', 'version' => '1.0.0']);
    });
    
    // Users API
    Route::apiResource('users', App\Modules\Users\Controllers\UsersController::class)
        ->middleware('can:users.view');
    Route::post('users/{user}/toggle-status', [App\Modules\Users\Controllers\UsersController::class, 'toggleStatus'])
        ->name('api.users.toggle-status')
        ->middleware('can:users.edit');
    Route::post('users/{user}/reset-password', [App\Modules\Users\Controllers\UsersController::class, 'resetPassword'])
        ->name('api.users.reset-password')
        ->middleware(['can:users.edit', 'throttle:10,1']);
        
    // Departments API
    Route::apiResource('departments', App\Modules\Departments\Controllers\DepartmentsController::class)
        ->middleware('can:departments.view');
    Route::post('departments/{department}/toggle-status', [App\Modules\Departments\Controllers\DepartmentsController::class, 'toggleStatus'])
        ->name('api.departments.toggle-status')
        ->middleware('can:departments.edit');
    Route::get('departments/search', [App\Modules\Departments\Controllers\DepartmentsController::class, 'search'])
        ->name('api.departments.search')
        ->middleware('can:departments.view');
        
    // Modules API (System administration)
    Route::middleware('can:modules.view')->prefix('modules')->group(function () {
        Route::get('/', [App\Http\Controllers\ModuleController::class, 'index']);
        Route::post('{module}/enable', [App\Http\Controllers\ModuleController::class, 'enable'])
            ->middleware(['can:modules.manage', 'throttle:10,1']);
        Route::post('{module}/disable', [App\Http\Controllers\ModuleController::class, 'disable'])
            ->middleware(['can:modules.manage', 'throttle:10,1']);
        Route::get('navigation', [App\Http\Controllers\ModuleController::class, 'navigation']);
        Route::get('dependencies', [App\Http\Controllers\ModuleController::class, 'dependencies']);
        Route::get('health', [App\Http\Controllers\ModuleController::class, 'health']);
        Route::post('clear-cache', [App\Http\Controllers\ModuleController::class, 'clearCache'])
            ->middleware(['can:modules.manage', 'throttle:10,1']);
    });
});
