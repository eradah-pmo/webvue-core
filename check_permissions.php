<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== RBAC & Permissions Check ===\n\n";

// Check admin user
$user = User::where('email', 'admin@example.com')->first();

if ($user) {
    echo "✅ User Found: {$user->name} ({$user->email})\n";
    echo "📋 Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
    echo "🔑 Direct Permissions: " . $user->getDirectPermissions()->pluck('name')->join(', ') . "\n";
    echo "🔓 All Permissions: " . $user->getAllPermissions()->pluck('name')->join(', ') . "\n\n";
    
    // Check specific permissions for sidebar items
    $sidebarPermissions = [
        'dashboard.view',
        'users.view', 
        'roles.view',
        'departments.view',
        'settings.view'
    ];
    
    echo "=== Sidebar Permissions Check ===\n";
    foreach ($sidebarPermissions as $permission) {
        $hasPermission = $user->can($permission);
        echo ($hasPermission ? '✅' : '❌') . " {$permission}: " . ($hasPermission ? 'GRANTED' : 'DENIED') . "\n";
    }
    
    // Check if user is super admin
    echo "\n=== Role Check ===\n";
    $isSuperAdmin = $user->hasRole('super-admin');
    echo ($isSuperAdmin ? '✅' : '❌') . " Super Admin Role: " . ($isSuperAdmin ? 'YES' : 'NO') . "\n";
    
} else {
    echo "❌ Admin user not found!\n";
}

echo "\n=== Navigation Items Check ===\n";
$defaultNavigation = [
    ['name' => 'dashboard', 'permission' => 'dashboard.view'],
    ['name' => 'users', 'permission' => 'users.view'],
    ['name' => 'roles', 'permission' => 'roles.view'],
    ['name' => 'departments', 'permission' => 'departments.view'],
    ['name' => 'settings', 'permission' => 'settings.view'],
];

foreach ($defaultNavigation as $item) {
    $hasPermission = $user ? $user->can($item['permission']) : false;
    echo ($hasPermission ? '✅' : '❌') . " {$item['name']} ({$item['permission']}): " . ($hasPermission ? 'VISIBLE' : 'HIDDEN') . "\n";
}
