<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use App\Models\Module;
use App\Core\Contracts\ModuleServiceInterface;

class ModuleService implements ModuleServiceInterface
{
    protected string $modulesPath;
    protected bool $cacheEnabled;
    protected bool $safeMode;

    public function __construct()
    {
        $this->modulesPath = app_path('Modules');
        $this->cacheEnabled = config('modules.cache_enabled', true);
        $this->safeMode = config('modules.safe_mode', true);
    }

    /**
     * Get all available modules
     */
    public function getAllModules(): Collection
    {
        if ($this->cacheEnabled) {
            return Cache::remember('modules.all', 3600, function () {
                return $this->loadModulesFromFilesystem();
            });
        }

        return $this->loadModulesFromFilesystem();
    }

    /**
     * Get active modules only
     */
    public function getActiveModules(): Collection
    {
        return $this->getAllModules()->where('active', true);
    }

    /**
     * Get module by name
     */
    public function getModule(string $name): ?array
    {
        return $this->getAllModules()->firstWhere('name', $name);
    }

    /**
     * Check if module is active
     */
    public function isModuleActive(string $name): bool
    {
        $module = $this->getModule($name);
        return $module && ($module['active'] ?? false);
    }

    /**
     * Check if user has access to module
     */
    public function userHasModuleAccess($user, string $moduleName): bool
    {
        if (!$user) {
            return false;
        }

        // Super admin has access to all modules
        if ($user->hasRole('super-admin')) {
            return true;
        }

        $module = $this->getModule($moduleName);
        if (!$module) {
            return false;
        }

        // Check module permissions
        $requiredPermissions = $module['permissions'] ?? [];
        if (empty($requiredPermissions)) {
            return true; // No specific permissions required
        }

        // Check if user has any of the required permissions
        foreach ($requiredPermissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enable a module
     */
    public function enableModule(string $name): bool
    {
        $module = $this->getModule($name);
        
        if (!$module) {
            return false;
        }

        // Check dependencies
        if (!$this->checkDependencies($module)) {
            throw new \Exception("Module dependencies not met for: {$name}");
        }

        return $this->updateModuleStatus($name, true);
    }

    /**
     * Disable a module
     */
    public function disableModule(string $name): bool
    {
        $module = $this->getModule($name);
        
        if (!$module) {
            return false;
        }

        // Check if module is critical and safe mode is enabled
        if ($this->safeMode && ($module['critical'] ?? false)) {
            throw new \Exception("Cannot disable critical module in safe mode: {$name}");
        }

        // Check for dependent modules
        $dependents = $this->getDependentModules($name);
        if ($dependents->isNotEmpty()) {
            throw new \Exception("Cannot disable module {$name}. Dependent modules: " . $dependents->pluck('name')->implode(', '));
        }

        return $this->updateModuleStatus($name, false);
    }

    /**
     * Check if module dependencies are met
     */
    public function checkDependencies(array $module): bool
    {
        $dependencies = $module['dependencies'] ?? [];
        
        foreach ($dependencies as $dependency) {
            $dependencyModule = $this->getModule($dependency);
            
            if (!$dependencyModule || !$dependencyModule['active']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get modules that depend on the given module
     */
    public function getDependentModules(string $moduleName): Collection
    {
        return $this->getAllModules()->filter(function ($module) use ($moduleName) {
            $dependencies = $module['dependencies'] ?? [];
            return in_array($moduleName, $dependencies);
        });
    }

    /**
     * Get module navigation items
     */
    public function getModuleNavigation(): Collection
    {
        return $this->getActiveModules()
            ->filter(function ($module) {
                return isset($module['navigation']) && !empty($module['navigation']);
            })
            ->map(function ($module) {
                return [
                    'name' => $module['name'],
                    'navigation' => $module['navigation'],
                    'permissions' => $module['permissions'] ?? [],
                ];
            })
            ->sortBy('navigation.order');
    }

    /**
     * Get icon name mapping for navigation
     */
    private function getIconName($iconString)
    {
        // Map icon strings to their actual names that Sidebar.jsx expects
        $iconMap = [
            'CubeIcon' => 'CubeIcon',
            'UsersIcon' => 'UsersIcon', 
            'ShieldCheckIcon' => 'ShieldCheckIcon',
            'BuildingOfficeIcon' => 'BuildingOfficeIcon',
            'Cog6ToothIcon' => 'Cog6ToothIcon',
            'HomeIcon' => 'HomeIcon',
        ];
        
        return $iconMap[$iconString] ?? 'CubeIcon';
    }

    /**
     * Get module navigation items for a specific user (with permission filtering)
     */
    public function getNavigationForUser($user): Collection
    {
        return $this->getActiveModules()
            ->filter(function ($module) use ($user) {
                // Check if module has navigation
                if (!isset($module['navigation']) || empty($module['navigation'])) {
                    return false;
                }

                // Check if user has required permissions for this module
                $requiredPermissions = $module['permissions'] ?? [];
                if (!empty($requiredPermissions)) {
                    foreach ($requiredPermissions as $permission) {
                        if (!$user->can($permission)) {
                            return false;
                        }
                    }
                }

                return true;
            })
            ->map(function ($module) {
                $navigation = $module['navigation'];
                
                // Return navigation in the format expected by Sidebar.jsx
                return [
                    'name' => $navigation['name'] ?? strtolower($module['name']),
                    'href' => $navigation['href'] ?? '/' . strtolower($module['name']),
                    'icon' => $this->getIconName($navigation['icon'] ?? 'CubeIcon'),
                    'permission' => $module['permissions'][0] ?? null, // Use first permission as main permission
                    'order' => $navigation['order'] ?? 999,
                ];
            })
            ->sortBy('order')
            ->values();
    }

    /**
     * Clear module cache
     */
    public function clearCache(): void
    {
        Cache::forget('modules.all');
        Cache::forget('modules.navigation');
    }

    /**
     * Load modules from filesystem
     */
    protected function loadModulesFromFilesystem(): Collection
    {
        if (!File::exists($this->modulesPath)) {
            return collect();
        }

        $modules = collect();
        $directories = File::directories($this->modulesPath);

        foreach ($directories as $directory) {
            $moduleName = basename($directory);
            $configPath = $directory . '/module.json';

            if (File::exists($configPath)) {
                $config = json_decode(File::get($configPath), true);
                
                if ($config) {
                    $config['path'] = $directory;
                    $config['name'] = $moduleName;
                    $modules->push($config);
                }
            }
        }

        return $modules;
    }

    /**
     * Update module status
     */
    protected function updateModuleStatus(string $name, bool $active): bool
    {
        $modulePath = $this->modulesPath . '/' . $name . '/module.json';
        
        if (!File::exists($modulePath)) {
            return false;
        }

        $config = json_decode(File::get($modulePath), true);
        $config['active'] = $active;
        $config['last_updated'] = now()->toISOString();

        File::put($modulePath, json_encode($config, JSON_PRETTY_PRINT));
        
        // Update database record
        Module::updateOrCreate(
            ['name' => $name],
            [
                'active' => $active,
                'version' => $config['version'] ?? '1.0.0',
                'last_updated' => now(),
            ]
        );

        $this->clearCache();
        
        return true;
    }
}
