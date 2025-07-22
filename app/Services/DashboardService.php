<?php

namespace App\Services;

use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Get advanced dashboard statistics with time comparisons
     */
    public function getAdvancedStats($user): array
    {
        return Cache::remember(
            "dashboard_stats_{$user->id}",
            now()->addMinutes(15),
            fn() => $this->calculateAdvancedStats($user)
        );
    }

    /**
     * Calculate advanced statistics
     */
    private function calculateAdvancedStats($user): array
    {
        $stats = [];
        
        // Users statistics
        if ($user->can('users.view')) {
            $stats['users'] = $this->getUsersStats($user);
            $stats['active_users'] = $this->getActiveUsersStats($user);
        }

        // Roles statistics
        if ($user->can('roles.view')) {
            $stats['roles'] = $this->getRolesStats();
        }

        // Departments statistics
        if ($user->can('departments.view')) {
            $stats['departments'] = $this->getDepartmentsStats($user);
        }

        // Activities statistics
        $stats['activities'] = $this->getActivitiesStats($user);

        return $stats;
    }

    /**
     * Get users statistics with comparisons
     */
    private function getUsersStats($user): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $query = User::query();
        
        if (!$user->hasRole('super-admin')) {
            $accessibleDepartments = $user->getAccessibleDepartments();
            $query->whereIn('department_id', $accessibleDepartments->pluck('id'));
        }

        $currentCount = $query->where('created_at', '>=', $currentMonth)->count();
        $lastMonthCount = $query->whereBetween('created_at', [
            $lastMonth, 
            $lastMonth->copy()->endOfMonth()
        ])->count();

        $totalCount = $query->count();
        $change = $this->calculatePercentageChange($currentCount, $lastMonthCount);

        return [
            'current' => $totalCount,
            'change' => $change['formatted'],
            'changeType' => $change['type'],
            'period' => 'vs last month',
            'chartData' => $this->getUsersChartData($user),
        ];
    }

    /**
     * Get active users statistics
     */
    private function getActiveUsersStats($user): array
    {
        $query = User::where('active', true);
        
        if (!$user->hasRole('super-admin')) {
            $accessibleDepartments = $user->getAccessibleDepartments();
            $query->whereIn('department_id', $accessibleDepartments->pluck('id'));
        }

        $currentCount = $query->count();
        $totalUsers = User::count();
        $percentage = $totalUsers > 0 ? round(($currentCount / $totalUsers) * 100, 1) : 0;

        return [
            'current' => $currentCount,
            'change' => "{$percentage}%",
            'changeType' => $percentage >= 80 ? 'positive' : ($percentage >= 60 ? 'neutral' : 'negative'),
            'period' => 'of total users',
            'chartData' => $this->getActiveUsersChartData($user),
        ];
    }

    /**
     * Get roles statistics
     */
    private function getRolesStats(): array
    {
        $currentCount = Role::count();
        $lastWeekCount = Role::where('created_at', '<=', now()->subWeek())->count();
        $change = $this->calculatePercentageChange($currentCount, $lastWeekCount);

        return [
            'current' => $currentCount,
            'change' => $change['formatted'],
            'changeType' => $change['type'],
            'period' => 'vs last week',
        ];
    }

    /**
     * Get departments statistics
     */
    private function getDepartmentsStats($user): array
    {
        if ($user->hasRole('super-admin')) {
            $currentCount = Department::count();
        } else {
            $currentCount = $user->getAccessibleDepartments()->count();
        }

        $activeCount = Department::where('active', true)->count();
        $percentage = $currentCount > 0 ? round(($activeCount / $currentCount) * 100, 1) : 0;

        return [
            'current' => $currentCount,
            'change' => "{$percentage}%",
            'changeType' => $percentage >= 90 ? 'positive' : 'neutral',
            'period' => 'active departments',
        ];
    }

    /**
     * Get activities statistics
     */
    private function getActivitiesStats($user): array
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        $query = Activity::query();
        
        if (!$user->hasRole('super-admin')) {
            $accessibleUserIds = $this->getAccessibleUserIds($user);
            if (!empty($accessibleUserIds)) {
                $query->whereIn('causer_id', $accessibleUserIds);
            } else {
                $query->where('causer_id', $user->id);
            }
        }

        $todayCount = $query->where('created_at', '>=', $today)->count();
        $yesterdayCount = $query->whereBetween('created_at', [
            $yesterday, 
            $yesterday->copy()->endOfDay()
        ])->count();

        $change = $this->calculatePercentageChange($todayCount, $yesterdayCount);

        return [
            'current' => $todayCount,
            'change' => $change['formatted'],
            'changeType' => $change['type'],
            'period' => 'vs yesterday',
            'chartData' => $this->getActivitiesChartData($user),
        ];
    }

    /**
     * Get system alerts
     */
    public function getSystemAlerts($user): array
    {
        $alerts = [];

        // Check for inactive users
        if ($user->can('users.view')) {
            $inactiveCount = User::where('active', false)->count();
            if ($inactiveCount > 0) {
                $alerts[] = [
                    'id' => 'inactive_users',
                    'type' => 'warning',
                    'severity' => 'medium',
                    'title' => 'Inactive Users Detected',
                    'message' => "There are {$inactiveCount} inactive users in the system.",
                    'timestamp' => now()->diffForHumans(),
                    'action' => [
                        'label' => 'View Users',
                        'url' => '/users?filter=inactive'
                    ]
                ];
            }
        }

        // Check for recent failed login attempts
        $failedLogins = Activity::where('description', 'like', '%failed%login%')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($failedLogins > 5) {
            $alerts[] = [
                'id' => 'failed_logins',
                'type' => 'security',
                'severity' => 'high',
                'title' => 'Multiple Failed Login Attempts',
                'message' => "There have been {$failedLogins} failed login attempts in the last hour.",
                'timestamp' => now()->diffForHumans(),
                'action' => [
                    'label' => 'View Security Logs',
                    'url' => '/audit-logs?filter=security'
                ]
            ];
        }

        // Check system health
        $recentActivities = Activity::where('created_at', '>=', now()->subMinutes(30))->count();
        if ($recentActivities === 0) {
            $alerts[] = [
                'id' => 'low_activity',
                'type' => 'info',
                'severity' => 'low',
                'title' => 'Low System Activity',
                'message' => 'No user activity detected in the last 30 minutes.',
                'timestamp' => now()->diffForHumans(),
            ];
        }

        return $alerts;
    }

    /**
     * Get chart data for users over time
     */
    private function getUsersChartData($user): array
    {
        $query = User::query();
        
        if (!$user->hasRole('super-admin')) {
            $accessibleDepartments = $user->getAccessibleDepartments();
            $query->whereIn('department_id', $accessibleDepartments->pluck('id'));
        }

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = $query->where('created_at', '<=', $date)->count();
            $data[] = $count;
        }

        return $data;
    }

    /**
     * Get chart data for active users
     */
    private function getActiveUsersChartData($user): array
    {
        return [80, 85, 78, 90, 88, 92, 95]; // Mock data for now
    }

    /**
     * Get chart data for activities
     */
    private function getActivitiesChartData($user): array
    {
        $query = Activity::query();
        
        if (!$user->hasRole('super-admin')) {
            $accessibleUserIds = $this->getAccessibleUserIds($user);
            if (!empty($accessibleUserIds)) {
                $query->whereIn('causer_id', $accessibleUserIds);
            } else {
                $query->where('causer_id', $user->id);
            }
        }

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = $query->whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return $data;
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentageChange($current, $previous): array
    {
        if ($previous == 0) {
            return [
                'formatted' => $current > 0 ? '+100%' : '0%',
                'type' => $current > 0 ? 'positive' : 'neutral'
            ];
        }

        $change = (($current - $previous) / $previous) * 100;
        $formatted = ($change > 0 ? '+' : '') . number_format($change, 1) . '%';
        $type = $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'neutral');

        return [
            'formatted' => $formatted,
            'type' => $type
        ];
    }

    /**
     * Get accessible user IDs for the current user
     */
    private function getAccessibleUserIds($user): array
    {
        if ($user->hasRole('super-admin')) {
            return User::pluck('id')->toArray();
        }

        if ($user->can('users.view')) {
            $accessibleDepartments = $user->getAccessibleDepartments();
            return User::whereIn('department_id', $accessibleDepartments->pluck('id'))
                ->pluck('id')
                ->toArray();
        }

        return [$user->id];
    }
}
