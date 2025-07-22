<?php

namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UsersService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['department', 'roles'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        
        $user = User::create($data);
        
        // Assign roles if provided
        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        
        return $user->load(['department', 'roles']);
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        
        $user->update($data);
        
        // Update roles if provided
        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        
        return $user->fresh(['department', 'roles']);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function find(int $id): ?User
    {
        return User::with(['department', 'roles'])->find($id);
    }

    public function findOrFail(int $id): User
    {
        return User::with(['department', 'roles'])->findOrFail($id);
    }

    public function resetPassword(User $user, string $password): bool
    {
        return $user->update(['password' => bcrypt($password)]);
    }

    public function toggleStatus(User $user): bool
    {
        return $user->toggleStatus();
    }
}