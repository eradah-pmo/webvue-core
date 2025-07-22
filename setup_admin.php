<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

try {
    // Clear cache
    \Artisan::call('permission:cache-reset');
    
    // Create permissions
    $permissions = [
        'users.view', 'users.create', 'users.edit', 'users.delete',
        'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
        'settings.view', 'settings.create', 'settings.edit', 'settings.delete',
        'audit-logs.view'
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate([
            'name' => $permission,
            'guard_name' => 'web'
        ]);
    }

    // Create super-admin role
    $role = Role::firstOrCreate([
        'name' => 'super-admin',
        'guard_name' => 'web'
    ]);

    // Assign all permissions to role
    $role->givePermissionTo(Permission::all());

    // Create admin user
    $user = User::updateOrCreate(
        ['email' => 'admin@example.com'],
        [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]
    );

    // Assign role to user
    $user->assignRole('super-admin');

    echo "✅ Admin user created successfully!\n";
    echo "📧 Email: admin@example.com\n";
    echo "🔑 Password: password\n";
    echo "👑 Role: super-admin\n";
    echo "🛡️ Permissions: " . $user->getAllPermissions()->count() . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
