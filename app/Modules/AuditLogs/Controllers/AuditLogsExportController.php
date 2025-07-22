<?php

namespace App\Modules\AuditLogs\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers\AuditHelperSimple;
use App\Modules\AuditLogs\Services\AuditLogsService;
use App\Modules\AuditLogs\Services\AuditLogExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogsExportController extends Controller
{
    /**
     * The services instances.
     */
    private AuditLogsService $auditLogsService;
    private AuditLogExportService $exportService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        AuditLogsService $auditLogsService,
        AuditLogExportService $exportService
    ) {
        $this->auditLogsService = $auditLogsService;
        $this->exportService = $exportService;
    }

    /**
     * Export audit logs to CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('audit-logs.export');

        $filters = $request->only(['event', 'module', 'severity', 'user_id', 'date_from', 'date_to', 'search']);
        $limit = min($request->get('limit', 10000), 10000); // Ensure reasonable limit
        $format = $request->get('format', 'csv');
        
        // Log the export action
        AuditHelperSimple::logDataExport(
            auth()->user(),
            'audit_logs',
            null, // El recuento se establecerá en el servicio
            [
                'filters' => $filters,
                'export_format' => $format,
            ]
        );

        // Usar el servicio de exportación especializado
        return $this->exportService->exportToCsv($filters, $limit);
    }
}
