<?php

namespace App\Modules\AuditLogs\Services;

use App\Models\AuditLogSimple;

class AuditLogFilterService
{
    /**
     * Apply filters to query
     */
    public function applyFilters($query, array $filters): void
    {
        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (!empty($filters['module'])) {
            $query->byModule($filters['module']);
        }

        if (!empty($filters['severity'])) {
            $query->bySeverity($filters['severity']);
        }

        if (!empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                  ->orWhere('auditable_type', 'like', "%{$search}%")
                  ->orWhereJsonContains('metadata->description', $search)
                  ->orWhereJsonContains('metadata->action', $search);
            });
        }
    }

    /**
     * Get filter options for UI
     */
    public function getFilterOptions(): array
    {
        return [
            'events' => AuditLogSimple::distinct()
                ->pluck('event')
                ->filter()
                ->sort()
                ->values(),
            
            'modules' => AuditLogSimple::whereNotNull('metadata')
                ->get()
                ->pluck('module')
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            
            'severities' => ['info', 'warning', 'critical'],
            
            'users' => \App\Modules\Users\Models\Users::select('id', 'name', 'email')
                ->whereExists(function ($query) {
                    $query->select('id')
                        ->from('audit_logs')
                        ->whereColumn('audit_logs.user_id', 'users.id');
                })
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Create a base query with common conditions
     */
    public function createBaseQuery()
    {
        return AuditLogSimple::with(['user'])
            ->latest('created_at');
    }
}
