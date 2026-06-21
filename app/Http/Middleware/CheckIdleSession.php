<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckIdleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity', now());
            $config = DB::table('configuraciones')->where('clave', 'sesion_timeout')->first();
            $timeout = $config ? $config->valor : 30; // Valor por defecto de seguridad si no hay nada en BD

            // Depuración: Verifica qué valor está obteniendo
            // \Log::info('Timeout configurado: ' . $timeout);
            // \Log::info('Dif minutos: ' . now()->diffInMinutes($lastActivity));

            if (now()->diffInMinutes($lastActivity) >= $timeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['session' => 'Tu sesión ha expirado por inactividad.']);
            }

            session(['last_activity' => now()]);
        }

        return $next($request);
    }
}
