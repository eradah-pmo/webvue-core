<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard permissions
            'dashboard.view',
            
            // User permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.manage',
            
            // Department permissions
            'departments.view',
            'departments.create',
            'departments.edit',
            'departments.delete',
            'departments.manage',
            
            // Role permissions
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'roles.manage',
            
            // Module permissions
            'modules.view',
            'modules.manage',
            'modules.configure',
            
            // System permissions
            'system.logs',
            'system.settings',
            'system.backup',
            'system.maintenance',
            
            // Audit log permissions
            'audit.view',
            'audit.export',
            
            // File permissions
            'files.upload',
            'files.download',
            'files.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $this->createRoles($permissions);
        
        // Create test users
        $this->createTestUsers();
    }

    /**
     * Create roles and assign permissions
     */
    private function createRoles(array $permissions): void
    {
        // Super Admin - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo($permissions);

        // Admin - Most permissions except system critical ones
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $adminPermissions = [
            'dashboard.view',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'roles.view', 'roles.create', 'roles.edit',
            'modules.view',
            'audit.view',
            'files.upload', 'files.download', 'files.delete',
        ];
        $admin->givePermissionTo($adminPermissions);

        // Manager - Department level permissions
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $managerPermissions = [
            'dashboard.view',
            'users.view', 'users.create', 'users.edit',
            'departments.view',
            'files.upload', 'files.download',
        ];
        $manager->givePermissionTo($managerPermissions);

        // User - Basic permissions
        $user = Role::firstOrCreate(['name' => 'user']);
        $userPermissions = [
            'dashboard.view',
            'files.upload', 'files.download',
        ];
        $user->givePermissionTo($userPermissions);

        // Guest - Very limited permissions
        $guest = Role::firstOrCreate(['name' => 'guest']);
        $guestPermissions = [
            'dashboard.view',
        ];
        $guest->givePermissionTo($guestPermissions);
    }

    /**
     * Create test users with different roles
     */
    private function createTestUsers(): void
    {
        // Create Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'active' => true,
            ]
        );
        $superAdmin->assignRole('super-admin');

        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'sysadmin@example.com'],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'active' => true,
            ]
        );
        $admin->assignRole('admin');

        // Create Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'first_name' => 'Department',
                'last_name' => 'Manager',
                'name' => 'Department Manager',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'active' => true,
            ]
        );
        $manager->assignRole('manager');

        // Create Regular User
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'first_name' => 'Regular',
                'last_name' => 'User',
                'name' => 'Regular User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'active' => true,
            ]
        );
        $user->assignRole('user');

        $this->command->info('Test users created:');
        $this->command->line('Super Admin: admin@example.com / password');
        $this->command->line('Admin: sysadmin@example.com / password');
        $this->command->line('Manager: manager@example.com / password');
        $this->command->line('User: user@example.com / password');
    }
}
