<?php

namespace App\Modules\AuditLogs\Services;

use App\Models\AuditLogSimple;
use Illuminate\Support\Collection;

class AuditLogExportService
{
    protected AuditLogFilterService $filterService;

    public function __construct(AuditLogFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Export audit logs to array
     */
    public function exportLogs(array $filters = [], int $limit = 10000): Collection
    {
        $query = AuditLogSimple::with(['user'])
            ->latest('created_at');

        // Apply filters
        $this->filterService->applyFilters($query, $filters);

        return $query->limit($limit)->get();
    }

    /**
     * Format logs for CSV export
     */
    public function formatForCsv(Collection $logs): array
    {
        $csvData = [];
        
        // Add headers
        $csvData[] = [
            'ID',
            'Event',
            'User',
            'IP Address',
            'Module',
            'Description',
            'Created At'
        ];
        
        // Add log data
        foreach ($logs as $log) {
            $csvData[] = [
                $log->id,
                $log->event,
                $log->user ? $log->user->name : 'System',
                $log->ip_address,
                $log->module ?? 'N/A',
                $log->description ?? '',
                $log->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        return $csvData;
    }

    /**
     * Format logs for PDF export
     */
    public function formatForPdf(Collection $logs): array
    {
        $data = [
            'title' => 'Audit Logs Export',
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'count' => $logs->count(),
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'event' => $log->event,
                    'user' => $log->user ? $log->user->name : 'System',
                    'ip_address' => $log->ip_address,
                    'module' => $log->module ?? 'N/A',
                    'description' => $log->description ?? '',
                    'created_at' => $log->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray()
        ];
        
        return $data;
    }

    /**
     * Clean old audit logs
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return AuditLogSimple::where('created_at', '<', $cutoffDate)
            ->where('event', '!=', 'security') // Keep security events longer
            ->whereJsonMissing('metadata.severity', 'critical') // Keep critical events
            ->delete();
    }
}
