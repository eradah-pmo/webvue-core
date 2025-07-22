<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Roles\Controllers\RolesController;
use App\Modules\Roles\Controllers\RolesExportController;
use App\Modules\Roles\Controllers\RolesPermissionController;

// Roles Management Routes
Route::middleware(['auth', 'active-module:roles'])->group(function () {
    // Main roles resource routes
    Route::resource('roles', RolesController::class);
    
    // Additional role actions
    Route::post('roles/{role}/toggle-status', [RolesController::class, 'toggleStatus'])
        ->name('roles.toggle-status')
        ->middleware('can:roles.edit');
        
    Route::post('roles/{role}/duplicate', [RolesController::class, 'duplicate'])
        ->name('roles.duplicate')
        ->middleware('can:roles.create');
        
    // Export routes
    Route::prefix('roles-export')->name('roles.export.')->group(function() {
        Route::get('csv', [RolesExportController::class, 'csv'])
            ->name('csv')
            ->middleware('can:roles.export');
            
        Route::get('pdf', [RolesExportController::class, 'pdf'])
            ->name('pdf')
            ->middleware('can:roles.export');
    });
    
    // Permission management routes
    Route::prefix('roles-permissions')->name('roles.permissions.')->group(function() {
        Route::get('all', [RolesPermissionController::class, 'getAllPermissions'])
            ->name('all')
            ->middleware('can:roles.view');
            
        Route::get('grouped', [RolesPermissionController::class, 'getGroupedPermissions'])
            ->name('grouped')
            ->middleware('can:roles.view');
            
        Route::put('{role}/sync', [RolesPermissionController::class, 'updatePermissions'])
            ->name('sync')
            ->middleware('can:roles.edit');
            
        Route::post('{role}/add', [RolesPermissionController::class, 'addPermissions'])
            ->name('add')
            ->middleware('can:roles.edit');
            
        Route::delete('{role}/remove', [RolesPermissionController::class, 'removePermissions'])
            ->name('remove')
            ->middleware('can:roles.edit');
    });
});