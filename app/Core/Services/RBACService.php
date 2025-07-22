<?php

namespace App\Core\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;

class RBACService
{
    /**
     * Horizontal roles (system-wide)
     */
    const HORIZONTAL_ROLES = [
        'super-admin' => 'Super Administrator',
        'admin' => 'Administrator', 
        'manager' => 'Manager',
        'user' => 'User',
    ];

    /**
     * Vertical scopes (contextual access)
     */
    const VERTICAL_SCOPES = [
        'department' => 'Department Level',
        'business_unit' => 'Business Unit Level',
        'project' => 'Project Level',
    ];

    /**
     * Initialize default roles and permissions
     */
    public function initializeRBAC(): void
    {
        // Create horizontal roles
        foreach (self::HORIZONTAL_ROLES as $name => $displayName) {
            Role::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName]
            );
        }

        // Create base permissions
        $this->createBasePermissions();
        
        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Create base permissions
     */
    protected function createBasePermissions(): void
    {
        $permissions = [
            // User management
            'users.view' => 'View Users',
            'users.create' => 'Create Users',
            'users.edit' => 'Edit Users',
            'users.delete' => 'Delete Users',
            
            // Role management
            'roles.view' => 'View Roles',
            'roles.create' => 'Create Roles',
            'roles.edit' => 'Edit Roles',
            'roles.delete' => 'Delete Roles',
            
            // Department management
            'departments.view' => 'View Departments',
            'departments.create' => 'Create Departments',
            'departments.edit' => 'Edit Departments',
            'departments.delete' => 'Delete Departments',
            
            // Module management
            'modules.view' => 'View Modules',
            'modules.manage' => 'Manage Modules',
            'modules.configure' => 'Configure Modules',
            
            // System administration
            'system.settings' => 'System Settings',
            'system.logs' => 'View System Logs',
            'system.audit' => 'View Audit Logs',
            
            // Dashboard access
            'dashboard.access' => 'Access Dashboard',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName]
            );
        }
    }

    /**
     * Assign permissions to roles
     */
    protected function assignPermissionsToRoles(): void
    {
        // Super Admin - All permissions
        $superAdmin = Role::findByName('super-admin');
        $superAdmin->syncPermissions(Permission::all());

        // Admin - Most permissions except system critical
        $admin = Role::findByName('admin');
        $adminPermissions = Permission::whereNotIn('name', [
            'system.settings',
            'modules.configure'
        ])->get();
        $admin->syncPermissions($adminPermissions);

        // Manager - Department and user management
        $manager = Role::findByName('manager');
        $managerPermissions = Permission::whereIn('name', [
            'dashboard.access',
            'users.view',
            'users.create',
            'users.edit',
            'departments.view',
            'departments.edit',
        ])->get();
        $manager->syncPermissions($managerPermissions);

        // User - Basic access
        $user = Role::findByName('user');
        $userPermissions = Permission::whereIn('name', [
            'dashboard.access',
        ])->get();
        $user->syncPermissions($userPermissions);
    }

    /**
     * Create scoped permission
     */
    public function createScopedPermission(string $permission, string $scope, $scopeId): string
    {
        $scopedPermission = "{$permission}.{$scope}.{$scopeId}";
        
        Permission::firstOrCreate([
            'name' => $scopedPermission
        ], [
            'display_name' => ucfirst($permission) . " for " . ucfirst($scope) . " {$scopeId}"
        ]);

        return $scopedPermission;
    }

    /**
     * Grant scoped access to user
     */
    public function grantScopedAccess(User $user, string $permission, string $scope, $scopeId): void
    {
        $scopedPermission = $this->createScopedPermission($permission, $scope, $scopeId);
        $user->givePermissionTo($scopedPermission);
    }

    /**
     * Revoke scoped access from user
     */
    public function revokeScopedAccess(User $user, string $permission, string $scope, $scopeId): void
    {
        $scopedPermission = "{$permission}.{$scope}.{$scopeId}";
        $user->revokePermissionTo($scopedPermission);
    }

    /**
     * Check if user has scoped access
     */
    public function hasAccess(User $user, string $permission, string $scope = null, $scopeId = null): bool
    {
        // Super admin has access to everything
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Check base permission first
        if (!$user->hasPermissionTo($permission)) {
            return false;
        }

        // If no scope specified, base permission is enough
        if (!$scope || !$scopeId) {
            return true;
        }

        // Check scoped permission
        $scopedPermission = "{$permission}.{$scope}.{$scopeId}";
        return $user->hasPermissionTo($scopedPermission);
    }

    /**
     * Get user's accessible scopes
     */
    public function getAccessibleScopes(User $user, string $scope): Collection
    {
        if ($user->hasRole('super-admin')) {
            // Super admin has access to all scopes
            switch ($scope) {
                case 'department':
                    return collect(\App\Models\Department::all()->pluck('id'));
                default:
                    return collect();
            }
        }

        // Get scoped permissions for this user
        $scopedPermissions = $user->permissions()
            ->where('name', 'like', "%.{$scope}.%")
            ->get();

        return $scopedPermissions->map(function ($permission) use ($scope) {
            $parts = explode('.', $permission->name);
            return end($parts); // Return the scope ID
        });
    }

    /**
     * Get RBAC matrix for display
     */
    public function getRBACMatrix(): array
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return [
            'roles' => $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name ?? $role->name,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                ];
            }),
            'permissions' => $permissions->map(function ($perms, $group) {
                return [
                    'group' => $group,
                    'permissions' => $perms->map(function ($perm) {
                        return [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'display_name' => $perm->display_name ?? $perm->name,
                        ];
                    }),
                ];
            }),
            'scopes' => self::VERTICAL_SCOPES,
        ];
    }
}
