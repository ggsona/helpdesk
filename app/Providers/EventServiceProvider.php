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
        // Spatie LogsActivity maneja los eventos eloquent automáticamente.
        // Solo necesitamos registrar los eventos de autenticación.

        // --- Listeners de Sesión e Inicios de Sesión ---
        Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            try {
                activity('Autenticación')
                    ->causedBy($event->user)
                    ->event('login')
                    ->log('Inicio de sesión exitoso');
            } catch (\Exception $e) {
                logger()->error("Failed to write login audit: " . $e->getMessage());
            }
        });

        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            try {
                if ($event->user) {
                    activity('Autenticación')
                        ->causedBy($event->user)
                        ->event('logout')
                        ->log('Cierre de sesión');
                }
            } catch (\Exception $e) {
                logger()->error("Failed to write logout audit: " . $e->getMessage());
            }
        });

        Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            try {
                activity('Autenticación')
                    ->event('login_failed')
                    ->withProperties(['email' => $event->credentials['email'] ?? 'Desconocido'])
                    ->log('Intento de inicio de sesión fallido');
            } catch (\Exception $e) {
                logger()->error("Failed to write login failed audit: " . $e->getMessage());
            }
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
