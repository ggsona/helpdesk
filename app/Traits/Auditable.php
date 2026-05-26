<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('create', null, $model->getDirtyForAudit());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirtyForAudit();
            if (!empty($dirty)) {
                // Get original values of only the dirty attributes
                $old = [];
                foreach ($dirty as $key => $value) {
                    $old[$key] = $model->getOriginal($key);
                }
                $model->logAudit('update', $old, $dirty);
            }
        });

        static::deleted(function ($model) {
            $model->logAudit('delete', $model->getAttributes(), null);
        });
    }

    protected function getDirtyForAudit(): array
    {
        $dirty = $this->getDirty();
        unset($dirty['updated_at']);
        unset($dirty['created_at']);
        return $dirty;
    }

    protected function logAudit(string $action, ?array $old, ?array $new)
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'auditable_type' => get_class($this),
                'auditable_id' => $this->getKey(),
                'action' => $action,
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => request() ? request()->ip() : null,
                'user_agent' => request() ? request()->userAgent() : null,
            ]);
        } catch (\Exception $e) {
            // Prevent audit failures from breaking the main transaction, especially during seeding/tests
            logger()->error("Failed to write audit log: " . $e->getMessage());
        }
    }
}
