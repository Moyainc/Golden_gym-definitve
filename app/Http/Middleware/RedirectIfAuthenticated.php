<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Redireccionar según el rol
            if (in_array($user->rol, ['Administrador', 'Contador'])) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->rol === 'Recepcionista') {
                return redirect()->route('admin.clientes');
            }

            // Por si hay otros roles que quieras manejar
            return redirect('/'); // Página por defecto
        }

        return $next($request);
    }
}
