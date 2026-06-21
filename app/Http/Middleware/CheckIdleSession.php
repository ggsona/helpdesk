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
            $lastActivity = session('last_activity', now()->timestamp);
            $config = DB::table('configuraciones')->where('clave', 'sesion_timeout')->first();
            $timeout = $config ? $config->valor : 30; // Valor por defecto de seguridad si no hay nada en BD

            // Compatibilidad con sesiones guardadas como objetos Carbon o strings
            if ($lastActivity instanceof \Carbon\Carbon) {
                $lastActivityCarbon = $lastActivity;
            } else if (is_numeric($lastActivity)) {
                $lastActivityCarbon = \Carbon\Carbon::createFromTimestamp($lastActivity);
            } else {
                $lastActivityCarbon = \Carbon\Carbon::parse($lastActivity);
            }

            if (now()->diffInMinutes($lastActivityCarbon) >= $timeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['session' => 'Tu sesión ha expirado por inactividad.']);
            }

            session(['last_activity' => now()->timestamp]);
        }

        return $next($request);
    }
}
