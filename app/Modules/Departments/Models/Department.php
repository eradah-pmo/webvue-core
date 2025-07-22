<?php

namespace App\Modules\Departments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    use HasFactory, LogsActivity, HasAuditLog;

    protected $table = 'departments';

    protected $fillable = [
        'name',
        'description',
        'code',
        'parent_id',
        'manager_id',
        'email',
        'phone',
        'address',
        'budget',
        'color',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'budget' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(\App\Modules\Users\Models\User::class, 'department_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Users\Models\User::class, 'manager_id');
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

    public function scopeRootDepartments(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubDepartments(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_name . ' / ' . $this->name;
        }
        return $this->name;
    }

    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    public function getChildrenCountAttribute(): int
    {
        return $this->children()->count();
    }

    public function getHierarchyLevelAttribute(): int
    {
        $level = 0;
        $parent = $this->parent;
        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }
        return $level;
    }

    // Methods
    public function toggleStatus(): bool
    {
        $this->active = !$this->active;
        return $this->save();
    }

    public function canBeDeleted(): bool
    {
        // Department with users cannot be deleted
        if ($this->users()->count() > 0) {
            return false;
        }
        
        // Department with sub-departments cannot be deleted
        if ($this->children()->count() > 0) {
            return false;
        }
        
        return true;
    }

    public function getAllDescendants()
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    public function getAncestors()
    {
        $ancestors = collect();
        $parent = $this->parent;
        
        while ($parent) {
            $ancestors->prepend($parent);
            $parent = $parent->parent;
        }
        
        return $ancestors;
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $ancestors = $this->getAncestors();
        
        foreach ($ancestors as $ancestor) {
            $breadcrumb[] = [
                'id' => $ancestor->id,
                'name' => $ancestor->name,
                'url' => route('departments.show', $ancestor->id),
            ];
        }
        
        $breadcrumb[] = [
            'id' => $this->id,
            'name' => $this->name,
            'url' => route('departments.show', $this->id),
        ];
        
        return $breadcrumb;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'description', 'code', 'parent_id', 'manager_id',
                'email', 'phone', 'address', 'budget', 'active', 'sort_order'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}