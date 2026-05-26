<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Event::listen('eloquent.created: *', function ($eventName, array $data) {
            $model = $data[0];
            static::logGlobalAudit($model, 'create');
        });

        Event::listen('eloquent.updated: *', function ($eventName, array $data) {
            $model = $data[0];
            static::logGlobalAudit($model, 'update');
        });

        Event::listen('eloquent.deleted: *', function ($eventName, array $data) {
            $model = $data[0];
            static::logGlobalAudit($model, 'delete');
        });

        // --- Listeners de Sesión e Inicios de Sesión ---
        Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            try {
                \App\Models\AuditLog::create([
                    'user_id' => $event->user->id,
                    'auditable_type' => get_class($event->user),
                    'auditable_id' => $event->user->id,
                    'action' => 'login',
                    'old_values' => null,
                    'new_values' => ['email' => $event->user->email, 'name' => $event->user->name],
                    'ip_address' => request() ? request()->ip() : null,
                    'user_agent' => request() ? request()->userAgent() : null,
                ]);
            } catch (\Exception $e) {
                logger()->error("Failed to write login audit: " . $e->getMessage());
            }
        });

        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            try {
                if ($event->user) {
                    \App\Models\AuditLog::create([
                        'user_id' => $event->user->id,
                        'auditable_type' => get_class($event->user),
                        'auditable_id' => $event->user->id,
                        'action' => 'logout',
                        'old_values' => null,
                        'new_values' => ['email' => $event->user->email, 'name' => $event->user->name],
                        'ip_address' => request() ? request()->ip() : null,
                        'user_agent' => request() ? request()->userAgent() : null,
                    ]);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to write logout audit: " . $e->getMessage());
            }
        });

        Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            try {
                \App\Models\AuditLog::create([
                    'user_id' => null,
                    'auditable_type' => \App\Models\User::class,
                    'auditable_id' => 0,
                    'action' => 'login_failed',
                    'old_values' => null,
                    'new_values' => ['credentials' => array_keys($event->credentials ?? [])],
                    'ip_address' => request() ? request()->ip() : null,
                    'user_agent' => request() ? request()->userAgent() : null,
                ]);
            } catch (\Exception $e) {
                logger()->error("Failed to write login failed audit: " . $e->getMessage());
            }
        });
    }

    protected static function logGlobalAudit($model, string $action)
    {
        if ($model instanceof \App\Models\AuditLog) {
            return;
        }

        $className = get_class($model);
        
        // Skip log if it's not a relevant auditable class
        if (strpos($className, 'App\\Models\\') !== 0 && strpos($className, 'Spatie\\Permission\\') !== 0) {
            return;
        }

        $old = null;
        $new = null;

        if ($action === 'create') {
            $new = $model->getAttributes();
            unset($new['updated_at'], $new['created_at']);
        } elseif ($action === 'update') {
            $dirty = $model->getDirty();
            unset($dirty['updated_at'], $dirty['created_at']);
            if (empty($dirty)) {
                return;
            }
            $old = [];
            foreach ($dirty as $key => $value) {
                $old[$key] = $model->getOriginal($key);
            }
            $new = $dirty;
        } elseif ($action === 'delete') {
            $old = $model->getAttributes();
        }

        try {
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'auditable_type' => $className,
                'auditable_id' => $model->getKey() ?? 0,
                'action' => $action,
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => request() ? request()->ip() : null,
                'user_agent' => request() ? request()->userAgent() : null,
            ]);
        } catch (\Exception $e) {
            logger()->error("Failed to write global audit log: " . $e->getMessage());
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
