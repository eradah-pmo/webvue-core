<?php

namespace App\Core\Services;

use App\Models\User;
use App\Modules\Users\Models\User as ModuleUser;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Permission Service - Handles Role-Based and Department-Scoped Permissions
 * 
 * This service implements a strict RBAC system where:
 * 1. Permissions are ONLY attached to Roles (not directly to Users)
 * 2. Users have Roles
 * 3. Department managers have scoped permissions over their department and sub-departments
 */
class PermissionService
{
    /**
     * Check if user has permission (Role-based only)
     * 
     * @param User|ModuleUser|Authenticatable $user
     * @param string $permission
     * @return bool
     */
    public function userHasPermission($user, string $permission): bool
    {
        // Super admin has all permissions
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Check permission through roles only (no direct user permissions)
        return $user->hasPermissionTo($permission);
    }

    /**
     * Check if user has scoped permission within a department
     * 
     * @param User|ModuleUser|Authenticatable $user
     * @param string $permission
     * @param int|null $departmentId
     * @return bool
     */
    public function userHasScopedPermission($user, string $permission, ?int $departmentId = null): bool
    {
        // Super admin has all permissions everywhere
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // If no department specified, check global permission
        if (!$departmentId) {
            return $this->userHasPermission($user, $permission);
        }

        // Check if user has global permission first
        if ($this->userHasPermission($user, $permission)) {
            return true;
        }

        // Check department-scoped permissions
        return $this->userHasDepartmentScopedPermission($user, $permission, $departmentId);
    }

    /**
     * Check if user has department-scoped permission
     * 
     * @param User $user
     * @param string $permission
     * @param int $departmentId
     * @return bool
     */
    public function userHasDepartmentScopedPermission(User $user, string $permission, int $departmentId): bool
    {
        $department = Department::find($departmentId);
        if (!$department) {
            return false;
        }

        // Check if user is manager of this department
        if ($department->manager_id === $user->id) {
            return $this->managerHasPermissionInDepartment($user, $permission, $department);
        }

        // Check if user is manager of any parent department
        $parentDepartment = $department->parent;
        while ($parentDepartment) {
            if ($parentDepartment->manager_id === $user->id) {
                return $this->managerHasPermissionInDepartment($user, $permission, $parentDepartment);
            }
            $parentDepartment = $parentDepartment->parent;
        }

        // Check if user belongs to this department and has role-based permission
        if ($user->department_id === $departmentId) {
            return $this->userHasPermission($user, $permission);
        }

        return false;
    }

    /**
     * Check if manager has permission within their managed department
     * 
     * @param User $manager
     * @param string $permission
     * @param Department $department
     * @return bool
     */
    protected function managerHasPermissionInDepartment(User $manager, string $permission, Department $department): bool
    {
        // Manager role should have department-scoped permissions
        if ($manager->hasRole('manager') || $manager->hasRole('department-manager')) {
            // Define which permissions managers can have in their departments
            $managerScopedPermissions = [
                'users.view',
                'users.create', 
                'users.edit',
                'departments.view',
                'audit.view',
                'files.upload',
                'files.download'
            ];

            return in_array($permission, $managerScopedPermissions);
        }

        // Admin and super-admin have broader permissions
        if ($manager->hasRole('admin') || $manager->hasRole('super-admin')) {
            return $this->userHasPermission($manager, $permission);
        }

        return false;
    }

    /**
     * Get user's accessible departments based on their role and position
     * 
     * @param User|ModuleUser|Authenticatable $user
     * @return Collection
     */
    public function getUserAccessibleDepartments($user): Collection
    {
        // Super admin can access all departments
        if ($user->hasRole('super-admin')) {
            return Department::active()->get();
        }

        // Admin can access all departments
        if ($user->hasRole('admin')) {
            return Department::active()->get();
        }

        $accessibleDepartments = collect();

        // Add user's own department
        if ($user->department_id) {
            $userDepartment = Department::find($user->department_id);
            if ($userDepartment) {
                $accessibleDepartments->push($userDepartment);
            }
        }

        // If user is a manager, add managed departments and their sub-departments
        $managedDepartments = Department::where('manager_id', $user->id)->get();
        foreach ($managedDepartments as $department) {
            $accessibleDepartments->push($department);
            
            // Add all sub-departments
            $subDepartments = $this->getAllSubDepartments($department);
            $accessibleDepartments = $accessibleDepartments->merge($subDepartments);
        }

        return $accessibleDepartments->unique('id');
    }

    /**
     * Get all sub-departments recursively
     * 
     * @param Department $department
     * @return Collection
     */
    protected function getAllSubDepartments(Department $department): Collection
    {
        $subDepartments = collect();
        
        foreach ($department->children as $child) {
            $subDepartments->push($child);
            $subDepartments = $subDepartments->merge($this->getAllSubDepartments($child));
        }

        return $subDepartments;
    }

    /**
     * Get users that the given user can manage
     * 
     * @param User|ModuleUser|Authenticatable $user
     * @return Collection
     */
    public function getUserManageableUsers($user): Collection
    {
        // Super admin can manage all users
        if ($user->hasRole('super-admin')) {
            return User::active()->get();
        }

        // Admin can manage all users except super-admins
        if ($user->hasRole('admin')) {
            return User::active()
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'super-admin');
                })
                ->get();
        }

        // Managers can manage users in their accessible departments
        $accessibleDepartments = $this->getUserAccessibleDepartments($user);
        $departmentIds = $accessibleDepartments->pluck('id');

        return User::active()
            ->whereIn('department_id', $departmentIds)
            ->where('id', '!=', $user->id) // Can't manage themselves
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['super-admin', 'admin']); // Can't manage admins
            })
            ->get();
    }

    /**
     * Assign role to user (removes direct permissions if any exist)
     * 
     * @param User|ModuleUser|Authenticatable $user
     * @param string|array $roles
     * @return bool
     */
    public function assignRoleToUser($user, $roles): bool
    {
        try {
            // Remove any direct permissions (enforce role-based only)
            $user->permissions()->detach();
            
            // Assign roles
            $user->assignRole($roles);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove direct permissions from user (enforce role-based only)
     * 
     * @param User|ModuleUser|Authenticatable $user
     * @return bool
     */
    public function removeDirectPermissions($user): bool
    {
        try {
            $user->permissions()->detach();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get role-based permissions for user (no direct permissions)
     * 
     * @param User|ModuleUser|Authenticatable $user
     * @return Collection
     */
    public function getUserPermissions($user): Collection
    {
        return $user->getPermissionsViaRoles();
    }

    /**
     * Check if user can manage another user
     * 
     * @param User|ModuleUser|Authenticatable $manager
     * @param User|ModuleUser|Authenticatable $targetUser
     * @return bool
     */
    public function canManageUser($manager, $targetUser): bool
    {
        // Super admin can manage everyone
        if ($manager->hasRole('super-admin')) {
            return true;
        }

        // Admin can manage everyone except super-admins
        if ($manager->hasRole('admin')) {
            return !$targetUser->hasRole('super-admin');
        }

        // Managers can only manage users in their accessible departments
        $manageableUsers = $this->getUserManageableUsers($manager);
        return $manageableUsers->contains('id', $targetUser->id);
    }
}
