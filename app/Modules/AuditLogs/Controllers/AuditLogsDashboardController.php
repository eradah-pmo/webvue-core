<?php

namespace App\Modules\AuditLogs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AuditLogs\Services\AuditLogsService;
use App\Modules\AuditLogs\Services\AuditLogDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogsDashboardController extends Controller
{
    /**
     * The services instances.
     */
    private AuditLogsService $auditLogsService;
    private AuditLogDashboardService $dashboardService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        AuditLogsService $auditLogsService,
        AuditLogDashboardService $dashboardService
    ) {
        $this->auditLogsService = $auditLogsService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the security dashboard with analytics.
     */
    public function index(Request $request): Response
    {
        $this->authorize('audit-logs.dashboard');

        $hours = $request->get('hours', 24);
        
        return Inertia::render('AuditLogs/Dashboard', [
            'dashboardData' => $this->dashboardService->getDashboardData($hours),
            'securityTrends' => $this->dashboardService->getSecurityTrends(30),
            'hours' => $hours,
        ]);
    }
}
