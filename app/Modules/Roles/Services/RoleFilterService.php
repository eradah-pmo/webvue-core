<?php

namespace App\Modules\Roles\Services;

use App\Modules\Roles\Models\Roles;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RoleFilterService
{
    /**
     * Get filter options for roles listing
     */
    public function getFilterOptions(): array
    {
        return [
            'statuses' => $this->getStatusOptions(),
            'levels' => $this->getLevelOptions(),
        ];
    }

    /**
     * Get paginated roles with filters applied
     */
    public function getPaginatedRoles(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildFilterQuery($filters);
        
        return $query->latest()->paginate($perPage);
    }

    /**
     * Get filtered roles for export (without pagination)
     */
    public function getFilteredRoles(array $filters = [], ?int $limit = null): Collection
    {
        $query = $this->buildFilterQuery($filters);
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->latest()->get();
    }

    /**
     * Build query with filters applied
     */
    private function buildFilterQuery(array $filters = [])
    {
        $query = Roles::query()->with(['permissions']);
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if (isset($filters['active']) && $filters['active'] !== '') {
            $query->where('active', (bool) $filters['active']);
        }
        
        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }
        
        // Additional filters can be added here
        
        return $query;
    }

    /**
     * Get status options for filter dropdown
     */
    private function getStatusOptions(): array
    {
        return [
            ['id' => '1', 'name' => 'نشط', 'name_en' => 'Active'],
            ['id' => '0', 'name' => 'غير نشط', 'name_en' => 'Inactive'],
        ];
    }

    /**
     * Get level options for filter dropdown
     */
    private function getLevelOptions(): array
    {
        // Get distinct role levels from database
        $levels = Roles::select('level')
            ->distinct()
            ->orderBy('level')
            ->get()
            ->pluck('level')
            ->toArray();
        
        // Convert to filter options format
        return array_map(function ($level) {
            return [
                'id' => $level,
                'name' => "المستوى {$level}",
                'name_en' => "Level {$level}",
            ];
        }, $levels);
    }
}
