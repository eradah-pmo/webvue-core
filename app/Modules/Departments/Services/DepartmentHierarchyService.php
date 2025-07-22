<?php

namespace App\Modules\Departments\Services;

use App\Modules\Departments\Models\Department;
use Illuminate\Database\Eloquent\Collection;

class DepartmentHierarchyService
{
    /**
     * Get department hierarchy
     */
    public function getDepartmentHierarchy(): Collection
    {
        return Department::with(['children' => function($query) {
            $query->active()->ordered();
        }])
        ->rootDepartments()
        ->active()
        ->ordered()
        ->get();
    }

    /**
     * Get root departments
     */
    public function getRootDepartments(): Collection
    {
        return Department::rootDepartments()->active()->ordered()->get();
    }

    /**
     * Move department to new parent
     */
    public function moveDepartment(Department $department, ?int $newParentId): bool
    {
        if ($newParentId && $this->wouldCreateCircularReference($department->id, $newParentId)) {
            throw new \Exception('Cannot move department: would create circular reference.');
        }
        
        $department->parent_id = $newParentId;
        return $department->save();
    }

    /**
     * Reorder departments
     */
    public function reorderDepartments(array $departmentIds): bool
    {
        foreach ($departmentIds as $index => $departmentId) {
            Department::where('id', $departmentId)->update(['sort_order' => $index + 1]);
        }
        
        return true;
    }

    /**
     * Get next sort order for department
     */
    public function getNextSortOrder(?int $parentId): int
    {
        $query = Department::query();
        
        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }
        
        return $query->max('sort_order') + 1;
    }

    /**
     * Check if moving would create circular reference
     */
    private function wouldCreateCircularReference(int $departmentId, int $newParentId): bool
    {
        if ($departmentId === $newParentId) {
            return true;
        }
        
        $department = Department::find($departmentId);
        if (!$department) {
            return false;
        }
        
        $descendants = $department->getAllDescendants();
        return $descendants->contains('id', $newParentId);
    }
}
