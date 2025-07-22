<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class SecurityObserver
{
    /**
     * Handle user login events
     */
    public function userLogin($user)
    {
        AuditLog::createEntry([
            'event' => 'login',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'auth',
            'action' => 'user_login',
            'description' => "User {$user->name} logged in successfully",
            'severity' => 'info',
            'tags' => ['authentication', 'login'],
        ]);
    }

    /**
     * Handle user logout events
     */
    public function userLogout($user)
    {
        AuditLog::createEntry([
            'event' => 'logout',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'auth',
            'action' => 'user_logout',
            'description' => "User {$user->name} logged out",
            'severity' => 'info',
            'tags' => ['authentication', 'logout'],
        ]);
    }

    /**
     * Handle failed login attempts
     */
    public function loginFailed($email, $ipAddress = null)
    {
        AuditLog::createEntry([
            'event' => 'login_failed',
            'auditable_type' => 'App\\Models\\User',
            'auditable_id' => null,
            'user_email' => $email,
            'module' => 'auth',
            'action' => 'login_failed',
            'description' => "Failed login attempt for email: {$email}",
            'severity' => 'warning',
            'tags' => ['authentication', 'failed_login', 'security'],
            'metadata' => [
                'attempted_email' => $email,
                'ip_address' => $ipAddress ?: request()?->ip(),
            ],
        ]);
    }

    /**
     * Handle password reset requests
     */
    public function passwordResetRequested($user)
    {
        AuditLog::createEntry([
            'event' => 'password_reset_requested',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'auth',
            'action' => 'password_reset_request',
            'description' => "Password reset requested for user {$user->name}",
            'severity' => 'warning',
            'tags' => ['authentication', 'password_reset', 'security'],
        ]);
    }

    /**
     * Handle password changes
     */
    public function passwordChanged($user)
    {
        AuditLog::createEntry([
            'event' => 'password_changed',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'auth',
            'action' => 'password_change',
            'description' => "Password changed for user {$user->name}",
            'severity' => 'warning',
            'tags' => ['authentication', 'password_change', 'security'],
        ]);
    }

    /**
     * Handle role assignments
     */
    public function roleAssigned($user, $role)
    {
        AuditLog::createEntry([
            'event' => 'role_assigned',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'rbac',
            'action' => 'assign_role',
            'description' => "Role '{$role}' assigned to user {$user->name}",
            'severity' => 'warning',
            'tags' => ['rbac', 'role_assignment', 'security'],
            'metadata' => [
                'role_name' => $role,
                'assigned_by' => auth()->user()?->name,
            ],
        ]);
    }

    /**
     * Handle role removals
     */
    public function roleRemoved($user, $role)
    {
        AuditLog::createEntry([
            'event' => 'role_removed',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'rbac',
            'action' => 'remove_role',
            'description' => "Role '{$role}' removed from user {$user->name}",
            'severity' => 'warning',
            'tags' => ['rbac', 'role_removal', 'security'],
            'metadata' => [
                'role_name' => $role,
                'removed_by' => auth()->user()?->name,
            ],
        ]);
    }

    /**
     * Handle permission grants
     */
    public function permissionGranted($user, $permission)
    {
        AuditLog::createEntry([
            'event' => 'permission_granted',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'rbac',
            'action' => 'grant_permission',
            'description' => "Permission '{$permission}' granted to user {$user->name}",
            'severity' => 'warning',
            'tags' => ['rbac', 'permission_grant', 'security'],
            'metadata' => [
                'permission_name' => $permission,
                'granted_by' => auth()->user()?->name,
            ],
        ]);
    }

    /**
     * Handle permission revocations
     */
    public function permissionRevoked($user, $permission)
    {
        AuditLog::createEntry([
            'event' => 'permission_revoked',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'rbac',
            'action' => 'revoke_permission',
            'description' => "Permission '{$permission}' revoked from user {$user->name}",
            'severity' => 'warning',
            'tags' => ['rbac', 'permission_revoke', 'security'],
            'metadata' => [
                'permission_name' => $permission,
                'revoked_by' => auth()->user()?->name,
            ],
        ]);
    }

    /**
     * Handle user status changes
     */
    public function userStatusChanged($user, $oldStatus, $newStatus)
    {
        $severity = ($newStatus === 'inactive' || $newStatus === false) ? 'warning' : 'info';
        
        AuditLog::createEntry([
            'event' => 'status_changed',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'users',
            'action' => 'change_status',
            'description' => "User {$user->name} status changed from {$oldStatus} to {$newStatus}",
            'severity' => $severity,
            'tags' => ['user_management', 'status_change'],
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'metadata' => [
                'changed_by' => auth()->user()?->name,
            ],
        ]);
    }

    /**
     * Handle suspicious activities
     */
    public function suspiciousActivity($description, $metadata = [], $user = null)
    {
        AuditLog::createEntry([
            'event' => 'suspicious_activity',
            'auditable_type' => $user ? get_class($user) : null,
            'auditable_id' => $user?->id,
            'module' => 'security',
            'action' => 'suspicious_activity',
            'description' => $description,
            'severity' => 'critical',
            'tags' => ['security', 'suspicious', 'alert'],
            'metadata' => $metadata,
        ]);
    }

    /**
     * Handle data export events
     */
    public function dataExported($user, $dataType, $recordCount)
    {
        AuditLog::createEntry([
            'event' => 'data_exported',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'data',
            'action' => 'export_data',
            'description' => "User {$user->name} exported {$recordCount} {$dataType} records",
            'severity' => 'warning',
            'tags' => ['data_export', 'security'],
            'metadata' => [
                'data_type' => $dataType,
                'record_count' => $recordCount,
                'export_format' => request()?->get('format', 'unknown'),
            ],
        ]);
    }

    /**
     * Handle bulk operations
     */
    public function bulkOperation($user, $operation, $affectedCount, $modelType)
    {
        AuditLog::createEntry([
            'event' => 'bulk_operation',
            'auditable_type' => $modelType,
            'auditable_id' => null,
            'module' => 'bulk',
            'action' => "bulk_{$operation}",
            'description' => "User {$user->name} performed bulk {$operation} on {$affectedCount} {$modelType} records",
            'severity' => 'warning',
            'tags' => ['bulk_operation', 'data_modification'],
            'metadata' => [
                'operation' => $operation,
                'affected_count' => $affectedCount,
                'model_type' => $modelType,
            ],
        ]);
    }
}
