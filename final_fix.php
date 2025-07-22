<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "Starting final permission fix...\n";

// Clear all permission-related data
DB::statement('DELETE FROM model_has_permissions');
DB::statement('DELETE FROM model_has_roles');
DB::statement('DELETE FROM role_has_permissions');
DB::statement('DELETE FROM permissions');
DB::statement('DELETE FROM roles');

echo "Cleared existing permissions and roles...\n";

// Create permissions
$permissions = [
    'dashboard.view',
    'users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage',
    'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.manage',
    'departments.view', 'departments.create', 'departments.edit', 'departments.delete', 'departments.manage',
    'settings.view', 'settings.edit', 'settings.manage',
    'audit-logs.view', 'audit-logs.export',
];

$permissionIds = [];
foreach ($permissions as $permission) {
    $id = DB::table('permissions')->insertGetId([
        'name' => $permission,
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    $permissionIds[$permission] = $id;
}

echo "Created " . count($permissions) . " permissions...\n";

// Create roles
$roles = [
    'super-admin' => $permissions, // All permissions
    'admin' => ['dashboard.view', 'users.view', 'users.create', 'users.edit', 'roles.view', 'departments.view', 'settings.view'],
    'manager' => ['dashboard.view', 'users.view', 'departments.view'],
    'user' => ['dashboard.view']
];

$roleIds = [];
foreach ($roles as $roleName => $rolePermissions) {
    $roleId = DB::table('roles')->insertGetId([
        'name' => $roleName,
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    $roleIds[$roleName] = $roleId;
    
    // Assign permissions to role
    foreach ($rolePermissions as $permission) {
        if (isset($permissionIds[$permission])) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionIds[$permission],
                'role_id' => $roleId
            ]);
        }
    }
}

echo "Created " . count($roles) . " roles with permissions...\n";

// Ensure admin user exists and has correct password
$adminUser = DB::table('users')->where('email', 'admin@example.com')->first();
if (!$adminUser) {
    $adminUserId = DB::table('users')->insertGetId([
        'name' => 'Super Admin',
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'email' => 'admin@example.com',
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
        'active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "Created admin user...\n";
} else {
    $adminUserId = $adminUser->id;
    // Update password to ensure it's correct
    DB::table('users')->where('id', $adminUserId)->update([
        'password' => Hash::make('password'),
        'active' => true,
        'updated_at' => now()
    ]);
    echo "Updated admin user password...\n";
}

// Assign super-admin role to admin user
DB::table('model_has_roles')->insert([
    'role_id' => $roleIds['super-admin'],
    'model_type' => 'App\\Models\\User',
    'model_id' => $adminUserId
]);

echo "Assigned super-admin role to admin user...\n";

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

echo "Cleared permission cache...\n";
echo "✅ Final permission fix completed successfully!\n";
echo "You can now login with: admin@example.com / password\n";
