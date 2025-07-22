<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestUserPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get the currently authenticated user or use the first user if none is authenticated
        $user = Auth::user() ?? User::first();
        
        if (!$user) {
            $this->command->error('No user found to assign permissions to!');
            return;
        }
        
        $this->command->info('Assigning permissions to user: ' . $user->name . ' (ID: ' . $user->id . ')');
        
        // Assign super-admin role to the user
        $superAdminRole = Role::where('name', 'super-admin')->first();
        
        if ($superAdminRole) {
            $user->assignRole($superAdminRole);
            $this->command->info('Assigned super-admin role to user');
        } else {
            $this->command->error('Super-admin role not found!');
            
            // If role doesn't exist, assign all permissions directly
            $allPermissions = Permission::all();
            $user->syncPermissions($allPermissions);
            $this->command->info('Assigned all ' . $allPermissions->count() . ' permissions directly to user');
        }
        
        // Clear user permissions cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->command->info('Test permissions assigned successfully!');
        $this->command->info('User now has the following permissions:');
        
        foreach ($user->getAllPermissions() as $permission) {
            $this->command->line('- ' . $permission->name);
        }
    }
}
