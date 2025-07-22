<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use App\Observers\SecurityObserver;
use App\Modules\Users\Models\Users;

class AuditLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register authentication event listeners
        $this->registerAuthEventListeners();
        
        // Register model observers
        $this->registerModelObservers();
        
        // Register custom security events
        $this->registerSecurityEventListeners();
    }

    /**
     * Register authentication event listeners
     */
    protected function registerAuthEventListeners(): void
    {
        $securityObserver = app(SecurityObserver::class);

        // Login events
        Event::listen(Login::class, function (Login $event) use ($securityObserver) {
            $securityObserver->userLogin($event->user);
        });

        // Logout events
        Event::listen(Logout::class, function (Logout $event) use ($securityObserver) {
            if ($event->user) {
                $securityObserver->userLogout($event->user);
            }
        });

        // Failed login attempts
        Event::listen(Failed::class, function (Failed $event) use ($securityObserver) {
            $securityObserver->loginFailed(
                $event->credentials['email'] ?? 'unknown',
                request()->ip()
            );
        });

        // Password reset events
        Event::listen(PasswordReset::class, function (PasswordReset $event) use ($securityObserver) {
            $securityObserver->passwordChanged($event->user);
        });
    }

    /**
     * Register model observers
     */
    protected function registerModelObservers(): void
    {
        // Note: HasAuditLog trait already handles basic CRUD operations
        // This is for additional specific observations if needed
    }

    /**
     * Register custom security event listeners
     */
    protected function registerSecurityEventListeners(): void
    {
        $securityObserver = app(SecurityObserver::class);

        // Role assignment events
        Event::listen('user.role.assigned', function ($event) use ($securityObserver) {
            $securityObserver->roleAssigned($event['user'], $event['role']);
        });

        // Role removal events
        Event::listen('user.role.removed', function ($event) use ($securityObserver) {
            $securityObserver->roleRemoved($event['user'], $event['role']);
        });

        // Permission grant events
        Event::listen('user.permission.granted', function ($event) use ($securityObserver) {
            $securityObserver->permissionGranted($event['user'], $event['permission']);
        });

        // Permission revoke events
        Event::listen('user.permission.revoked', function ($event) use ($securityObserver) {
            $securityObserver->permissionRevoked($event['user'], $event['permission']);
        });

        // User status change events
        Event::listen('user.status.changed', function ($event) use ($securityObserver) {
            $securityObserver->userStatusChanged(
                $event['user'], 
                $event['old_status'], 
                $event['new_status']
            );
        });

        // Suspicious activity events
        Event::listen('security.suspicious.activity', function ($event) use ($securityObserver) {
            $securityObserver->suspiciousActivity(
                $event['description'],
                $event['metadata'] ?? [],
                $event['user'] ?? null
            );
        });

        // Data export events
        Event::listen('data.exported', function ($event) use ($securityObserver) {
            $securityObserver->dataExported(
                $event['user'],
                $event['data_type'],
                $event['record_count']
            );
        });

        // Bulk operation events
        Event::listen('bulk.operation', function ($event) use ($securityObserver) {
            $securityObserver->bulkOperation(
                $event['user'],
                $event['operation'],
                $event['affected_count'],
                $event['model_type']
            );
        });
    }
}
