<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Users\Controllers\UsersController;

// Users Management Routes
Route::middleware(['auth', 'can:users.view'])->group(function () {
    Route::resource('users', UsersController::class);
    
    // Additional user actions
    Route::post('users/{user}/toggle-status', [UsersController::class, 'toggleStatus'])
        ->name('users.toggle-status')
        ->middleware('can:users.edit');
        
    Route::post('users/{user}/reset-password', [UsersController::class, 'resetPassword'])
        ->name('users.reset-password')
        ->middleware('can:users.edit');
});