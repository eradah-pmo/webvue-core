<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Services\UserExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UsersExportController extends Controller
{
    /**
     * The export service instance.
     */
    private UserExportService $exportService;

    /**
     * Create a new controller instance.
     */
    public function __construct(UserExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export users to CSV file.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('users.export');

        $filters = $request->only([
            'search', 'role_id', 'department_id', 'status'
        ]);
        
        $limit = min($request->get('limit', 10000), 10000); // Ensure reasonable limit
        
        return $this->exportService->exportToCsv($filters, $limit);
    }
}
