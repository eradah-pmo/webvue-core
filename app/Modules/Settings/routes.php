<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Settings\Controllers\SettingsController;

// Settings management routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Standard CRUD routes
    Route::resource('settings', SettingsController::class);
    
    // Additional API routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::post('update-multiple', [SettingsController::class, 'updateMultiple'])
            ->name('update-multiple');
        Route::post('clear-cache', [SettingsController::class, 'clearCache'])
            ->name('clear-cache');
    });
});

// Public API routes (no auth required)
Route::prefix('api/settings')->name('api.settings.')->group(function () {
    Route::get('public', [SettingsController::class, 'getPublic'])
        ->name('public');
});