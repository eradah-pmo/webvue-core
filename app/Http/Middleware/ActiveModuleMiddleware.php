<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Core\Services\ModuleService;
use Symfony\Component\HttpFoundation\Response;

class ActiveModuleMiddleware
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
        // Check if module is active
        if (!$this->moduleService->isModuleActive($moduleName)) {
            abort(404, "Module '{$moduleName}' is not active or does not exist.");
        }

        // Check if user has access to this module
        $user = $request->user();
        if ($user && !$this->moduleService->userHasModuleAccess($user, $moduleName)) {
            abort(403, "You don't have access to the '{$moduleName}' module.");
        }

        return $next($request);
    }
}
