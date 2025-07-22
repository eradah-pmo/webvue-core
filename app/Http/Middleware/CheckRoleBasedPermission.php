<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Core\Services\PermissionService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role-Based Permission Middleware
 * 
 * This middleware enforces strict role-based permissions:
 * - Only checks permissions through roles (no direct user permissions)
 * - Supports department-scoped permissions for managers
 */
class CheckRoleBasedPermission
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $departmentParam = null): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Get department ID from request if specified
        $departmentId = null;
        if ($departmentParam) {
            $departmentId = $request->route($departmentParam) ?? $request->input($departmentParam);
        }

        // Check permission using the new service
        $hasPermission = $this->permissionService->userHasScopedPermission(
            $user, 
            $permission, 
            $departmentId
        );

        if (!$hasPermission) {
            abort(403, 'Insufficient permissions');
        }

        return $next($request);
    }
}
