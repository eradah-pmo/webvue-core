<?php

namespace App\Modules\Departments\Policies;

use App\Models\User;
use App\Modules\Departments\Models\Departments;

class DepartmentsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.view");
    }

    public function view(User $user, Departments ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.view");
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.create");
    }

    public function update(User $user, Departments ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.edit");
    }

    public function delete(User $user, Departments ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.delete");
    }
}