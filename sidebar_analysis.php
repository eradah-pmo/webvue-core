<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "=== COMPREHENSIVE SIDEBAR ANALYSIS ===\n\n";

// 1. Check User Data
echo "1. USER DATA ANALYSIS:\n";
echo "------------------------\n";
$user = User::where('email', 'admin@example.com')->first();
if ($user) {
    echo "✅ User found: {$user->name} ({$user->email})\n";
    echo "   - ID: {$user->id}\n";
    echo "   - Active: " . ($user->active ? 'YES' : 'NO') . "\n";
    echo "   - Email Verified: " . ($user->email_verified_at ? 'YES' : 'NO') . "\n";
} else {
    echo "❌ Admin user not found!\n";
    exit(1);
}

// 2. Check Roles
echo "\n2. ROLES ANALYSIS:\n";
echo "-------------------\n";
$userRoles = $user->roles;
if ($userRoles->count() > 0) {
    echo "✅ User has " . $userRoles->count() . " role(s):\n";
    foreach ($userRoles as $role) {
        echo "   - {$role->name} (guard: {$role->guard_name})\n";
    }
} else {
    echo "❌ User has no roles assigned!\n";
}

// 3. Check Permissions
echo "\n3. PERMISSIONS ANALYSIS:\n";
echo "-------------------------\n";
$allPermissions = $user->getAllPermissions();
if ($allPermissions->count() > 0) {
    echo "✅ User has " . $allPermissions->count() . " permission(s):\n";
    foreach ($allPermissions as $permission) {
        echo "   - {$permission->name}\n";
    }
} else {
    echo "❌ User has no permissions!\n";
}

// 4. Check Required Permissions for Sidebar
echo "\n4. SIDEBAR PERMISSIONS CHECK:\n";
echo "------------------------------\n";
$requiredPermissions = [
    'dashboard.view',
    'users.view', 
    'roles.view',
    'departments.view',
    'settings.view'
];

foreach ($requiredPermissions as $permission) {
    $hasPermission = $user->can($permission);
    $status = $hasPermission ? '✅' : '❌';
    echo "{$status} {$permission}: " . ($hasPermission ? 'ALLOWED' : 'DENIED') . "\n";
}

// 5. Check if user is super-admin
echo "\n5. SUPER ADMIN CHECK:\n";
echo "----------------------\n";
$isSuperAdmin = $user->hasRole('super-admin');
echo ($isSuperAdmin ? '✅' : '❌') . " Is Super Admin: " . ($isSuperAdmin ? 'YES' : 'NO') . "\n";

// 6. Check Database Tables
echo "\n6. DATABASE TABLES CHECK:\n";
echo "---------------------------\n";
$tablesCheck = [
    'users' => DB::table('users')->count(),
    'roles' => DB::table('roles')->count(),
    'permissions' => DB::table('permissions')->count(),
    'model_has_roles' => DB::table('model_has_roles')->count(),
    'role_has_permissions' => DB::table('role_has_permissions')->count(),
];

foreach ($tablesCheck as $table => $count) {
    echo "✅ {$table}: {$count} records\n";
}

// 7. Expected vs Actual Data for Inertia
echo "\n7. INERTIA DATA STRUCTURE:\n";
echo "---------------------------\n";
$expectedData = [
    'user' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'roles' => $user->roles->pluck('name')->toArray(),
        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
    ]
];

echo "Expected Inertia auth.user structure:\n";
echo json_encode($expectedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

echo "\n=== ANALYSIS COMPLETE ===\n";
echo "\nRECOMMENDATIONS:\n";
echo "1. Check browser console for 'Sidebar Debug' messages\n";
echo "2. Verify that auth.user data matches expected structure\n";
echo "3. Ensure user has super-admin role or required permissions\n";
echo "4. Check that Sidebar component receives correct props\n";
