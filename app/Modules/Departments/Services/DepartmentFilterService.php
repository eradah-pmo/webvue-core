<?php

namespace App\Modules\Departments\Services;

use App\Modules\Departments\Models\Department;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DepartmentFilterService
{
    /**
     * Get paginated departments with filters
     */
    public function getPaginatedDepartments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Department::query()->with(['parent', 'manager', 'users']);
        
        $this->applyFilters($query, $filters);
        
        return $query->ordered()->paginate($perPage);
    }

    /**
     * Apply filters to query
     */
    public function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }
        
        if (isset($filters['parent_id'])) {
            if ($filters['parent_id'] === 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }
        
        if (isset($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }
    }

    /**
     * Search departments by query
     */
    public function searchDepartments(string $query, int $limit = 10): Collection
    {
        return Department::where('name', 'like', '%' . $query . '%')
            ->orWhere('code', 'like', '%' . $query . '%')
            ->active()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * Get filter options for UI
     */
    public function getFilterOptions(): array
    {
        return [
            'managers' => \App\Modules\Users\Models\User::active()
                ->whereHas('managedDepartments')
                ->get(['id', 'name', 'email']),
            'parent_departments' => Department::active()
                ->whereNull('parent_id')
                ->get(['id', 'name']),
        ];
    }
}
