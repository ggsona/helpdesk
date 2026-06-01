<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        // Deactivated: Global model listener in EventServiceProvider handles this to prevent duplication.
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
