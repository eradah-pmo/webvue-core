<?php

namespace App\Modules\AuditLogs\Policies;

use App\Modules\Users\Models\Users;
use App\Models\AuditLogSimple;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuditLogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view audit logs listing.
     */
    public function viewAny(Users $user): bool
    {
        return $user->can('audit_logs.view');
    }

    /**
     * Determine whether the user can view the audit log.
     */
    public function view(Users $user, AuditLogSimple $auditLog): bool
    {
        return $user->can('audit_logs.view');
    }

    /**
     * Determine whether the user can view the security dashboard.
     */
    public function dashboard(Users $user): bool
    {
        return $user->can('audit_logs.dashboard');
    }

    /**
     * Determine whether the user can export audit logs.
     */
    public function export(Users $user): bool
    {
        return $user->can('audit_logs.export');
    }

    /**
     * Determine whether the user can delete audit logs.
     */
    public function delete(Users $user, AuditLogSimple $auditLog): bool
    {
        // Only super admins can delete audit logs
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can bulk delete audit logs.
     */
    public function bulkDelete(Users $user): bool
    {
        // Only super admins can bulk delete audit logs
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can clean old audit logs.
     */
    public function cleanOld(Users $user): bool
    {
        // Only super admins can clean old audit logs
        return $user->hasRole('super-admin');
    }
}
