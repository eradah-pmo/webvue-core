<?php

namespace App\Modules\Roles\Services;

use App\Modules\Roles\Models\Roles;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

class RolesService
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Roles::query()->with(['permissions']);
        
        // Apply filters
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }
        
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }
        
        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Roles
    {
        // Set default values
        $data['guard_name'] = $data['guard_name'] ?? 'web';
        $data['active'] = $data['active'] ?? true;
        $data['level'] = $data['level'] ?? 1;
        
        $role = Roles::create($data);
        
        // Sync permissions if provided
        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        
        return $role;
    }

    public function update(Roles $role, array $data): Roles
    {
        $role->update($data);
        
        // Sync permissions if provided
        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        
        return $role->fresh();
    }

    public function delete(Roles $role): bool
    {
        // Check if role can be deleted
        if (!$role->canBeDeleted()) {
            throw new \Exception('This role cannot be deleted.');
        }
        
        return $role->delete();
    }

    public function find(int $id): ?Roles
    {
        return Roles::with(['permissions'])->find($id);
    }

    public function findOrFail(int $id): Roles
    {
        return Roles::with(['permissions'])->findOrFail($id);
    }

    public function getAll(): Collection
    {
        return Roles::active()->orderBy('level')->get();
    }

    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    public function toggleStatus(Roles $role): bool
    {
        return $role->toggleStatus();
    }

    public function duplicateRole(Roles $role, string $newName): Roles
    {
        $newRole = $this->create([
            'name' => $newName,
            'description' => $role->description . ' (Copy)',
            'guard_name' => $role->guard_name,
            'active' => $role->active,
            'level' => $role->level,
            'color' => $role->color,
        ]);
        
        // Copy permissions
        $newRole->syncPermissions($role->permissions);
        
        return $newRole;
    }
}