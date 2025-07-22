<?php

namespace App\Modules\Roles\Services;

use App\Modules\Roles\Models\Roles;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Helpers\AuditHelperSimple;

class RolePermissionService
{
    /**
     * Get all available permissions
     */
    public function getAllPermissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }
    
    /**
     * Get permissions grouped by module/category
     */
    public function getGroupedPermissions(): array
    {
        $permissions = $this->getAllPermissions();
        $grouped = [];
        
        foreach ($permissions as $permission) {
            // Extract module name from permission (e.g. 'users.view' -> 'users')
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'general';
            
            if (!isset($grouped[$module])) {
                $grouped[$module] = [
                    'name' => ucfirst($module),
                    'permissions' => [],
                ];
            }
            
            $grouped[$module]['permissions'][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $this->formatPermissionName($permission->name),
            ];
        }
        
        return $grouped;
    }
    
    /**
     * Sync permissions for a role
     */
    public function syncPermissions(Roles $role, array $permissionIds): Roles
    {
        $oldPermissions = $role->permissions->pluck('name')->toArray();
        
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        $newPermissions = $permissions->pluck('name')->toArray();
        
        DB::transaction(function() use ($role, $permissions) {
            $role->syncPermissions($permissions);
        });
        
        // Clear permissions cache
        $this->clearPermissionsCache($role);
        
        // Log permission changes
        AuditHelperSimple::logAction(
            'role_permissions_updated',
            'Role permissions were updated',
            'security',
            auth()->user(),
            $role,
            'medium',
            [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'old_permissions' => $oldPermissions,
                'new_permissions' => $newPermissions,
            ]
        );
        
        return $role->fresh();
    }
    
    /**
     * Add permissions to role
     */
    public function addPermissions(Roles $role, array $permissionIds): Roles
    {
        $oldPermissions = $role->permissions->pluck('name')->toArray();
        
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        
        DB::transaction(function() use ($role, $permissions) {
            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission);
            }
        });
        
        // Clear permissions cache
        $this->clearPermissionsCache($role);
        
        // Get new permissions list
        $role->refresh();
        $newPermissions = $role->permissions->pluck('name')->toArray();
        
        // Log permission changes
        AuditHelperSimple::logAction(
            'role_permissions_added',
            'Permissions were added to role',
            'security',
            auth()->user(),
            $role,
            'medium',
            [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'added_permissions' => array_diff($newPermissions, $oldPermissions),
            ]
        );
        
        return $role;
    }
    
    /**
     * Remove permissions from role
     */
    public function removePermissions(Roles $role, array $permissionIds): Roles
    {
        $oldPermissions = $role->permissions->pluck('name')->toArray();
        
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        
        DB::transaction(function() use ($role, $permissions) {
            foreach ($permissions as $permission) {
                $role->revokePermissionTo($permission);
            }
        });
        
        // Clear permissions cache
        $this->clearPermissionsCache($role);
        
        // Get new permissions list
        $role->refresh();
        $newPermissions = $role->permissions->pluck('name')->toArray();
        
        // Log permission changes
        AuditHelperSimple::logAction(
            'role_permissions_removed',
            'Permissions were removed from role',
            'security',
            auth()->user(),
            $role,
            'medium',
            [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'removed_permissions' => array_diff($oldPermissions, $newPermissions),
            ]
        );
        
        return $role;
    }
    
    /**
     * Format permission name for display
     */
    private function formatPermissionName(string $name): string
    {
        $parts = explode('.', $name);
        
        if (count($parts) >= 2) {
            $module = ucfirst($parts[0]);
            $action = $parts[1];
            
            $actionMap = [
                'view' => 'View',
                'list' => 'List',
                'create' => 'Create',
                'store' => 'Store',
                'edit' => 'Edit',
                'update' => 'Update',
                'delete' => 'Delete',
                'manage' => 'Manage',
                'export' => 'Export',
                'import' => 'Import',
            ];
            
            $actionName = $actionMap[$action] ?? ucfirst($action);
            
            return "{$actionName} {$module}";
        }
        
        return ucfirst(str_replace('_', ' ', $name));
    }
    
    /**
     * Clear permissions cache for a role
     */
    private function clearPermissionsCache(Roles $role): void
    {
        // Clear spatie permission cache
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Clear any other permission caches
        Cache::forget('role_permissions_' . $role->id);
    }
}
