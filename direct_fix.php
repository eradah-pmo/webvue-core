<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Clear all permission-related tables
DB::statement('DELETE FROM model_has_permissions');
DB::statement('DELETE FROM model_has_roles');
DB::statement('DELETE FROM role_has_permissions');
DB::statement('DELETE FROM permissions');
DB::statement('DELETE FROM roles');

// Insert basic permissions directly
$permissions = [
    ['name' => 'dashboard.view', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'users.view', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'users.create', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'users.edit', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'users.delete', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'roles.view', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'roles.create', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'roles.edit', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'roles.delete', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'departments.view', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'departments.create', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'departments.edit', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'departments.delete', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'settings.view', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'settings.edit', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'audit-logs.view', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
];

DB::table('permissions')->insert($permissions);

// Insert roles directly
$roles = [
    ['name' => 'super-admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'manager', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'user', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
];

DB::table('roles')->insert($roles);

// Give super-admin all permissions
$superAdminRoleId = DB::table('roles')->where('name', 'super-admin')->first()->id;
$allPermissionIds = DB::table('permissions')->pluck('id');

foreach ($allPermissionIds as $permissionId) {
    DB::table('role_has_permissions')->insert([
        'permission_id' => $permissionId,
        'role_id' => $superAdminRoleId
    ]);
}

// Assign super-admin role to admin user
$adminUser = DB::table('users')->where('email', 'admin@example.com')->first();
if ($adminUser) {
    DB::table('model_has_roles')->insert([
        'role_id' => $superAdminRoleId,
        'model_type' => 'App\\Models\\User',
        'model_id' => $adminUser->id
    ]);
    echo "Super Admin role assigned to admin@example.com\n";
}

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

echo "Permissions fixed successfully!\n";
