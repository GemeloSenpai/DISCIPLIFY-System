<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Usuario no autenticado → redirigir al login (ya manejado por Breeze)
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Usuario autenticado pero sin el rol requerido → error 403
        if (!in_array(auth()->user()->rol, $roles)) {
            abort(403, 'Acceso no autorizado');
        }

        return $next($request);
    }
}