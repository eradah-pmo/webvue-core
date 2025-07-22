<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Core\Traits\HasAuditLog;
use App\Core\Services\PermissionService;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity, HasAuditLog;

    /**
     * The guard name for Spatie Permission
     *
     * @var string
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'avatar',
        'locale',
        'timezone',
        'department_id',
        'preferences',
        'active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'preferences' => 'array',
            'active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'first_name', 'last_name', 'email', 'active', 'department_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: $this->name;
    }

    /**
     * Get the user's initials
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->full_name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }

    /**
     * Check if user has access to a specific scope (using new permission service)
     */
    public function hasAccess(string $scope, $scopeId = null): bool
    {
        $permissionService = app(PermissionService::class);
        
        // Check department-level access
        if ($scope === 'department') {
            return $permissionService->userHasDepartmentScopedPermission(
                $this, 
                'departments.view', 
                $scopeId
            );
        }

        // For other scopes, use role-based permissions only
        $permission = "access.{$scope}";
        return $permissionService->userHasPermission($this, $permission);
    }

    /**
     * Get user's accessible departments (using new permission service)
     */
    public function getAccessibleDepartments()
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->getUserAccessibleDepartments($this);
    }

    /**
     * Get users that this user can manage (using new permission service)
     */
    public function getManageableUsers()
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->getUserManageableUsers($this);
    }

    /**
     * Check if this user can manage another user (using new permission service)
     */
    public function canManageUser(User $targetUser): bool
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->canManageUser($this, $targetUser);
    }

    /**
     * Check if user has permission (role-based only)
     */
    public function hasRoleBasedPermission(string $permission): bool
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->userHasPermission($this, $permission);
    }

    /**
     * Check if user has scoped permission within a department
     */
    public function hasScopedPermission(string $permission, ?int $departmentId = null): bool
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->userHasScopedPermission($this, $permission, $departmentId);
    }

    /**
     * Get departments that this user manages
     */
    public function managedDepartments()
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    /**
     * Relationships
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Update last login information
     */
    public function updateLastLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for users in specific department
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}
