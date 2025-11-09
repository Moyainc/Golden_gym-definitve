<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
{
    $user = $request->user();

    if (
        !$user ||
        !in_array(
            strtolower(trim($user->rol)),
            array_map(fn($r) => strtolower(trim($r)), $roles)
        )
    ) {
        abort(403, 'Acceso no autorizado.');
    }

    return $next($request);
}

}
