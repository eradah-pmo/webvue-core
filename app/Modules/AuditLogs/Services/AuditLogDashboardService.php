<?php

namespace App\Modules\AuditLogs\Services;

use App\Models\AuditLogSimple;
use App\Helpers\AuditHelperSimple;
use Illuminate\Support\Collection;

class AuditLogDashboardService
{
    /**
     * Get security dashboard data
     */
    public function getDashboardData(int $hours = 24): array
    {
        return [
            'overview' => AuditHelperSimple::getSecurityOverview($hours),
            'critical_events' => AuditHelperSimple::getRecentCriticalEvents($hours),
            'security_alerts' => AuditHelperSimple::getSecurityAlerts($hours),
            'module_activity' => $this->getModuleActivity($hours),
            'hourly_activity' => $this->getHourlyActivity(),
            'top_users' => $this->getTopUsers($hours),
            'recent_logins' => $this->getRecentLogins($hours),
            'failed_attempts' => $this->getFailedAttempts($hours),
        ];
    }

    /**
     * Get activity by module
     */
    public function getModuleActivity(int $hours = 24): Collection
    {
        return AuditLogSimple::recent($hours)
            ->selectRaw('JSON_EXTRACT(metadata, "$.module") as module, COUNT(*) as count')
            ->whereNotNull('metadata')
            ->groupBy('module')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'module' => trim($item->module, '"'), // Remove JSON quotes
                    'count' => $item->count,
                ];
            });
    }

    /**
     * Get hourly activity chart data
     */
    public function getHourlyActivity(): array
    {
        $hourlyData = AuditLogSimple::recent(24)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $activityChart = [];
        for ($i = 0; $i < 24; $i++) {
            $activityChart[] = [
                'hour' => $i,
                'label' => sprintf('%02d:00', $i),
                'count' => $hourlyData->get($i)?->count ?? 0,
            ];
        }

        return $activityChart;
    }

    /**
     * Get top users by activity
     */
    public function getTopUsers(int $hours = 24): Collection
    {
        return AuditLogSimple::recent($hours)
            ->with('user')
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
    }

    /**
     * Get recent login activities
     */
    public function getRecentLogins(int $hours = 24): Collection
    {
        return AuditLogSimple::recent($hours)
            ->with('user')
            ->where('event', 'login')
            ->latest('created_at')
            ->limit(20)
            ->get();
    }

    /**
     * Get failed login attempts
     */
    public function getFailedAttempts(int $hours = 24): Collection
    {
        return AuditLogSimple::recent($hours)
            ->where('event', 'login_failed')
            ->selectRaw('ip_address, COUNT(*) as attempts, MAX(created_at) as last_attempt')
            ->groupBy('ip_address')
            ->orderByDesc('attempts')
            ->limit(10)
            ->get();
    }

    /**
     * Get security trends
     */
    public function getSecurityTrends(int $days = 30): array
    {
        $trends = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            
            $dayData = AuditLogSimple::whereDate('created_at', $date)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN event = "login" THEN 1 ELSE 0 END) as logins,
                    SUM(CASE WHEN event = "login_failed" THEN 1 ELSE 0 END) as failed_logins,
                    SUM(CASE WHEN JSON_EXTRACT(metadata, "$.severity") = "critical" THEN 1 ELSE 0 END) as critical_events
                ')
                ->first();
            
            $trends[] = [
                'date' => $date,
                'total' => $dayData->total ?? 0,
                'logins' => $dayData->logins ?? 0,
                'failed_logins' => $dayData->failed_logins ?? 0,
                'critical_events' => $dayData->critical_events ?? 0,
            ];
        }
        
        return $trends;
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(int $userId, int $days = 7): array
    {
        return AuditHelperSimple::getUserActivitySummary($userId, $days);
    }
}
