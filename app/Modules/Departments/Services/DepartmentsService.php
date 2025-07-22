<?php

namespace App\Modules\Departments\Services;

use App\Modules\Departments\Models\Department;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DepartmentsService
{
    protected DepartmentFilterService $filterService;
    protected DepartmentHierarchyService $hierarchyService;

    public function __construct(
        DepartmentFilterService $filterService,
        DepartmentHierarchyService $hierarchyService
    ) {
        $this->filterService = $filterService;
        $this->hierarchyService = $hierarchyService;
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->filterService->getPaginatedDepartments($filters, $perPage);
    }

    public function create(array $data): Department
    {
        $data['active'] = $data['active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? $this->hierarchyService->getNextSortOrder($data['parent_id'] ?? null);
        
        return Department::create($data);
    }

    public function update(Department $department, array $data): Department
    {
        $department->update($data);
        return $department->fresh();
    }

    public function delete(Department $department): bool
    {
        if (!$department->canBeDeleted()) {
            throw new \Exception('Cannot delete department with child departments');
        }
        
        return $department->delete();
    }

    public function find(int $id): ?Department
    {
        return Department::with(['parent', 'children', 'manager', 'users'])->find($id);
    }

    public function findOrFail(int $id): Department
    {
        return Department::with(['parent', 'children', 'manager', 'users'])->findOrFail($id);
    }

    public function getAll(): Collection
    {
        return Department::active()->ordered()->get();
    }

    public function getRootDepartments(): Collection
    {
        return $this->hierarchyService->getRootDepartments();
    }

    public function getDepartmentHierarchy(): Collection
    {
        return $this->hierarchyService->getDepartmentHierarchy();
    }

    public function toggleStatus(Department $department): bool
    {
        return $department->toggleStatus();
    }

    public function moveDepartment(Department $department, ?int $newParentId): bool
    {
        return $this->hierarchyService->moveDepartment($department, $newParentId);
    }

    public function reorderDepartments(array $departmentIds): bool
    {
        return $this->hierarchyService->reorderDepartments($departmentIds);
    }

    public function getDepartmentStats(Department $department): array
    {
        return [
            'users_count' => $department->users()->count(),
            'children_count' => $department->children()->count(),
            'active_children' => $department->children()->where('active', true)->count(),
            'active_users_count' => $department->users()->where('active', true)->count(),
        ];
    }

    public function searchDepartments(string $query, int $limit = 10): Collection
    {
        return $this->filterService->searchDepartments($query, $limit);
    }
}