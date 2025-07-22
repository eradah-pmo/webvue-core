<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Departments\Controllers\DepartmentsController;

// Departments Management Routes
Route::middleware(['auth', 'can:departments.view'])->group(function () {
    Route::resource('departments', DepartmentsController::class);
    
    // Additional department actions
    Route::post('departments/{department}/toggle-status', [DepartmentsController::class, 'toggleStatus'])
        ->name('departments.toggle-status')
        ->middleware('can:departments.edit');
        
    Route::post('departments/{department}/move', [DepartmentsController::class, 'move'])
        ->name('departments.move')
        ->middleware('can:departments.edit');
        
    Route::post('departments/reorder', [DepartmentsController::class, 'reorder'])
        ->name('departments.reorder')
        ->middleware('can:departments.edit');
        
    Route::get('departments/search/api', [DepartmentsController::class, 'search'])
        ->name('departments.search');
});