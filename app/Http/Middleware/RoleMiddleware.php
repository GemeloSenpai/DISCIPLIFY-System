<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;
        
        if (!in_array($userRole, $roles)) {
            abort(403, 'Acceso no autorizado para tu rol');
        }

        return $next($request);
    }
}