<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Create basic permissions if they don't exist
$permissions = [
    'dashboard.view',
    'users.view', 'users.create', 'users.edit', 'users.delete',
    'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
    'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
    'settings.view', 'settings.edit',
    'audit-logs.view'
];

foreach ($permissions as $permission) {
    Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
}

// Create roles if they don't exist
$roles = [
    'super-admin' => $permissions, // All permissions
    'admin' => ['dashboard.view', 'users.view', 'roles.view', 'departments.view', 'settings.view'],
    'manager' => ['dashboard.view', 'users.view', 'departments.view'],
    'user' => ['dashboard.view']
];

foreach ($roles as $roleName => $rolePermissions) {
    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    $role->syncPermissions($rolePermissions);
}

// Assign super-admin role to admin user
$adminUser = User::where('email', 'admin@example.com')->first();
if ($adminUser) {
    $adminUser->assignRole('super-admin');
    echo "Super Admin role assigned to admin@example.com\n";
} else {
    echo "Admin user not found\n";
}

echo "Permissions fixed successfully!\n";
