<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Core\Services\ModuleService;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ModuleService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (env('MODULES_AUTO_REGISTER', true)) {
            $this->registerModules();
        }
    }

    /**
     * Register all active modules
     */
    protected function registerModules(): void
    {
        $modulesPath = app_path('Modules');
        
        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $configPath = $modulePath . '/module.json';
            
            if (File::exists($configPath)) {
                $config = json_decode(File::get($configPath), true);
                
                // Skip inactive modules
                if (!($config['active'] ?? true)) {
                    continue;
                }

                // Register module routes
                $this->registerModuleRoutes($modulePath, $moduleName);
                
                // Register module views
                $this->registerModuleViews($modulePath, $moduleName);
                
                // Register module translations
                $this->registerModuleTranslations($modulePath, $moduleName);
            }
        }
    }

    /**
     * Register module routes
     */
    protected function registerModuleRoutes(string $modulePath, string $moduleName): void
    {
        $routesPath = $modulePath . '/routes.php';
        
        if (File::exists($routesPath)) {
            Route::middleware(['web', 'auth', 'verified'])
                ->group($routesPath);
        }
    }

    /**
     * Register module views
     */
    protected function registerModuleViews(string $modulePath, string $moduleName): void
    {
        $viewsPath = $modulePath . '/resources/views';
        
        if (File::exists($viewsPath)) {
            $this->loadViewsFrom($viewsPath, strtolower($moduleName));
        }
    }

    /**
     * Register module translations
     */
    protected function registerModuleTranslations(string $modulePath, string $moduleName): void
    {
        $langPath = $modulePath . '/resources/lang';
        
        if (File::exists($langPath)) {
            $this->loadTranslationsFrom($langPath, strtolower($moduleName));
        }
    }
}
