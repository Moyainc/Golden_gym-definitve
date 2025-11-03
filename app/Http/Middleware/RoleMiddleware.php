<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = Auth::user();
        if (!$user || $user->rol !== $role) {
            abort(403, 'Acceso denegado');
        }

        return $next($request);
    }
}
