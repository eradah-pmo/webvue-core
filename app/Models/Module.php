<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Core\Traits\HasAuditLog;

class Module extends Model
{
    use HasFactory, LogsActivity, HasAuditLog;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'version',
        'active',
        'critical',
        'dependencies',
        'permissions',
        'navigation',
        'config',
        'installed_at',
        'last_updated',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'critical' => 'boolean',
            'dependencies' => 'array',
            'permissions' => 'array',
            'navigation' => 'array',
            'config' => 'array',
            'installed_at' => 'datetime',
            'last_updated' => 'datetime',
        ];
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'version', 'active', 'critical', 'dependencies'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Scope for active modules
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for critical modules
     */
    public function scopeCritical($query)
    {
        return $query->where('critical', true);
    }

    /**
     * Check if module can be disabled
     */
    public function canBeDisabled(): bool
    {
        // Critical modules cannot be disabled in safe mode
        if ($this->critical && config('modules.safe_mode', true)) {
            return false;
        }

        return true;
    }

    /**
     * Get dependent modules
     */
    public function getDependentModules()
    {
        return static::where('dependencies', 'like', '%"' . $this->name . '"%')
            ->where('active', true)
            ->get();
    }
}
