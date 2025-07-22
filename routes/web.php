<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// I18n Test Route (for debugging)
Route::get('/i18n-test', function () {
    return Inertia::render('I18nTest');
});

// CSRF Token Refresh Route
Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
})->name('csrf.token');

// Sanctum CSRF Cookie Route (if not already defined)
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
})->middleware('web');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/api/dashboard/quick-stats', [App\Http\Controllers\DashboardController::class, 'quickStats'])
        ->name('dashboard.quick-stats');
    
    // Diagnostic Route - Test Navigation Without Permissions
    Route::get('/test-navigation', function () {
        $user = auth()->user();
        $permissionService = app(\App\Core\Services\PermissionService::class);
        
        // If no user is authenticated, get the first user for testing
        if (!$user) {
            $user = \App\Models\User::first();
            if ($user) {
                auth()->login($user);
                $message = 'Auto-logged in as: ' . $user->name . ' for testing purposes.';
            } else {
                return Inertia::render('TestNavigation', [
                    'user' => null,
                    'permissions' => [],
                    'directPermissions' => [],
                    'roles' => [],
                    'accessibleDepartments' => collect([]),
                    'manageableUsers' => 0,
                    'message' => 'No users found in database. Please run seeders first.',
                    'systemInfo' => [
                        'roleBasedPermissions' => 0,
                        'directPermissions' => 0,
                        'totalRoles' => 0,
                        'canViewUsers' => false,
                        'canDeleteUsers' => false,
                    ]
                ]);
            }
        } else {
            $message = 'Navigation test successful! User is authenticated.';
        }
        
        // Load user with relationships
        $user->load(['roles', 'department']);
        
        return Inertia::render('TestNavigation', [
            'user' => $user,
            'permissions' => $user->getPermissionsViaRoles()->pluck('name'),
            'directPermissions' => $user->getDirectPermissions()->pluck('name'),
            'roles' => $user->getRoleNames(),
            'accessibleDepartments' => $permissionService->getUserAccessibleDepartments($user),
            'manageableUsers' => $permissionService->getUserManageableUsers($user)->count(),
            'message' => $message,
            'systemInfo' => [
                'roleBasedPermissions' => $user->getPermissionsViaRoles()->count(),
                'directPermissions' => $user->getDirectPermissions()->count(),
                'totalRoles' => $user->roles->count(),
                'canViewUsers' => $permissionService->userHasPermission($user, 'users.view'),
                'canDeleteUsers' => $permissionService->userHasPermission($user, 'users.delete'),
            ]
        ]);
    })->name('test.navigation');

    // Profile Management (placeholder)
    Route::get('/profile', function () {
        return Inertia::render('Profile/Edit');
    })->name('profile.edit');

    // User Language Preference
    Route::post('/user/language', function (Illuminate\Http\Request $request) {
        $request->validate(['language' => 'required|in:en,ar']);
        
        $request->user()->update(['locale' => $request->language]);
        
        return back();
    })->name('user.language');
    
    // Users Module Routes
    Route::resource('users', App\Modules\Users\Controllers\UsersController::class)
        ->middleware('can:users.view');
    Route::post('users/{user}/toggle-status', [App\Modules\Users\Controllers\UsersController::class, 'toggleStatus'])
        ->name('users.toggle-status')
        ->middleware('can:users.edit');
    Route::post('users/{user}/reset-password', [App\Modules\Users\Controllers\UsersController::class, 'resetPassword'])
        ->name('users.reset-password')
        ->middleware('can:users.edit');
        
    // Departments Module Routes
    Route::resource('departments', App\Modules\Departments\Controllers\DepartmentsController::class)
        ->middleware('can:departments.view');
    Route::post('departments/{department}/toggle-status', [App\Modules\Departments\Controllers\DepartmentsController::class, 'toggleStatus'])
        ->name('departments.toggle-status')
        ->middleware('can:departments.edit');
    Route::get('departments/search', [App\Modules\Departments\Controllers\DepartmentsController::class, 'search'])
        ->name('departments.search')
        ->middleware('can:departments.view');
    
    // Audit Logs Module
    require __DIR__.'/../app/Modules/AuditLogs/routes.php';
});

require __DIR__.'/auth.php';
