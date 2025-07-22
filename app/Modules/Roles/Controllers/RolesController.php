<?php

namespace App\Modules\Roles\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Roles\Models\Roles;
use App\Modules\Roles\Services\RolesService;
use App\Modules\Roles\Services\RoleFilterService;
use App\Modules\Roles\Services\RolePermissionService;
use App\Modules\Roles\Requests\StoreRolesRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RolesController extends Controller
{
    protected RolesService $rolesService;
    protected RoleFilterService $filterService;
    protected RolePermissionService $permissionService;

    public function __construct(
        RolesService $rolesService, 
        RoleFilterService $filterService, 
        RolePermissionService $permissionService
    ) {
        $this->rolesService = $rolesService;
        $this->filterService = $filterService;
        $this->permissionService = $permissionService;
        
        // Apply middleware
        $this->middleware('auth');
        $this->middleware('active-module:roles');
    }

    public function index(Request $request)
    {
        $this->authorizeAction('roles.view');
        
        $filters = $request->only(['search', 'active', 'level']);
        $roles = $this->filterService->getPaginatedRoles($filters, 15);
        $filterOptions = $this->filterService->getFilterOptions();
        
        return $this->renderIndexPage($roles, $filters, $filterOptions);
    }

    private function authorizeAction(string $permission): void
    {
        if (! Gate::allows($permission)) {
            abort(403, 'Unauthorized action');
        }
    }

    private function renderIndexPage($roles, $filters, $filterOptions)
    {
        return Inertia::render("Roles/Index", [
            "roles" => $roles,
            "filters" => $filters,
            "filterOptions" => $filterOptions,
        ]);
    }

    public function create()
    {
        // Check permission
        if (! Gate::allows('roles.create')) {
            abort(403, 'Unauthorized action');
        }

        $groupedPermissions = $this->permissionService->getGroupedPermissions();
        
        return Inertia::render("Roles/Form", [
            "groupedPermissions" => $groupedPermissions,
        ]);
    }

    public function store(StoreRolesRequest $request)
    {
        // Check permission
        if (! Gate::allows('roles.create')) {
            abort(403, 'Unauthorized action');
        }

        $role = $this->rolesService->create($request->validated());
        
        return redirect()->route("roles.index")
            ->with("success", __('roles.created_successfully'));
    }

    public function show(Roles $role)
    {
        // Check permission
        if (! Gate::allows('roles.view')) {
            abort(403, 'Unauthorized action');
        }

        $role->load(['permissions', 'users']);
        
        return Inertia::render("Roles/Show", [
            "role" => $role,
        ]);
    }

    public function edit(Roles $role)
    {
        // Check permission
        if (! Gate::allows('roles.edit')) {
            abort(403, 'Unauthorized action');
        }

        $role->load(['permissions']);
        $groupedPermissions = $this->permissionService->getGroupedPermissions();
        
        return Inertia::render("Roles/Form", [
            "role" => $role,
            "groupedPermissions" => $groupedPermissions,
            "isEdit" => true,
        ]);
    }

    public function update(StoreRolesRequest $request, Roles $role)
    {
        // Check permission
        if (! Gate::allows('roles.edit')) {
            abort(403, 'Unauthorized action');
        }

        $this->rolesService->update($role, $request->validated());
        
        return redirect()->route("roles.index")
            ->with("success", __('roles.updated_successfully'));
    }

    public function destroy(Roles $role)
    {
        $this->authorizeAction('roles.delete');
        
        try {
            $this->rolesService->delete($role);
            return $this->redirectWithSuccess('roles.deleted_successfully');
        } catch (\Exception $e) {
            return $this->redirectWithError($e->getMessage());
        }
    }

    private function redirectWithSuccess(string $messageKey)
    {
        return redirect()->route("roles.index")
            ->with("success", __($messageKey));
    }

    private function redirectWithError(string $message)
    {
        return redirect()->route("roles.index")
            ->with("error", $message);
    }

    public function toggleStatus(Roles $role)
    {
        // Check permission
        if (! Gate::allows('roles.edit')) {
            abort(403, 'Unauthorized action');
        }

        $this->rolesService->toggleStatus($role);
        
        return back()->with("success", __('roles.status_updated'));
    }

    public function duplicate(Roles $role, Request $request)
    {
        $this->authorizeAction('roles.create');
        
        $newName = $this->generateDuplicateName($role, $request);
        
        try {
            $this->rolesService->duplicateRole($role, $newName);
            return $this->redirectWithSuccess('roles.duplicated_successfully');
        } catch (\Exception $e) {
            return $this->redirectBackWithError($e->getMessage());
        }
    }

    private function generateDuplicateName(Roles $role, Request $request): string
    {
        return $request->input('name', $role->name . ' ' . __('roles.copy_suffix'));
    }

    private function redirectBackWithError(string $message)
    {
        return back()->with("error", $message);
    }
}