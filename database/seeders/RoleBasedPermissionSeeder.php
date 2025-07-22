<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Department;
use App\Core\Services\PermissionService;

/**
 * Role-Based Permission Seeder
 * 
 * This seeder implements the new permission system where:
 * 1. Permissions are ONLY attached to Roles (not directly to Users)
 * 2. Users have Roles
 * 3. Department managers have scoped permissions over their departments
 */
class RoleBasedPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('🔧 Setting up Role-Based Permission System...');

        // Step 1: Remove all direct user permissions (enforce role-based only)
        $this->removeDirectUserPermissions();

        // Step 2: Create permissions
        $permissions = $this->createPermissions();

        // Step 3: Create roles and assign permissions
        $this->createRolesWithPermissions($permissions);

        // Step 4: Setup department managers
        $this->setupDepartmentManagers();

        // Step 5: Assign roles to existing users
        $this->assignRolesToUsers();

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('✅ Role-Based Permission System setup completed!');
    }

    /**
     * Remove all direct user permissions
     */
    private function removeDirectUserPermissions(): void
    {
        $this->command->info('🧹 Removing direct user permissions...');
        
        $users = User::all();
        foreach ($users as $user) {
            // Remove direct permissions (keep only role-based permissions)
            $user->permissions()->detach();
        }

        $this->command->info('✅ Direct user permissions removed');
    }

    /**
     * Create all permissions
     */
    private function createPermissions(): array
    {
        $this->command->info('📋 Creating permissions...');

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
            'audit-logs.view',
            
            // Settings permissions
            'settings.view',
            'settings.create',
            'settings.edit',
            'settings.delete',
            
            // File permissions
            'files.upload',
            'files.download',
            'files.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('✅ ' . count($permissions) . ' permissions created');
        return $permissions;
    }

    /**
     * Create roles and assign permissions
     */
    private function createRolesWithPermissions(array $permissions): void
    {
        $this->command->info('👥 Creating roles with permissions...');

        // Super Admin - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions($permissions);
        $this->command->line('- Super Admin: ' . count($permissions) . ' permissions');

        // Admin - Most permissions except system critical ones
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $adminPermissions = [
            'dashboard.view',
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'roles.view', 'roles.create', 'roles.edit',
            'modules.view',
            'audit.view', 'audit-logs.view',
            'settings.view', 'settings.create', 'settings.edit',
            'files.upload', 'files.download', 'files.delete',
        ];
        $admin->syncPermissions($adminPermissions);
        $this->command->line('- Admin: ' . count($adminPermissions) . ' permissions');

        // Department Manager - Department-scoped permissions
        $departmentManager = Role::firstOrCreate(['name' => 'department-manager']);
        $managerPermissions = [
            'dashboard.view',
            'users.view', 'users.create', 'users.edit', // Can manage users in their department
            'departments.view',
            'audit.view',
            'files.upload', 'files.download',
        ];
        $departmentManager->syncPermissions($managerPermissions);
        $this->command->line('- Department Manager: ' . count($managerPermissions) . ' permissions');

        // Manager - Basic management permissions
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $basicManagerPermissions = [
            'dashboard.view',
            'users.view',
            'departments.view',
            'files.upload', 'files.download',
        ];
        $manager->syncPermissions($basicManagerPermissions);
        $this->command->line('- Manager: ' . count($basicManagerPermissions) . ' permissions');

        // User - Basic permissions
        $user = Role::firstOrCreate(['name' => 'user']);
        $userPermissions = [
            'dashboard.view',
            'files.upload', 'files.download',
        ];
        $user->syncPermissions($userPermissions);
        $this->command->line('- User: ' . count($userPermissions) . ' permissions');

        // Guest - Very limited permissions
        $guest = Role::firstOrCreate(['name' => 'guest']);
        $guestPermissions = [
            'dashboard.view',
        ];
        $guest->syncPermissions($guestPermissions);
        $this->command->line('- Guest: ' . count($guestPermissions) . ' permissions');

        $this->command->info('✅ Roles created with permissions');
    }

    /**
     * Setup department managers
     */
    private function setupDepartmentManagers(): void
    {
        $this->command->info('🏢 Setting up department managers...');

        // Create sample departments if they don't exist
        $departments = [
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Marketing', 'code' => 'MKT'],
        ];

        foreach ($departments as $deptData) {
            Department::firstOrCreate(
                ['code' => $deptData['code']],
                [
                    'name' => $deptData['name'],
                    'active' => true,
                ]
            );
        }

        $this->command->info('✅ Department structure setup completed');
    }

    /**
     * Assign roles to existing users
     */
    private function assignRolesToUsers(): void
    {
        $this->command->info('👤 Assigning roles to users...');

        $permissionService = app(PermissionService::class);

        // Find existing users and assign appropriate roles
        $users = User::all();
        
        foreach ($users as $user) {
            // Remove existing roles first
            $user->roles()->detach();
            
            // Assign role based on user characteristics
            if ($user->email === 'admin@example.com' || str_contains($user->email, 'admin')) {
                $permissionService->assignRoleToUser($user, 'super-admin');
                $this->command->line('- ' . $user->name . ': Super Admin');
            } elseif (str_contains($user->email, 'manager') || $user->managedDepartments()->count() > 0) {
                $permissionService->assignRoleToUser($user, 'department-manager');
                $this->command->line('- ' . $user->name . ': Department Manager');
            } else {
                $permissionService->assignRoleToUser($user, 'user');
                $this->command->line('- ' . $user->name . ': User');
            }
        }

        $this->command->info('✅ User roles assigned');
    }
}
