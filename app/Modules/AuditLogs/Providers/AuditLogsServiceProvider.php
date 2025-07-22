<?php

namespace App\Modules\AuditLogs\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\AuditLogSimple;
use App\Modules\AuditLogs\Policies\AuditLogPolicy;

class AuditLogsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register module services here
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(AuditLogSimple::class, AuditLogPolicy::class);

        // Register custom gates
        Gate::define('audit_logs.view', function ($user) {
            return $user->can('view audit logs') || $user->hasRole(['super-admin', 'admin']);
        });

        Gate::define('audit_logs.dashboard', function ($user) {
            return $user->can('view audit dashboard') || $user->hasRole(['super-admin', 'admin']);
        });

        Gate::define('audit_logs.export', function ($user) {
            return $user->can('export audit logs') || $user->hasRole(['super-admin', 'admin']);
        });

        Gate::define('audit_logs.manage', function ($user) {
            return $user->can('manage audit logs') || $user->hasRole('super-admin');
        });
    }
}
