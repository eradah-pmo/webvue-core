<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait HasAuditLog
{
    /**
     * Boot the HasAuditLog trait for a model.
     */
    public static function bootHasAuditLog()
    {
        // Log when model is created
        static::created(function (Model $model) {
            $model->logAuditEvent('created', [
                'new_values' => $model->getAttributes(),
                'description' => "Created {$model->getTable()} record",
                'severity' => 'info',
            ]);
        });

        // Log when model is updated
        static::updated(function (Model $model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();
            
            // Remove timestamps from changes if they're the only changes
            $significantChanges = array_diff_key($changes, array_flip(['updated_at']));
            
            if (!empty($significantChanges)) {
                $changedFields = array_keys($significantChanges);
                
                $model->logAuditEvent('updated', [
                    'old_values' => array_intersect_key($original, $changes),
                    'new_values' => $changes,
                    'changed_fields' => $changedFields,
                    'description' => "Updated {$model->getTable()} record: " . implode(', ', $changedFields),
                    'severity' => $model->isSecuritySensitiveUpdate($changedFields) ? 'warning' : 'info',
                ]);
            }
        });

        // Log when model is deleted
        static::deleted(function (Model $model) {
            $model->logAuditEvent('deleted', [
                'old_values' => $model->getOriginal(),
                'description' => "Deleted {$model->getTable()} record",
                'severity' => 'warning',
            ]);
        });
    }

    /**
     * Log an audit event for this model
     */
    public function logAuditEvent(string $event, array $data = []): AuditLog
    {
        $defaultData = [
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'module' => $this->getModuleName(),
            'action' => $this->getActionName($event),
        ];

        return AuditLog::createEntry(array_merge($defaultData, $data));
    }

    /**
     * Get the module name for this model
     */
    protected function getModuleName(): string
    {
        $className = class_basename($this);
        
        // Try to extract module from namespace
        $namespace = (new \ReflectionClass($this))->getNamespaceName();
        if (preg_match('/App\\\\Modules\\\\([^\\\\]+)/', $namespace, $matches)) {
            return strtolower($matches[1]);
        }
        
        // Fallback to model name
        return strtolower($className);
    }

    /**
     * Get action name based on event
     */
    protected function getActionName(string $event): string
    {
        $modelName = strtolower(class_basename($this));
        return "{$event}_{$modelName}";
    }

    /**
     * Check if the update involves security-sensitive fields
     */
    protected function isSecuritySensitiveUpdate(array $changedFields): bool
    {
        $sensitiveFields = $this->getSecuritySensitiveFields();
        return !empty(array_intersect($changedFields, $sensitiveFields));
    }

    /**
     * Get security-sensitive fields for this model
     */
    protected function getSecuritySensitiveFields(): array
    {
        // Default sensitive fields - can be overridden in models
        return [
            'password',
            'email',
            'phone',
            'permissions',
            'roles',
            'active',
            'status',
            'is_admin',
            'is_superuser',
            'access_level',
            'department_id',
            'manager_id',
        ];
    }

    /**
     * Get audit logs for this model
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('occurred_at');
    }

    /**
     * Get recent audit logs
     */
    public function recentAuditLogs(int $hours = 24)
    {
        return $this->auditLogs()->recent($hours);
    }

    /**
     * Get critical audit logs
     */
    public function criticalAuditLogs()
    {
        return $this->auditLogs()->critical();
    }

    /**
     * Log custom audit event
     */
    public function logCustomAudit(string $action, string $description, array $metadata = [], string $severity = 'info'): AuditLog
    {
        return $this->logAuditEvent('custom', [
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'severity' => $severity,
        ]);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $action, string $description, array $metadata = []): AuditLog
    {
        return $this->logAuditEvent('security', [
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'severity' => 'critical',
            'tags' => ['security', 'alert'],
        ]);
    }
}
