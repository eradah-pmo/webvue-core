<?php

namespace App\Modules\Settings\Policies;

use App\Models\User;
use App\Modules\Settings\Models\Settings;

class SettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.view");
    }

    public function view(User $user, Settings ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.view");
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.create");
    }

    public function update(User $user, Settings ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.edit");
    }

    public function delete(User $user, Settings ${{moduleName}}): bool
    {
        return $user->hasPermissionTo("{{moduleName}}.delete");
    }
}