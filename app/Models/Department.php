<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Core\Traits\HasAuditLog;

class Department extends Model
{
    use HasFactory, LogsActivity, HasAuditLog;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'manager_id',
        'active',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'description', 'parent_id', 'manager_id', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the parent department
     */
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Get child departments
     */
    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * Get all descendants (recursive)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the department manager
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get users in this department
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all users including from child departments
     */
    public function allUsers()
    {
        $userIds = collect([$this->id]);
        
        // Get all descendant department IDs
        $this->collectDescendantIds($userIds);
        
        return User::whereIn('department_id', $userIds);
    }

    /**
     * Get department hierarchy path
     */
    public function getHierarchyPathAttribute(): string
    {
        $path = collect([$this->name]);
        $parent = $this->parent;
        
        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }
        
        return $path->implode(' > ');
    }

    /**
     * Get department level (depth in hierarchy)
     */
    public function getLevelAttribute(): int
    {
        $level = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }
        
        return $level;
    }

    /**
     * Scope for active departments
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for root departments (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for departments with children
     */
    public function scopeWithChildren($query)
    {
        return $query->has('children');
    }

    /**
     * Collect descendant department IDs recursively
     */
    protected function collectDescendantIds($collection)
    {
        $children = $this->children;
        
        foreach ($children as $child) {
            $collection->push($child->id);
            $child->collectDescendantIds($collection);
        }
    }

    /**
     * Check if department can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Cannot delete if has users
        if ($this->users()->count() > 0) {
            return false;
        }

        // Cannot delete if has child departments
        if ($this->children()->count() > 0) {
            return false;
        }

        return true;
    }
}
