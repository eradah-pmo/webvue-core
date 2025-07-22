<?php

namespace App\Traits;

use App\Models\AuditLogSimple;
use App\Helpers\AuditHelperSimple;

trait HasAuditLogSimple
{
    /**
     * Boot the trait
     */
    protected static function bootHasAuditLogSimple()
    {
        static::created(function ($model) {
            $model->logAuditEvent('created');
        });

        static::updated(function ($model) {
            $model->logAuditEvent('updated');
        });

        static::deleted(function ($model) {
            $model->logAuditEvent('deleted');
        });
    }

    /**
     * Log audit event for this model
     */
    protected function logAuditEvent(string $event): void
    {
        $oldValues = null;
        $newValues = null;
        $changedFields = [];

        if ($event === 'updated' && $this->isDirty()) {
            $oldValues = $this->getOriginal();
            $newValues = $this->getAttributes();
            $changedFields = array_keys($this->getDirty());
            
            // Filter sensitive fields
            $sensitiveFields = $this->getSensitiveFields();
            foreach ($sensitiveFields as $field) {
                if (isset($oldValues[$field])) {
                    $oldValues[$field] = '[REDACTED]';
                }
                if (isset($newValues[$field])) {
                    $newValues[$field] = '[REDACTED]';
                }
            }
        } elseif ($event === 'created') {
            $newValues = $this->getAttributes();
            $sensitiveFields = $this->getSensitiveFields();
            foreach ($sensitiveFields as $field) {
                if (isset($newValues[$field])) {
                    $newValues[$field] = '[REDACTED]';
                }
            }
        } elseif ($event === 'deleted') {
            $oldValues = $this->getOriginal();
            $sensitiveFields = $this->getSensitiveFields();
            foreach ($sensitiveFields as $field) {
                if (isset($oldValues[$field])) {
                    $oldValues[$field] = '[REDACTED]';
                }
            }
        }

        AuditLogSimple::createEntry([
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $changedFields,
            'module' => $this->getAuditModule(),
            'action' => $this->getAuditAction($event),
            'description' => $this->getAuditDescription($event),
            'severity' => $this->getAuditSeverity($event),
            'tags' => $this->getAuditTags($event),
        ]);
    }

    /**
     * Log custom audit event
     */
    public function logCustomAudit(string $event, string $description, array $metadata = [], string $severity = 'info'): void
    {
        AuditLogSimple::createEntry([
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'module' => $this->getAuditModule(),
            'action' => $event,
            'description' => $description,
            'severity' => $severity,
            'tags' => array_merge($this->getAuditTags($event), ['custom']),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log security event related to this model
     */
    public function logSecurityEvent(string $action, string $description, array $metadata = []): void
    {
        AuditHelperSimple::logSecurity($action, $description, array_merge([
            'model_type' => get_class($this),
            'model_id' => $this->getKey(),
            'model_identifier' => $this->getAuditIdentifier(),
        ], $metadata), auth()->user());
    }

    /**
     * Get audit logs for this model
     */
    public function auditLogs()
    {
        return AuditLogSimple::where('auditable_type', get_class($this))
            ->where('auditable_id', $this->getKey())
            ->latest('created_at');
    }

    /**
     * Get recent audit logs
     */
    public function recentAuditLogs(int $hours = 24)
    {
        return $this->auditLogs()
            ->recent($hours)
            ->get();
    }

    /**
     * Get sensitive fields that should be redacted in logs
     * Override this method in your model to specify sensitive fields
     */
    protected function getSensitiveFields(): array
    {
        return ['password', 'remember_token', 'api_token'];
    }

    /**
     * Get audit module name
     * Override this method in your model
     */
    protected function getAuditModule(): string
    {
        $className = class_basename($this);
        return strtolower(str_replace('\\', '_', $className));
    }

    /**
     * Get audit action for event
     * Override this method in your model for custom actions
     */
    protected function getAuditAction(string $event): string
    {
        return $event;
    }

    /**
     * Get audit description for event
     * Override this method in your model for custom descriptions
     */
    protected function getAuditDescription(string $event): string
    {
        $identifier = $this->getAuditIdentifier();
        $modelName = class_basename($this);
        
        return match($event) {
            'created' => "{$modelName} '{$identifier}' was created",
            'updated' => "{$modelName} '{$identifier}' was updated",
            'deleted' => "{$modelName} '{$identifier}' was deleted",
            default => "{$modelName} '{$identifier}' - {$event}",
        };
    }

    /**
     * Get audit severity for event
     * Override this method in your model for custom severity levels
     */
    protected function getAuditSeverity(string $event): string
    {
        return match($event) {
            'deleted' => 'warning',
            'created', 'updated' => 'info',
            default => 'info',
        };
    }

    /**
     * Get audit tags for event
     * Override this method in your model for custom tags
     */
    protected function getAuditTags(string $event): array
    {
        $modelName = strtolower(class_basename($this));
        return [$modelName, $event];
    }

    /**
     * Get model identifier for audit logs
     * Override this method in your model to customize identifier
     */
    protected function getAuditIdentifier(): string
    {
        if (isset($this->attributes['name'])) {
            return $this->attributes['name'];
        }
        
        if (isset($this->attributes['title'])) {
            return $this->attributes['title'];
        }
        
        if (isset($this->attributes['email'])) {
            return $this->attributes['email'];
        }
        
        return "ID: {$this->getKey()}";
    }

    /**
     * Check if model has audit logs
     */
    public function hasAuditLogs(): bool
    {
        return $this->auditLogs()->exists();
    }

    /**
     * Get audit summary for this model
     */
    public function getAuditSummary(int $days = 30): array
    {
        $logs = $this->auditLogs()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        return [
            'total_events' => $logs->count(),
            'by_event' => $logs->groupBy('event')->map->count(),
            'by_severity' => $logs->groupBy('severity')->map->count(),
            'first_event' => $logs->last()?->created_at,
            'last_event' => $logs->first()?->created_at,
            'unique_users' => $logs->whereNotNull('user_id')->pluck('user_id')->unique()->count(),
        ];
    }
}
