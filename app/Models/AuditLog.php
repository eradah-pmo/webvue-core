<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event',
        'auditable_type',
        'auditable_id',
        'user_id',
        'user_name',
        'user_email',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'old_values',
        'new_values',
        'changed_fields',
        'module',
        'action',
        'description',
        'severity',
        'session_id',
        'tags',
        'metadata',
        'occurred_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'changed_fields' => 'array',
            'tags' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Scope for specific event types
     */
    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope for specific model type
     */
    public function scopeForModel($query, string $modelType)
    {
        return $query->where('auditable_type', $modelType);
    }

    /**
     * Scope for specific user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific module
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope for specific severity
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('occurred_at', [$startDate, $endDate]);
    }

    /**
     * Scope for IP address
     */
    public function scopeByIp($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Get critical events
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Get recent events
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('occurred_at', '>=', now()->subHours($hours));
    }

    /**
     * Create audit log entry
     */
    public static function createEntry(array $data): self
    {
        // Automatically add request context if available
        if (request()) {
            $data = array_merge([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'session_id' => session()->getId(),
            ], $data);
        }

        // Add current user info if available
        if (auth()->check()) {
            $user = auth()->user();
            $data = array_merge([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
            ], $data);
        }

        // Set occurred_at if not provided
        if (!isset($data['occurred_at'])) {
            $data['occurred_at'] = now();
        }

        // Handle null auditable_id (set to 0 for events without specific entity)
        if (!isset($data['auditable_id']) || $data['auditable_id'] === null) {
            $data['auditable_id'] = 0;
        }

        return static::create($data);
    }
}
