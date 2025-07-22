<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Core\Services\ModuleService;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    protected ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
        $this->middleware(['auth', 'can:modules.view']);
    }

    /**
     * Get all modules with their status
     */
    public function index(Request $request): JsonResponse
    {
        $modules = $this->moduleService->getAllModules();
        
        return response()->json([
            'success' => true,
            'data' => $modules->map(function ($module) {
                return [
                    'name' => $module['name'],
                    'display_name' => $module['display_name'] ?? $module['name'],
                    'description' => $module['description'] ?? '',
                    'version' => $module['version'] ?? '1.0.0',
                    'active' => $module['active'] ?? false,
                    'critical' => $module['critical'] ?? false,
                    'dependencies' => $module['dependencies'] ?? [],
                    'permissions' => $module['permissions'] ?? [],
                    'navigation' => $module['navigation'] ?? null,
                ];
            })->values(),
            'meta' => [
                'total' => $modules->count(),
                'active' => $modules->where('active', true)->count(),
                'inactive' => $modules->where('active', false)->count(),
            ]
        ]);
    }

    /**
     * Enable a module
     */
    public function enable(Request $request, string $moduleName): JsonResponse
    {
        $this->authorize('modules.manage');
        
        try {
            $result = $this->moduleService->enableModule($moduleName);
            
            if ($result) {
                activity()
                    ->causedBy($request->user())
                    ->withProperties(['module' => $moduleName])
                    ->log('Module enabled');
                    
                return response()->json([
                    'success' => true,
                    'message' => "Module '{$moduleName}' has been enabled successfully."
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => "Failed to enable module '{$moduleName}'."
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Disable a module
     */
    public function disable(Request $request, string $moduleName): JsonResponse
    {
        $this->authorize('modules.manage');
        
        try {
            $result = $this->moduleService->disableModule($moduleName);
            
            if ($result) {
                activity()
                    ->causedBy($request->user())
                    ->withProperties(['module' => $moduleName])
                    ->log('Module disabled');
                    
                return response()->json([
                    'success' => true,
                    'message' => "Module '{$moduleName}' has been disabled successfully."
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => "Failed to disable module '{$moduleName}'."
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get navigation items for current user
     */
    public function navigation(Request $request): JsonResponse
    {
        $user = $request->user();
        $navigation = $this->moduleService->getNavigationForUser($user);
        
        return response()->json([
            'success' => true,
            'data' => $navigation->map(function ($navItem) {
                return [
                    'name' => $navItem['name'],
                    'navigation' => $navItem['navigation'],
                    'permissions' => $navItem['permissions'],
                ];
            })->values()
        ]);
    }

    /**
     * Get module dependencies tree
     */
    public function dependencies(Request $request): JsonResponse
    {
        $modules = $this->moduleService->getAllModules();
        $dependencyTree = [];
        
        foreach ($modules as $module) {
            $dependencyTree[] = [
                'name' => $module['name'],
                'dependencies' => $module['dependencies'] ?? [],
                'dependents' => $this->moduleService->getDependentModules($module['name'])->pluck('name')->toArray(),
                'can_disable' => !($module['critical'] ?? false) && 
                                 $this->moduleService->getDependentModules($module['name'])->isEmpty(),
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $dependencyTree
        ]);
    }

    /**
     * Check module health and status
     */
    public function health(Request $request): JsonResponse
    {
        $modules = $this->moduleService->getAllModules();
        $healthReport = [];
        
        foreach ($modules as $module) {
            $health = [
                'name' => $module['name'],
                'status' => $module['active'] ? 'active' : 'inactive',
                'dependencies_met' => $this->moduleService->checkDependencies($module),
                'has_dependents' => $this->moduleService->getDependentModules($module['name'])->isNotEmpty(),
                'critical' => $module['critical'] ?? false,
            ];
            
            // Check if module files exist
            $modulePath = app_path('Modules/' . $module['name']);
            $health['files_exist'] = file_exists($modulePath);
            
            // Check if required files exist
            $requiredFiles = ['module.json', 'routes.php'];
            $health['required_files'] = [];
            
            foreach ($requiredFiles as $file) {
                $health['required_files'][$file] = file_exists($modulePath . '/' . $file);
            }
            
            $healthReport[] = $health;
        }
        
        return response()->json([
            'success' => true,
            'data' => $healthReport,
            'summary' => [
                'total_modules' => count($healthReport),
                'active_modules' => collect($healthReport)->where('status', 'active')->count(),
                'healthy_modules' => collect($healthReport)->where('dependencies_met', true)->count(),
                'critical_modules' => collect($healthReport)->where('critical', true)->count(),
            ]
        ]);
    }

    /**
     * Clear module cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        $this->authorize('modules.manage');
        
        $this->moduleService->clearCache();
        
        activity()
            ->causedBy($request->user())
            ->log('Module cache cleared');
        
        return response()->json([
            'success' => true,
            'message' => 'Module cache has been cleared successfully.'
        ]);
    }
}
