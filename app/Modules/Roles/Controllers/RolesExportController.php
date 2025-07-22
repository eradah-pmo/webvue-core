<?php

namespace App\Modules\Roles\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Roles\Services\RoleExportService;
use App\Modules\Roles\Services\RoleFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RolesExportController extends Controller
{
    /**
     * The role export service instance
     */
    private RoleExportService $exportService;
    
    /**
     * The role filter service instance
     */
    private RoleFilterService $filterService;
    
    /**
     * Create a new controller instance
     */
    public function __construct(RoleExportService $exportService, RoleFilterService $filterService)
    {
        $this->exportService = $exportService;
        $this->filterService = $filterService;
        
        // Apply middleware
        $this->middleware('auth');
        $this->middleware('active-module:roles');
    }
    
    /**
     * Export roles in CSV format
     */
    public function csv(Request $request): StreamedResponse
    {
        // Check permission
        if (! Gate::allows('roles.export')) {
            abort(403, 'Unauthorized action');
        }
        
        // Get filter parameters from request
        $filters = $this->getFiltersFromRequest($request);
        
        // Get limit from request with a reasonable default
        $limit = $request->input('limit', 1000);
        
        // Export to CSV
        return $this->exportService->exportToCsv($filters, $limit);
    }
    
    /**
     * Export roles in PDF format
     */
    public function pdf(Request $request): \Illuminate\Http\Response
    {
        // Check permission
        if (! Gate::allows('roles.export')) {
            abort(403, 'Unauthorized action');
        }
        
        // Get filter parameters from request
        $filters = $this->getFiltersFromRequest($request);
        
        // Get limit from request with a reasonable default
        $limit = $request->input('limit', 500);
        
        // Export to PDF
        return $this->exportService->exportToPdf($filters, $limit);
    }
    
    /**
     * Extract filters from the request
     */
    private function getFiltersFromRequest(Request $request): array
    {
        return $request->only([
            'search',
            'active',
            'level',
            // Add other filter parameters as needed
        ]);
    }
}
