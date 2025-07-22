<?php

namespace App\Core\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait HasAuditLog
{
    /**
     * Boot the trait
     */
    protected static function bootHasAuditLog()
    {
        static::created(function (Model $model) {
            $model->logAuditEvent('created');
        });

        static::updated(function (Model $model) {
            $model->logAuditEvent('updated');
        });

        static::deleted(function (Model $model) {
            $model->logAuditEvent('deleted');
        });
    }

    /**
     * Log audit event
     */
    protected function logAuditEvent(string $event)
    {
        $oldValues = null;
        $newValues = null;

        if ($event === 'updated') {
            $oldValues = $this->getOriginal();
            $newValues = $this->getAttributes();
        } elseif ($event === 'created') {
            $newValues = $this->getAttributes();
        } elseif ($event === 'deleted') {
            $oldValues = $this->getOriginal();
        }

        AuditLog::create([
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'user_id' => auth()->id(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $this->getAuditMetadata(),
        ]);
    }

    /**
     * Get additional metadata for audit log
     */
    protected function getAuditMetadata(): array
    {
        return [
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get audit logs for this model
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
