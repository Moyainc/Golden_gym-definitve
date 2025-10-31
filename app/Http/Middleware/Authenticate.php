<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
  public function handle(Request $request, Closure $next, ...$guards)
  {
    if (!Auth::check()) {
      // Redirige al login si no está logeado
      return redirect()->route('login')
        ->with('error', 'Debes iniciar sesión para continuar.');
    }

    return $next($request);
  }
}
