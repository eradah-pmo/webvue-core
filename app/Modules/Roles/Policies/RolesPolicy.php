<?php

namespace App\Modules\Roles\Policies;

use App\Models\User;
use App\Modules\Roles\Models\Roles;

class RolesPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.view");
    }

    public function view(User $user, Roles ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.view");
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.create");
    }

    public function update(User $user, Roles ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.edit");
    }

    public function delete(User $user, Roles ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.delete");
    }
}