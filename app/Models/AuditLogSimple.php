<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLogSimple extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    /**
     * The attributes that are mass assignable.
     * Based on the current table structure (12 columns)
     */
    protected $fillable = [
        'event',
        'auditable_type',
        'auditable_id',
        'user_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Users\Models\User::class);
    }

    /**
     * Get the auditable model
     */
    public function auditable(): MorphTo
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
     * Scope for recent events
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Create audit log entry (simplified version)
     */
    public static function createEntry(array $data): self
    {
        // Map new fields to existing fields or metadata
        $mappedData = [
            'event' => $data['event'] ?? 'unknown',
            'auditable_type' => $data['auditable_type'] ?? null,
            'auditable_id' => $data['auditable_id'] ?? null,
            'user_id' => $data['user_id'] ?? auth()->id(),
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'ip_address' => $data['ip_address'] ?? request()?->ip(),
            'user_agent' => $data['user_agent'] ?? request()?->userAgent(),
            'metadata' => array_merge($data['metadata'] ?? [], [
                // Store additional fields in metadata
                'module' => $data['module'] ?? null,
                'action' => $data['action'] ?? null,
                'description' => $data['description'] ?? null,
                'severity' => $data['severity'] ?? 'info',
                'tags' => $data['tags'] ?? [],
                'url' => $data['url'] ?? request()?->fullUrl(),
                'method' => $data['method'] ?? request()?->method(),
                'session_id' => $data['session_id'] ?? session()?->getId(),
                'user_name' => $data['user_name'] ?? auth()->user()?->name,
                'user_email' => $data['user_email'] ?? auth()->user()?->email,
                'changed_fields' => $data['changed_fields'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now()->toISOString(),
            ]),
        ];

        return static::create($mappedData);
    }

    /**
     * Get module from metadata
     */
    public function getModuleAttribute(): ?string
    {
        return $this->metadata['module'] ?? null;
    }

    /**
     * Get action from metadata
     */
    public function getActionAttribute(): ?string
    {
        return $this->metadata['action'] ?? null;
    }

    /**
     * Get description from metadata
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->metadata['description'] ?? null;
    }

    /**
     * Get severity from metadata
     */
    public function getSeverityAttribute(): string
    {
        return $this->metadata['severity'] ?? 'info';
    }

    /**
     * Get tags from metadata
     */
    public function getTagsAttribute(): array
    {
        return $this->metadata['tags'] ?? [];
    }

    /**
     * Scope for critical events
     */
    public function scopeCritical($query)
    {
        return $query->whereJsonContains('metadata->severity', 'critical');
    }

    /**
     * Scope by module
     */
    public function scopeByModule($query, string $module)
    {
        return $query->whereJsonContains('metadata->module', $module);
    }

    /**
     * Scope by severity
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->whereJsonContains('metadata->severity', $severity);
    }
}
