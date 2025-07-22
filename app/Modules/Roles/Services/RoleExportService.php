<?php

namespace App\Modules\Roles\Services;

use Illuminate\Support\Facades\Auth;
use App\Helpers\AuditHelperSimple;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoleExportService
{
    /**
     * The Role filter service instance.
     */
    private RoleFilterService $filterService;

    /**
     * Create a new service instance.
     */
    public function __construct(RoleFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Export roles data to CSV file.
     */
    public function exportToCsv(array $filters = [], ?int $limit = 1000): StreamedResponse
    {
        // Limit to reasonable number to prevent memory issues
        $limit = min($limit ?? 1000, 1000);
        
        // Get filtered roles for export
        $roles = $this->filterService->getFilteredRoles($filters, $limit);

        // Log the export action
        AuditHelperSimple::logDataExport(
            Auth::user(),
            'roles',
            $roles->count(),
            [
                'filters' => $filters,
                'export_format' => 'csv',
            ]
        );

        // Generate CSV
        $filename = 'roles_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(
            function() use ($roles) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'ID',
                    'Name',
                    'Description',
                    'Level',
                    'Status',
                    'Permissions',
                    'Users Count',
                    'Created At',
                ]);

                // CSV data
                foreach ($roles as $role) {
                    $permissions = $role->permissions->pluck('name')->implode(', ');
                    
                    fputcsv($file, [
                        $role->id,
                        $role->name,
                        $role->description,
                        $role->level,
                        $role->active ? 'Active' : 'Inactive',
                        $permissions,
                        $role->users_count ?? $role->users()->count(),
                        $role->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            }, 
            200, 
            $headers
        );
    }

    /**
     * Export roles data to PDF format.
     */
    public function exportToPdf(array $filters = [], ?int $limit = 500): \Illuminate\Http\JsonResponse
    {
        // Implementation for PDF export would go here
        // This would typically use a library like dompdf, mpdf, or snappy
        
        // For now, we'll leave this as a placeholder
        // Example:
        // $roles = $this->filterService->getFilteredRoles($filters, $limit);
        // $pdf = PDF::loadView('roles.export-pdf', ['roles' => $roles]);
        // return $pdf->download('roles_export.pdf');
        
        // Since we don't have PDF implementation yet:
        return response()->json([
            'error' => 'PDF export is not implemented yet',
        ], 501);
    }
}
