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

try {
    // Get all users and show them
    $users = User::all();
    
    echo "📋 Available Users:\n";
    foreach ($users as $user) {
        echo "- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    }
    
    echo "\n🔍 Enter the ID of the user you want to make Super Admin: ";
    $userId = trim(fgets(STDIN));
    
    $user = User::find($userId);
    if (!$user) {
        echo "❌ User not found!\n";
        exit(1);
    }
    
    // Get or create super-admin role
    $role = Role::firstOrCreate([
        'name' => 'super-admin',
        'guard_name' => 'web'
    ]);
    
    // Assign role to user
    $user->assignRole($role);
    
    echo "✅ User '{$user->name}' is now Super Admin!\n";
    echo "📧 Email: {$user->email}\n";
    echo "👑 Role: super-admin\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
