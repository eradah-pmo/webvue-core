<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Core\Contracts\ModuleServiceInterface;
use App\Core\Services\ModuleService;
use App\Core\Services\RBACService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind ModuleService interface
        $this->app->bind(ModuleServiceInterface::class, ModuleService::class);
        
        // Register RBACService
        $this->app->singleton(RBACService::class, function ($app) {
            return new RBACService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL
        Schema::defaultStringLength(191);
        
        // Set locale based on user preference or default
        if (auth()->check() && auth()->user()->locale) {
            app()->setLocale(auth()->user()->locale);
        }
    }
}
