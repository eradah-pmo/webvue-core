<?php

namespace App\Modules\Users\Services;

use Illuminate\Support\Facades\Auth;
use App\Helpers\AuditHelperSimple;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserExportService
{
    /**
     * The User filter service instance.
     */
    private UserFilterService $filterService;

    /**
     * Create a new service instance.
     */
    public function __construct(UserFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Export users data to CSV file.
     */
    public function exportToCsv(array $filters = [], ?int $limit = 10000): StreamedResponse
    {
        // Limit to reasonable number to prevent memory issues
        $limit = min($limit ?? 10000, 10000);
        
        // Get filtered users for export
        $users = $this->filterService->getFilteredUsers($filters, $limit);

        // Log the export action
        AuditHelperSimple::logDataExport(
            Auth::user(),
            'users',
            $users->count(),
            [
                'filters' => $filters,
                'export_format' => 'csv',
            ]
        );

        // Generate CSV
        $filename = 'users_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(
            function() use ($users) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'ID',
                    'Name',
                    'Email',
                    'Department',
                    'Roles',
                    'Status',
                    'Created At',
                    'Last Login',
                ]);

                // CSV data
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->department ? $user->department->name : '',
                        $user->roles->pluck('name')->implode(', '),
                        $user->is_active ? 'Active' : 'Inactive',
                        $user->created_at->format('Y-m-d H:i:s'),
                        $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                    ]);
                }

                fclose($file);
            }, 
            200, 
            $headers
        );
    }

    /**
     * Export users data to PDF format.
     */
    public function exportToPdf(array $filters = [], ?int $limit = 1000): \Illuminate\Http\Response
    {
        // Implementation for PDF export would go here
        // This would typically use a library like dompdf, mpdf, or snappy
        
        // For now, we'll leave this as a placeholder
        // Example:
        // $users = $this->filterService->getFilteredUsers($filters, $limit);
        // $pdf = PDF::loadView('users.export-pdf', ['users' => $users]);
        // return $pdf->download('users_export.pdf');
        
        // Since we don't have PDF implementation yet:
        return response()->json([
            'error' => 'PDF export is not implemented yet',
        ], 501);
    }
}
