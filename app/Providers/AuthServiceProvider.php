<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define Gates for all permissions
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'settings.view', 'settings.create', 'settings.edit', 'settings.delete',
            'audit-logs.view'
        ];

        foreach ($permissions as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                // Super admin can do everything
                if ($user->hasRole('super-admin')) {
                    return true;
                }
                
                // Check if user has the specific permission via Spatie
                return $user->hasPermissionTo($permission);
            });
        }
    }
}
