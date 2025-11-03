<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use App\Models\Usuario;

class PerfilController extends Controller
{
  // Mostrar perfil (opcional, si quieres cargar los datos desde backend)
  public function showProfile()
  {
    $user = Usuario::findOrFail(Auth::id());

    return response()->json([
      'success' => true,
      'user' => [
        'nombre_completo' => $user->nombre_completo,
        'correo' => $user->correo,
        'telefono' => $user->telefono,
        'cedula' => $user->cedula,
        'rol' => ucfirst($user->rol),
        'ultimo_acceso' => $user->ultimo_acceso_formateado,
        'foto_url' => $user->foto ? asset('storage/' . $user->foto) : asset('img/avatars/avatar-4.jpg')
      ]
    ]);
  }

  // Actualizar datos del usuario logeado
  public function updateProfile(Request $request)
  {
    $user = Usuario::findOrFail(Auth::id());

    $validated = $request->validate([
      'nombre_completo' => 'required|string|max:255',
      'correo' => 'required|email|unique:usuarios,correo,' . $user->id,
      'telefono' => 'required|string|max:20',
      'cedula' => 'nullable|string|max:50|unique:usuarios,cedula,' . $user->id,
      'foto' => 'nullable|image|max:2048', // 2MB
    ]);

    $user->nombre_completo = $validated['nombre_completo'];
    $user->correo = $validated['correo'];
    $user->telefono = $validated['telefono'];
    $user->cedula = $validated['cedula'] ?? null;

    // Subir foto si hay
    if ($request->hasFile('foto')) {
      if ($user->foto) {
        Storage::disk('public')->delete($user->foto);
      }
      $path = $request->file('foto')->store('usuarios/fotos', 'public');
      $user->foto = $path;
    }

    $user->save();

    return response()->json([
      'success' => true,
      'message' => 'Perfil actualizado correctamente',
      'user' => [
        'nombre_completo' => $user->nombre_completo,
        'correo' => $user->correo,
        'telefono' => $user->telefono,
        'cedula' => $user->cedula,
        'rol' => $user->rol,
        'ultimo_acceso' => $user->ultimo_acceso,
        'foto_url' => $user->foto ? asset("storage/{$user->foto}") : asset("img/avatars/avatar-4.jpg"),
      ],
    ]);
  }

  // Cambiar contraseña
  public function changePassword(Request $request)
  {
    $user = Usuario::findOrFail(Auth::id());

    $validated = $request->validate([
      'current_password' => 'required',
      'new_password' => [
        'required',
        'confirmed', // requiere new_password_confirmation
        Password::min(8)
          ->letters()
          ->mixedCase()
          ->numbers()
          ->symbols()
      ],
    ]);

    if (!Hash::check($validated['current_password'], $user->password)) {
      return response()->json([
        'success' => false,
        'message' => 'La contraseña actual es incorrecta.'
      ], 422);
    }

    $user->password = Hash::make($validated['new_password']);
    $user->save();

    return response()->json([
      'success' => true,
      'message' => 'Contraseña actualizada correctamente'
    ]);
  }

  public function deletePhoto(Request $request)
  {
    $user = Usuario::findOrFail(Auth::id());

    if ($user->foto) {
      Storage::disk('public')->delete($user->foto);
      $user->foto = null;
      $user->save();
    }

    return response()->json([
      'success' => true,
      'message' => 'Foto de perfil eliminada'
    ]);
  }
}
