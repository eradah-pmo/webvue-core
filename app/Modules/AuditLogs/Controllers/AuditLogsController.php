<?php

namespace App\Modules\AuditLogs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLogSimple;
use App\Modules\AuditLogs\Services\AuditLogsService;
use App\Modules\AuditLogs\Services\AuditLogFilterService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogsController extends Controller
{
    /**
     * The AuditLogs service instances.
     */
    private AuditLogsService $auditLogsService;
    private AuditLogFilterService $filterService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        AuditLogsService $auditLogsService,
        AuditLogFilterService $filterService
    ) {
        $this->auditLogsService = $auditLogsService;
        $this->filterService = $filterService;
    }

    /**
     * Display audit logs listing with filters
     */
    public function index(Request $request): Response
    {
        $this->authorize('audit-logs.view');

        $filters = $request->only([
            'event', 'module', 'severity', 'user_id', 
            'date_from', 'date_to', 'search'
        ]);
        
        $auditLogs = $this->auditLogsService->getPaginatedLogs($filters);
        $filterOptions = $this->filterService->getFilterOptions();

        return Inertia::render('AuditLogs/Index', [
            'auditLogs' => $auditLogs,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
        ]);
    }

    /**
     * Show audit log details
     */
    public function show(int $id): Response
    {
        $this->authorize('audit-logs.view');

        $auditLog = $this->auditLogsService->getLogById($id);
        
        abort_if(!$auditLog, 404, 'Audit log not found');

        return Inertia::render('AuditLogs/Show', [
            'auditLog' => $auditLog,
        ]);
    }
}
