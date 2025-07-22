<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Auto Registration
    |--------------------------------------------------------------------------
    |
    | When enabled, modules will be automatically registered and their routes
    | will be loaded. Disable this if you want to manually control which
    | modules are loaded.
    |
    */

    'auto_register' => env('MODULES_AUTO_REGISTER', true),

    /*
    |--------------------------------------------------------------------------
    | Module Cache
    |--------------------------------------------------------------------------
    |
    | Enable caching of module configurations to improve performance.
    | Cache will be cleared automatically when modules are enabled/disabled.
    |
    */

    'cache_enabled' => env('MODULES_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Safe Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, critical modules cannot be disabled unless the user
    | has super-admin privileges. This prevents accidental system breakage.
    |
    */

    'safe_mode' => env('MODULES_SAFE_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Module Path
    |--------------------------------------------------------------------------
    |
    | The path where modules are stored relative to the app directory.
    |
    */

    'path' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Module Stubs Path
    |--------------------------------------------------------------------------
    |
    | The path where module stubs are stored for the make:module command.
    |
    */

    'stubs_path' => base_path('stubs'),

    /*
    |--------------------------------------------------------------------------
    | Default Module Configuration
    |--------------------------------------------------------------------------
    |
    | Default configuration values that will be used when creating new modules.
    |
    */

    'defaults' => [
        'version' => '1.0.0',
        'active' => true,
        'critical' => false,
        'dependencies' => [],
        'permissions' => [],
        'config' => [
            'per_page' => 15,
            'cache_enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Validation Rules
    |--------------------------------------------------------------------------
    |
    | Validation rules for module configuration files.
    |
    */

    'validation' => [
        'name' => 'required|string|max:255',
        'display_name' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:1000',
        'version' => 'required|string|regex:/^\d+\.\d+\.\d+$/',
        'active' => 'boolean',
        'critical' => 'boolean',
        'dependencies' => 'array',
        'permissions' => 'array',
        'navigation' => 'nullable|array',
        'config' => 'nullable|array',
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected Modules
    |--------------------------------------------------------------------------
    |
    | Modules that cannot be disabled even by super-admin users.
    | These are core system modules required for basic functionality.
    |
    */

    'protected' => [
        'Users',
        'Roles',
        'Settings',
    ],

];
