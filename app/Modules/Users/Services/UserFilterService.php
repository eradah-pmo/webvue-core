<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;
use App\Models\Department;

class UserFilterService
{
    /**
     * Get filter options for users listing
     */
    public function getFilterOptions(): array
    {
        return [
            'roles' => $this->getRolesForFilter(),
            'departments' => $this->getDepartmentsForFilter(),
            'status' => $this->getStatusOptions(),
        ];
    }

    /**
     * Get paginated users with filters applied
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()->with(['department', 'roles']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role_id'])) {
            $query->whereHas('roles', function($q) use ($filters) {
                $q->where('id', $filters['role_id']);
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', (bool) $filters['status']);
        }

        // Other filters can be added here

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get filtered users for export (without pagination)
     */
    public function getFilteredUsers(array $filters = [], ?int $limit = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::query()->with(['department', 'roles']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role_id'])) {
            $query->whereHas('roles', function($q) use ($filters) {
                $q->where('id', $filters['role_id']);
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', (bool) $filters['status']);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->latest()->get();
    }

    /**
     * Get roles for filter dropdown
     */
    private function getRolesForFilter(): array
    {
        return Role::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            })
            ->toArray();
    }

    /**
     * Get departments for filter dropdown
     */
    private function getDepartmentsForFilter(): array
    {
        return Department::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                ];
            })
            ->toArray();
    }

    /**
     * Get status options for filter dropdown
     */
    private function getStatusOptions(): array
    {
        return [
            ['id' => '1', 'name' => 'Active'],
            ['id' => '0', 'name' => 'Inactive'],
        ];
    }
}
