<?php

use App\Modules\AuditLogs\Controllers\AuditLogsController;
use App\Modules\AuditLogs\Controllers\AuditLogsDashboardController;
use App\Modules\AuditLogs\Controllers\AuditLogsExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Audit Logs Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin', 'active-module:audit-logs'])
    ->prefix('audit-logs')
    ->name('audit-logs.')
    ->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AuditLogsDashboardController::class, 'index'])
        ->middleware('permission:audit-logs.dashboard')
        ->name('dashboard');
    
    // Main listing
    Route::get('/', [AuditLogsController::class, 'index'])
        ->middleware('permission:audit-logs.view')
        ->name('index');
    
    // Show details
    Route::get('/{id}', [AuditLogsController::class, 'show'])
        ->middleware('permission:audit-logs.view')
        ->name('show');
    
    // Export
    Route::post('/export', [AuditLogsExportController::class, 'export'])
        ->middleware('permission:audit-logs.export')
        ->name('export');
    
});
