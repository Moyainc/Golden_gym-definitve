<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Usuario;

class LoginController extends Controller
{
  public function loginUsuario(Request $request)
  {
    //Protección contra ataques de fuerza bruta
    $this->checkRateLimit($request);

    $validator = Validator::make($request->all(), [
      'correo' => 'required|email|exists:usuarios,correo',
      'password' => 'required|string|min:6|max:100',
    ], [
      'correo.required' => 'El correo es obligatorio.',
      'correo.email' => 'Debe ser un correo válido.',
      'correo.exists' => 'No se encontró este correo.',
      'password.required' => 'La contraseña es obligatoria.',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'errors' => $validator->errors(),
      ], 422);
    }

    $usuario = Usuario::where('correo', $request->correo)->first();

    if (!$usuario || !Hash::check($request->password, $usuario->password)) {
      $this->incrementRateLimit($request);
      return response()->json([
        'success' => false,
        'message' => 'Correo o contraseña incorrectos.',
      ], 401);
    }

    //Autenticar usuario manualmente (sin attempt)
    Auth::login($usuario, $request->boolean('remember'));

    //Regenerar sesión para prevenir fijación
    $request->session()->regenerate();


    $usuario->update(['ultimo_acceso' => now()]);

    //Limpiar contador de intentos
    $this->clearRateLimit($request);

    // Respuesta segura
    return response()->json([
      'success' => true,
      'message' => 'Inicio de sesión exitoso.',
      'user' => [
        'nombre' => $usuario->nombre_completo,
      ],
      'redirect' => route('admin.dashboard'),
    ], 200);
  }

  // 🧠 Protección de intentos
  private function checkRateLimit(Request $request)
  {
    $key = 'login:' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 5)) {
      $seconds = RateLimiter::availableIn($key);
      throw ValidationException::withMessages([
        'correo' => ["Demasiados intentos. Intenta de nuevo en {$seconds} segundos."],
      ])->status(429);
    }
  }

  private function incrementRateLimit(Request $request)
  {
    RateLimiter::hit('login:' . $request->ip(), 300);
  }

  private function clearRateLimit(Request $request)
  {
    RateLimiter::clear('login:' . $request->ip());
  }

  // 🚪 Logout seguro
  public function logoutUsuario(Request $request)
  {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
  }
}
