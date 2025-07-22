<?php

namespace App\Modules\AuditLogs\Services;

use App\Models\AuditLogSimple;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\AuditLogs\Services\AuditLogFilterService;
use App\Modules\AuditLogs\Services\AuditLogDashboardService;
use App\Modules\AuditLogs\Services\AuditLogExportService;

class AuditLogsService
{
    protected AuditLogFilterService $filterService;
    protected AuditLogDashboardService $dashboardService;
    protected AuditLogExportService $exportService;

    public function __construct(
        AuditLogFilterService $filterService,
        AuditLogDashboardService $dashboardService,
        AuditLogExportService $exportService
    ) {
        $this->filterService = $filterService;
        $this->dashboardService = $dashboardService;
        $this->exportService = $exportService;
    }

    /**
     * Get paginated audit logs with filters
     */
    public function getPaginatedLogs(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->filterService->createBaseQuery();
        
        // Apply filters
        $this->filterService->applyFilters($query, $filters);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get audit log by ID with relations
     */
    public function getLogById(int $id): ?AuditLogSimple
    {
        return AuditLogSimple::with(['user', 'auditable'])->find($id);
    }

    /**
     * Get security dashboard data
     */
    public function getDashboardData(int $hours = 24): array
    {
        return $this->dashboardService->getDashboardData($hours);
    }

    /**
     * Get activity by module
     */
    public function getModuleActivity(int $hours = 24): Collection
    {
        return $this->dashboardService->getModuleActivity($hours);
    }

    /**
     * Get hourly activity chart data
     */
    public function getHourlyActivity(): array
    {
        return $this->dashboardService->getHourlyActivity();
    }

    /**
     * Get top users by activity
     */
    public function getTopUsers(int $hours = 24): Collection
    {
        return $this->dashboardService->getTopUsers($hours);
    }

    /**
     * Get recent login activities
     */
    public function getRecentLogins(int $hours = 24): Collection
    {
        return $this->dashboardService->getRecentLogins($hours);
    }

    /**
     * Get failed login attempts
     */
    public function getFailedAttempts(int $hours = 24): Collection
    {
        return $this->dashboardService->getFailedAttempts($hours);
    }

    /**
     * Export audit logs to array
     */
    public function exportLogs(array $filters = [], int $limit = 10000)
    {
        return $this->exportService->exportLogs($filters, $limit);
    }

    /**
     * Get filter options for UI
     */
    public function getFilterOptions(): array
    {
        return $this->filterService->getFilterOptions();
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(int $userId, int $days = 7): array
    {
        return $this->dashboardService->getUserActivitySummary($userId, $days);
    }

    /**
     * Clean old audit logs
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        return $this->exportService->cleanOldLogs($daysToKeep);
    }

    /**
     * Get security trends
     */
    public function getSecurityTrends(int $days = 30): array
    {
        return $this->dashboardService->getSecurityTrends($days);
    }
}
