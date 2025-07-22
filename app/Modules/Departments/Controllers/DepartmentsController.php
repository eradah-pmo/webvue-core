<?php

namespace App\Modules\Departments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Departments\Models\Department;
use App\Modules\Departments\Services\DepartmentsService;
use App\Modules\Departments\Requests\StoreDepartmentsRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentsController extends Controller
{
    protected DepartmentsService $departmentsService;

    public function __construct(DepartmentsService $departmentsService)
    {
        $this->departmentsService = $departmentsService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'active', 'parent_id', 'manager_id']);
        $departments = $this->departmentsService->paginate(15, $filters);
        $hierarchy = $this->departmentsService->getDepartmentHierarchy();
        
        return Inertia::render("Departments/Index", [
            "departments" => $departments,
            "hierarchy" => $hierarchy,
            "filters" => $filters,
        ]);
    }

    public function create()
    {
        $parentDepartments = $this->departmentsService->getAll();
        $managers = \App\Modules\Users\Models\User::active()->get(['id', 'name', 'email']);
        
        return Inertia::render("Departments/Form", [
            "parentDepartments" => $parentDepartments,
            "managers" => $managers,
        ]);
    }

    public function store(StoreDepartmentsRequest $request)
    {
        $department = $this->departmentsService->create($request->validated());
        
        return redirect()->route("departments.index")
            ->with("success", "Department created successfully");
    }

    public function show(Department $department)
    {
        $department->load(['parent', 'children', 'manager', 'users']);
        $stats = $this->departmentsService->getDepartmentStats($department);
        
        return Inertia::render("Departments/Show", [
            "department" => $department,
            "stats" => $stats,
        ]);
    }

    public function edit(Department $department)
    {
        $department->load(['parent', 'manager']);
        $parentDepartments = $this->departmentsService->getAll()->where('id', '!=', $department->id);
        $managers = \App\Modules\Users\Models\User::active()->get(['id', 'name', 'email']);
        
        return Inertia::render("Departments/Form", [
            "department" => $department,
            "parentDepartments" => $parentDepartments,
            "managers" => $managers,
            "isEdit" => true,
        ]);
    }

    public function update(StoreDepartmentsRequest $request, Department $department)
    {
        try {
            $this->departmentsService->update($department, $request->validated());
            return redirect()->route("departments.index")
                ->with("success", "Department updated successfully");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Department $department)
    {
        try {
            $this->departmentsService->delete($department);
            return redirect()->route("departments.index")
                ->with("success", "Department deleted successfully");
        } catch (\Exception $e) {
            return redirect()->route("departments.index")
                ->with("error", $e->getMessage());
        }
    }

    public function toggleStatus(Department $department)
    {
        $this->departmentsService->toggleStatus($department);
        
        return back()->with("success", "Department status updated successfully");
    }

    public function move(Department $department, Request $request)
    {
        $newParentId = $request->input('parent_id');
        
        try {
            $this->departmentsService->moveDepartment($department, $newParentId);
            return back()->with("success", "Department moved successfully");
        } catch (\Exception $e) {
            return back()->with("error", $e->getMessage());
        }
    }

    public function reorder(Request $request)
    {
        $departmentIds = $request->input('department_ids', []);
        
        try {
            $this->departmentsService->reorderDepartments($departmentIds);
            return back()->with("success", "Departments reordered successfully");
        } catch (\Exception $e) {
            return back()->with("error", $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $departments = $this->departmentsService->searchDepartments($query);
        
        return response()->json($departments);
    }
}