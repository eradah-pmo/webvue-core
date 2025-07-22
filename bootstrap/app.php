<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Register spatie/laravel-permission middleware
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Configure rate limiting
        $middleware->throttleWithRedis();
        
        // Add custom rate limits
        $middleware->alias([
            'throttle.login' => 'throttle:5,1',
            'throttle.api' => 'throttle:60,1', 
            'throttle.sensitive' => 'throttle:10,1',
        ]);

        // Add custom middleware
        $middleware->alias([
            'module.access' => \App\Http\Middleware\CheckModuleAccess::class,
            'log.activity' => \App\Http\Middleware\LogUserActivity::class,
            'active-module' => \App\Http\Middleware\ActiveModuleMiddleware::class,
        ]);

        // Apply activity logging to web routes
        $middleware->web(append: [
            \App\Http\Middleware\LogUserActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
