<?php

namespace App\Modules\Roles\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Roles\Models\Roles;
use App\Modules\Roles\Services\RolePermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;

class RolesPermissionController extends Controller
{
    /**
     * The role permission service instance
     */
    private RolePermissionService $permissionService;
    
    /**
     * Create a new controller instance
     */
    public function __construct(RolePermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
        
        // Apply middleware
        $this->middleware('auth');
        $this->middleware('active-module:roles');
    }
    
    /**
     * Get all available permissions
     */
    public function getAllPermissions(): JsonResponse
    {
        // Check permission
        if (! Gate::allows('roles.view')) {
            abort(403, 'Unauthorized action');
        }
        
        $permissions = $this->permissionService->getAllPermissions();
        
        return response()->json([
            'permissions' => $permissions,
        ]);
    }
    
    /**
     * Get permissions grouped by module
     */
    public function getGroupedPermissions(): JsonResponse
    {
        // Check permission
        if (! Gate::allows('roles.view')) {
            abort(403, 'Unauthorized action');
        }
        
        $groupedPermissions = $this->permissionService->getGroupedPermissions();
        
        return response()->json([
            'grouped_permissions' => $groupedPermissions,
        ]);
    }
    
    /**
     * Update role permissions
     */
    public function updatePermissions(Request $request, Roles $role): JsonResponse
    {
        // Check permission
        if (! Gate::allows('roles.edit')) {
            abort(403, 'Unauthorized action');
        }
        
        // Validate request
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);
        
        // Sync permissions
        $role = $this->permissionService->syncPermissions($role, $validated['permissions']);
        
        return response()->json([
            'message' => 'Permissions updated successfully',
            'role' => $role->load('permissions'),
        ]);
    }
    
    /**
     * Add permissions to role
     */
    public function addPermissions(Request $request, Roles $role): JsonResponse
    {
        // Check permission
        if (! Gate::allows('roles.edit')) {
            abort(403, 'Unauthorized action');
        }
        
        // Validate request
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);
        
        // Add permissions
        $role = $this->permissionService->addPermissions($role, $validated['permissions']);
        
        return response()->json([
            'message' => 'Permissions added successfully',
            'role' => $role->load('permissions'),
        ]);
    }
    
    /**
     * Remove permissions from role
     */
    public function removePermissions(Request $request, Roles $role): JsonResponse
    {
        // Check permission
        if (! Gate::allows('roles.edit')) {
            abort(403, 'Unauthorized action');
        }
        
        // Validate request
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);
        
        // Remove permissions
        $role = $this->permissionService->removePermissions($role, $validated['permissions']);
        
        return response()->json([
            'message' => 'Permissions removed successfully',
            'role' => $role->load('permissions'),
        ]);
    }
}
