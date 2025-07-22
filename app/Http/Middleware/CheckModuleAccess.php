<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Core\Services\ModuleService;

class CheckModuleAccess
{
    protected ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $moduleName): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if module exists and is active
        $module = $this->moduleService->getModule($moduleName);
        
        if (!$module || !$module['active']) {
            return response()->json([
                'error' => 'Module not found or inactive',
                'module' => $moduleName
            ], 404);
        }

        // Check if user has access to the module
        $requiredPermissions = $module['permissions'] ?? [];
        
        if (!empty($requiredPermissions)) {
            $hasAccess = false;
            
            foreach ($requiredPermissions as $permission) {
                if ($user->can($permission)) {
                    $hasAccess = true;
                    break;
                }
            }
            
            if (!$hasAccess) {
                return response()->json([
                    'error' => 'Insufficient permissions for this module',
                    'module' => $moduleName,
                    'required_permissions' => $requiredPermissions
                ], 403);
            }
        }

        // Add module info to request for use in controllers
        $request->attributes->set('current_module', $module);

        return $next($request);
    }
} 