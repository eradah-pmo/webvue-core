<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Models\User;
use App\Modules\Users\Services\UsersService;
use App\Modules\Users\Services\UserFilterService;
use App\Modules\Users\Requests\StoreUsersRequest;
use App\Core\Services\PermissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsersController extends Controller
{
    /**
     * The service instances.
     */
    protected UsersService $usersService;
    protected UserFilterService $filterService;
    protected PermissionService $permissionService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        UsersService $usersService,
        UserFilterService $filterService,
        PermissionService $permissionService
    ) {
        $this->usersService = $usersService;
        $this->filterService = $filterService;
        $this->permissionService = $permissionService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Check permission using new service
        if (!$this->permissionService->userHasPermission($user, 'users.view')) {
            abort(403, 'Insufficient permissions to view users');
        }
        
        $filters = $this->getRequestFilters($request);
        
        // Get users based on user's scope (department-based filtering)
        $manageableUsers = $this->permissionService->getUserManageableUsers($user);
        $users = $this->filterService->getPaginatedUsers($filters, $manageableUsers);
        $filterOptions = $this->filterService->getFilterOptions();
        
        return $this->renderIndexView($users, $filters, $filterOptions);
    }

    private function getRequestFilters(Request $request): array
    {
        return $request->only(['search', 'role_id', 'department_id', 'status']);
    }

    private function renderIndexView($users, $filters, $filterOptions)
    {
        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        
        // Check permission
        if (!$this->permissionService->userHasPermission($user, 'users.create')) {
            abort(403, 'Insufficient permissions to create users');
        }
        
        // Get accessible departments for this user
        $departments = $this->permissionService->getUserAccessibleDepartments($user);
        $roles = \Spatie\Permission\Models\Role::all();
        
        return Inertia::render("Users/Form", [
            "departments" => $departments,
            "roles" => $roles,
        ]);
    }

    public function store(StoreUsersRequest $request)
    {
        $currentUser = auth()->user();
        
        // Check permission
        if (!$this->permissionService->userHasPermission($currentUser, 'users.create')) {
            abort(403, 'Insufficient permissions to create users');
        }
        
        $user = $this->usersService->create($request->validated());
        
        return redirect()->route("users.index")
            ->with("success", "User created successfully");
    }

    public function show(User $user)
    {
        $user->load(['department', 'roles']);
        
        return Inertia::render("Users/Show", [
            "user" => $user,
        ]);
    }

    public function edit(User $user)
    {
        $currentUser = auth()->user();
        
        // Check if current user can manage this user
        if (!$this->permissionService->canManageUser($currentUser, $user)) {
            abort(403, 'Insufficient permissions to edit this user');
        }
        
        $user->load(['department', 'roles']);
        $formData = $this->getEditFormData($user);
        
        return Inertia::render("Users/Form", $formData);
    }

    private function getEditFormData(User $user): array
    {
        $currentUser = auth()->user();
        
        // Get accessible departments for current user
        $departments = $this->permissionService->getUserAccessibleDepartments($currentUser);
        
        return [
            "user" => $user,
            "departments" => $departments,
            "roles" => \Spatie\Permission\Models\Role::all(),
            "isEdit" => true,
        ];
    }

    public function update(StoreUsersRequest $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Check if current user can manage this user
        if (!$this->permissionService->canManageUser($currentUser, $user)) {
            abort(403, 'Insufficient permissions to update this user');
        }
        
        $this->usersService->update($user, $request->validated());
        
        return redirect()->route("users.index")
            ->with("success", "User updated successfully");
    }

    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        // Check if current user can manage this user
        if (!$this->permissionService->canManageUser($currentUser, $user)) {
            abort(403, 'Insufficient permissions to delete this user');
        }
        
        // Additional check for delete permission
        if (!$this->permissionService->userHasPermission($currentUser, 'users.delete')) {
            abort(403, 'Insufficient permissions to delete users');
        }
        
        $this->usersService->delete($user);
        
        return redirect()->route("users.index")
            ->with("success", "User deleted successfully");
    }

    public function toggleStatus(User $user)
    {
        $user->toggleStatus();
        
        return back()->with("success", "User status updated successfully");
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->validatePasswordRequest($request);
        $this->usersService->resetPassword($user, $request->password);
        
        return back()->with("success", "Password reset successfully");
    }

    /**
     * Validate password reset request.
     */
    private function validatePasswordRequest(Request $request): void
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
    }
}