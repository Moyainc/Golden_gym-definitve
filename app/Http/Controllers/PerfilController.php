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
  // Mostrar perfil del usuario logeado
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

  // Actualizar perfil
  public function updateProfile(Request $request)
  {
    $user = Usuario::findOrFail(Auth::id());

    $validated = $request->validate([
      'nombre_completo' => 'required|string|max:255',
      'correo' => 'required|email|unique:usuarios,correo,' . $user->id,
      'telefono' => 'required|string|max:20',
      'cedula' => 'nullable|string|max:50|unique:usuarios,cedula,' . $user->id,
      'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Limitar tipo y tamaño
    ]);

    // Evitar sobrescribir campos que no cambian
    $hasChanges = false;
    foreach (['nombre_completo', 'correo', 'telefono', 'cedula'] as $field) {
      if ($user->$field !== ($validated[$field] ?? null)) {
        $user->$field = $validated[$field] ?? null;
        $hasChanges = true;
      }
    }

    // Subir foto si se envió
    if ($request->hasFile('foto')) {
      if ($user->foto) Storage::disk('public')->delete($user->foto);

      $path = $request->file('foto')->store('usuarios/fotos', 'public');
      $user->foto = $path;
      $hasChanges = true;
    }

    if (!$hasChanges) {
      return response()->json([
        'success' => false,
        'message' => 'No se detectaron cambios para actualizar.'
      ], 422);
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
        'ultimo_acceso' => $user->ultimo_acceso_formateado,
        'foto_url' => $user->foto ? asset("storage/{$user->foto}") : asset("img/avatars/avatar-4.jpg"),
      ],
    ]);
  }

  // Cambiar contraseña
  public function changePassword(Request $request)
  {
    $user = Usuario::findOrFail(Auth::id());

    $validated = $request->validate([
      'current_password' => 'required|string',
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

    if (Hash::check($validated['new_password'], $user->password)) {
      return response()->json([
        'success' => false,
        'message' => 'La nueva contraseña no puede ser igual a la anterior.'
      ], 422);
    }

    $user->password = Hash::make($validated['new_password']);
    $user->save();

    return response()->json([
      'success' => true,
      'message' => 'Contraseña actualizada correctamente.'
    ]);
  }

  // Eliminar foto
  public function deletePhoto(Request $request)
  {
    $user = Usuario::findOrFail(Auth::id());

    if (!$user->foto) {
      return response()->json([
        'success' => false,
        'message' => 'No hay foto para eliminar.'
      ], 422);
    }

    Storage::disk('public')->delete($user->foto);
    $user->foto = null;
    $user->save();

    return response()->json([
      'success' => true,
      'message' => 'Foto de perfil eliminada.'
    ]);
  }
}
