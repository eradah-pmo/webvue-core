<?php

namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasAuditLog;
use App\Models\Department;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity, HasAuditLog;
    
    /**
     * The guard name for Spatie Laravel Permission
     */
    protected $guard_name = 'web';
    
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    protected $table = "users";

    protected $fillable = [
        'first_name',
        'last_name', 
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'locale',
        'timezone',
        'department_id',
        'preferences',
        'active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'phone', 'department_id', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relations
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    // Methods
    public function hasAccess(string $scope, $scopeId = null): bool
    {
        // Check if user has access to specific scope (department, business unit, etc.)
        switch ($scope) {
            case 'department':
                return $this->department_id === $scopeId || $this->hasRole('super-admin');
            case 'all':
                return $this->hasRole(['super-admin', 'admin']);
            default:
                return false;
        }
    }

    public function toggleStatus(): bool
    {
        $this->active = !$this->active;
        return $this->save();
    }
    
    /**
     * Get departments accessible to this user based on roles and permissions
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAccessibleDepartments()
    {
        // Super admin can access all departments
        if ($this->hasRole('super-admin')) {
            return \App\Models\Department::all();
        }
        
        // Users with specific department access
        if ($this->department_id) {
            return \App\Models\Department::where('id', $this->department_id)->get();
        }
        
        // Default: no departments
        return \App\Models\Department::whereRaw('1=0')->get();
    }
}