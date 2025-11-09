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
use App\Models\SeguridadLog;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
  public function loginUsuario(Request $request)
  {
    // Verifica si el usuario está bloqueado
    if ($this->estaBloqueado($request)) {
      return response()->json([
        'success' => false,
        'message' => 'Demasiados intentos fallidos. Intenta nuevamente en 10 minutos.',
      ], 429);
    }

    $this->checkRateLimit($request);

    // Validación básica
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

    $ip = $request->ip();
    $userAgent = $request->header('User-Agent');
    $ubicacion = $this->obtenerUbicacion($ip);

    $usuario = Usuario::where('correo', $request->correo)->first();

    // Si el correo o contraseña es incorrecta
    if (!$usuario || !Hash::check($request->password, $usuario->password)) {
      $this->incrementRateLimit($request);
      $this->registrarIntentoFallido($request, $usuario);
      $this->verificarBloqueoUsuario($request, $usuario);

      $key = 'login:' . $request->ip();
      $maxAttempts = 4;
      $attempts = RateLimiter::attempts($key);
      $remaining = $maxAttempts - $attempts;

      $message = ($remaining <= 2 && $remaining > 0)
        ? "Te faltan {$remaining} intento(s) antes de que tu cuenta se bloquee temporalmente."
        : "Correo o contraseña incorrectos.";

      $status = ($remaining <= 2 && $remaining > 0) ? 429 : 401;

      return response()->json([
        'success' => false,
        'message' => $message,
      ], $status);
    }



    // Inicio de sesión correcto
    Auth::login($usuario); // 🔹 Sin “remember”
    $request->session()->regenerate();

    session(['ultimo_acceso_anterior' => $usuario->ultimo_acceso]);
    $usuario->update(['ultimo_acceso' => now()]);

    $this->clearRateLimit($request);

    // Redirección según rol
    switch ($usuario->rol) {
      case 'Administrador':
        $redirect = route('admin.dashboard');
        break;
      case 'Recepcionista':
        $redirect = route('admin.clientes');
        break;
      case 'Contador':
        $redirect = route('admin.dashboard');
        break;
      default:
        Auth::logout();
        return response()->json([
          'success' => false,
          'message' => 'Tu rol no tiene permisos para acceder al sistema.',
        ], 403);
    }

    return response()->json([
      'success' => true,
      'message' => 'Inicio de sesión exitoso.',
      'user' => [
        'nombre' => $usuario->nombre_completo,
        'rol' => $usuario->rol,
      ],
      'redirect' => $redirect,
    ], 200);
  }

  // ---------------------------------------------------------
  // 🔐 Funciones auxiliares de seguridad
  // ---------------------------------------------------------

  protected function estaBloqueado(Request $request)
  {
    $correo = $request->correo;
    return cache()->get("bloqueado_{$correo}");
  }

  protected function registrarIntentoFallido(Request $request, ?Usuario $usuario)
  {
    $ip = $request->ip();
    $userAgent = $request->header('User-Agent');
    $ubicacion = $this->obtenerUbicacion($ip);

    SeguridadLog::create([
      'usuario_id' => $usuario?->id, // null si correo no existe
      'correo_intentado' => $request->correo,
      'exitoso' => false,
      'ip' => $ip,
      'dispositivo' => substr($userAgent, 0, 200),
      'ubicacion' => $ubicacion,
      'fecha_intento' => now(),
    ]);
  }

  protected function verificarBloqueoUsuario(Request $request, ?Usuario $usuario)
  {
    $correo = $request->correo;

    // Contar fallos recientes del mismo correo en 15 minutos
    $fallos = SeguridadLog::where('correo_intentado', $correo)
      ->where('exitoso', false)
      ->where('fecha_intento', '>=', now()->subMinutes(15))
      ->count();

    // Bloqueo seguro: si hay 5 o más intentos fallidos
    if ($fallos >= 5) {
      cache()->put("bloqueado_{$correo}", true, now()->addMinutes(10));

      // Guardar log de bloqueo, solo una vez
      if (!SeguridadLog::where('correo_intentado', $correo)
        ->where('exitoso', false)
        ->where('mensaje', 'like', '%bloqueado%')
        ->exists()) {
        SeguridadLog::create([
          'usuario_id' => $usuario?->id,
          'correo_intentado' => $correo,
          'exitoso' => false,
          'ip' => $request->ip(),
          'dispositivo' => substr($request->header('User-Agent'), 0, 200),
          'ubicacion' => $this->obtenerUbicacion($request->ip()),
          'fecha_intento' => now(),
          'mensaje' => 'Cuenta bloqueada temporalmente por exceso de intentos fallidos',
        ]);
      }
    }
  }


  //Obtener ubicación desde IP (ip-api)
  protected function obtenerUbicacion($ip)
  {
    try {
      $response = Http::get("http://ip-api.com/json/{$ip}?fields=city,country,status")->json();
      return ($response['status'] === 'success')
        ? "{$response['city']}, {$response['country']}"
        : 'Desconocida';
    } catch (\Exception $e) {
      return 'Desconocida';
    }
  }

  // Control de RateLimiter

  private function checkRateLimit(Request $request)
  {
    $key = 'login:' . $request->ip() . ':' . strtolower($request->correo);

    if (RateLimiter::tooManyAttempts($key, 4)) {
      $seconds = RateLimiter::availableIn($key);
      throw ValidationException::withMessages([
        'correo' => ["Demasiados intentos. Intenta de nuevo en unos minutos."],
      ])->status(429);
    }
  }



  private function incrementRateLimit(Request $request)
  {
    $key = 'login:' . $request->ip() . ':' . strtolower($request->correo);
    RateLimiter::hit($key, 300);
  }


  private function clearRateLimit(Request $request)
  {
    $key = 'login:' . $request->ip() . ':' . strtolower($request->correo);
    RateLimiter::clear($key);
  }


  // ---------------------------------------------------------
  // 🚪 Logout
  // ---------------------------------------------------------
  public function logoutUsuario(Request $request)
  {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
  }
}
