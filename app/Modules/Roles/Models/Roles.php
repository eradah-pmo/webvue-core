<?php

namespace App\Modules\Roles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;

class Roles extends Role
{
    use HasFactory, LogsActivity, HasAuditLog;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'active',
        'level',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'level' => 'integer',
        ];
    }

    // Relationships
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->morphedByMany(
            \App\Modules\Users\Models\Users::class,
            'model',
            config('permission.table_names.model_has_roles'),
            'role_id',
            'model_id'
        );
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    public function scopeByLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return ucfirst(str_replace('-', ' ', $this->name));
    }

    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    // Methods
    public function toggleStatus(): bool
    {
        $this->active = !$this->active;
        return $this->save();
    }

    public function canBeDeleted(): bool
    {
        // Super admin role cannot be deleted
        if ($this->name === 'super-admin') {
            return false;
        }
        
        // Role with users cannot be deleted
        return $this->users()->count() === 0;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(["name", "description", "active"])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // scopeActive already defined above
}