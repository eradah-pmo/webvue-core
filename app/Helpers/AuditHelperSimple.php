<?php

namespace App\Helpers;

use App\Models\AuditLogSimple;
use Illuminate\Support\Facades\Event;

class AuditHelperSimple
{
    /**
     * Log a security event
     */
    public static function logSecurity(string $action, string $description, array $metadata = [], $user = null): AuditLogSimple
    {
        return AuditLogSimple::createEntry([
            'event' => 'security',
            'auditable_type' => $user ? get_class($user) : null,
            'auditable_id' => $user?->id,
            'module' => 'security',
            'action' => $action,
            'description' => $description,
            'severity' => 'critical',
            'tags' => ['security', 'alert'],
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log authentication event
     */
    public static function logAuth(string $event, $user, array $metadata = []): AuditLogSimple
    {
        $severity = in_array($event, ['login_failed', 'password_reset_requested']) ? 'warning' : 'info';
        
        return AuditLogSimple::createEntry([
            'event' => $event,
            'auditable_type' => $user ? get_class($user) : null,
            'auditable_id' => $user?->id,
            'module' => 'auth',
            'action' => $event,
            'description' => self::getAuthDescription($event, $user),
            'severity' => $severity,
            'tags' => ['authentication'],
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log RBAC event
     */
    public static function logRBAC(string $action, $user, string $target, array $metadata = []): AuditLogSimple
    {
        return AuditLogSimple::createEntry([
            'event' => 'rbac_change',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'rbac',
            'action' => $action,
            'description' => "RBAC: {$action} - {$target} for user {$user->name}",
            'severity' => 'warning',
            'tags' => ['rbac', 'permissions'],
            'metadata' => array_merge(['target' => $target], $metadata),
        ]);
    }

    /**
     * Log data export event
     */
    public static function logDataExport($user, string $dataType, int $recordCount, array $metadata = []): AuditLogSimple
    {
        return AuditLogSimple::createEntry([
            'event' => 'data_export',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'module' => 'data',
            'action' => 'export',
            'description' => "Exported {$recordCount} {$dataType} records",
            'severity' => 'warning',
            'tags' => ['data_export', 'privacy'],
            'metadata' => array_merge([
                'data_type' => $dataType,
                'record_count' => $recordCount,
            ], $metadata),
        ]);
    }

    /**
     * Log bulk operation
     */
    public static function logBulkOperation($user, string $operation, int $affectedCount, string $modelType, array $metadata = []): AuditLogSimple
    {
        return AuditLogSimple::createEntry([
            'event' => 'bulk_operation',
            'auditable_type' => $modelType,
            'auditable_id' => null,
            'module' => 'bulk',
            'action' => "bulk_{$operation}",
            'description' => "Bulk {$operation}: {$affectedCount} {$modelType} records",
            'severity' => 'warning',
            'tags' => ['bulk_operation', 'data_modification'],
            'metadata' => array_merge([
                'operation' => $operation,
                'affected_count' => $affectedCount,
                'model_type' => $modelType,
                'performed_by' => $user->name,
            ], $metadata),
        ]);
    }

    /**
     * Log suspicious activity
     */
    public static function logSuspicious(string $description, array $metadata = [], $user = null): AuditLogSimple
    {
        return AuditLogSimple::createEntry([
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
     * Fire audit event
     */
    public static function fireEvent(string $eventName, array $data): void
    {
        Event::dispatch($eventName, $data);
    }

    /**
     * Get recent critical events
     */
    public static function getRecentCriticalEvents(int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLogSimple::critical()
            ->recent($hours)
            ->with(['user', 'auditable'])
            ->latest('created_at')
            ->get();
    }

    /**
     * Get security alerts
     */
    public static function getSecurityAlerts(int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLogSimple::whereJsonContains('metadata->tags', 'security')
            ->whereJsonContains('metadata->severity', 'critical')
            ->recent($hours)
            ->latest('created_at')
            ->get();
    }

    /**
     * Get user activity summary
     */
    public static function getUserActivitySummary($userId, int $days = 7): array
    {
        $startDate = now()->subDays($days);
        
        $activities = AuditLogSimple::byUser($userId)
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_activities' => $activities->count(),
            'by_module' => $activities->groupBy('module')->map->count(),
            'by_severity' => $activities->groupBy('severity')->map->count(),
            'recent_critical' => $activities->filter(function($log) {
                return $log->severity === 'critical';
            })->take(5),
            'login_count' => $activities->where('event', 'login')->count(),
            'failed_logins' => $activities->where('event', 'login_failed')->count(),
        ];
    }

    /**
     * Get system security overview
     */
    public static function getSecurityOverview(int $hours = 24): array
    {
        $startDate = now()->subHours($hours);
        
        $logs = AuditLogSimple::where('created_at', '>=', $startDate)->get();

        return [
            'total_events' => $logs->count(),
            'critical_events' => $logs->filter(function($log) {
                return $log->severity === 'critical';
            })->count(),
            'failed_logins' => $logs->where('event', 'login_failed')->count(),
            'successful_logins' => $logs->where('event', 'login')->count(),
            'rbac_changes' => $logs->filter(function($log) {
                return $log->module === 'rbac';
            })->count(),
            'data_exports' => $logs->where('event', 'data_export')->count(),
            'suspicious_activities' => $logs->where('event', 'suspicious_activity')->count(),
            'top_users' => $logs->whereNotNull('user_id')
                ->groupBy('user_id')
                ->map->count()
                ->sortDesc()
                ->take(10),
            'top_ips' => $logs->whereNotNull('ip_address')
                ->groupBy('ip_address')
                ->map->count()
                ->sortDesc()
                ->take(10),
        ];
    }

    /**
     * Get authentication description
     */
    private static function getAuthDescription(string $event, $user): string
    {
        $userName = $user ? $user->name : 'Unknown';
        
        return match($event) {
            'login' => "User {$userName} logged in successfully",
            'logout' => "User {$userName} logged out",
            'login_failed' => "Failed login attempt for {$userName}",
            'password_reset_requested' => "Password reset requested for {$userName}",
            'password_changed' => "Password changed for {$userName}",
            default => "Authentication event: {$event} for {$userName}",
        };
    }
}
