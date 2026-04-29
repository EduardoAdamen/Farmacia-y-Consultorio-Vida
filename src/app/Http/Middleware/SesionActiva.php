<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SesionActiva
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check()) {
            $ultimaActividad = session('ultima_actividad');
            $timeout = config('session.lifetime', 20) * 60;

            if ($ultimaActividad && (time() - $ultimaActividad) > $timeout) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')
                    ->withErrors(['timeout' => 'Tu sesión expiró por inactividad.']);
            }
            session(['ultima_actividad' => time()]);
        }

        return $next($request);
    }
}
