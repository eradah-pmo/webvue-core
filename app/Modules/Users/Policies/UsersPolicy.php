<?php

namespace App\Modules\Users\Policies;

use App\Models\User;
use App\Modules\Users\Models\Users;

class UsersPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.view");
    }

    public function view(User $user, Users ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.view");
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.create");
    }

    public function update(User $user, Users ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.edit");
    }

    public function delete(User $user, Users ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.delete");
    }
}