<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Models\Membresia;

class HomeController extends Controller
{
    public function index()
    {
        return view('users/index');
    }

    public function clientes()
    {
        return view('admin/clientes');
    }
    public function confirmacion_pagos()
    {
        return view('admin/confirmacion-pagos');
    }
    public function soporte()
    {
        return view('admin/soporte');
    }
    public function resumen_general()
    {
        return view('admin/contabilidad/resumen-general');
    }
    public function resumen_ventas()
    {
        return view('admin/contabilidad/resumen-ventas');
    }
    public function dashboard()
    {
        return view('admin/dashboard');
    }
    public function membresias()
    {
        $membresias = Membresia::orderByDesc('id')->get();
        return view('admin/membresia', compact('membresias'));
    }
    public function productos()
    {
        return view('admin/productos');
    }
    public function profile()
    {
        return view('admin/profile');
    }
    public function login()
    {
        return view('users/sign-in');
    }
    public function register()
    {
        return view('users/sign-up');
    }
    public function usuarios()
    {
        // Obtener todos los usuarios más recientes primero
        $usuarios = \App\Models\Usuario::select('id', 'nombre_completo', 'correo', 'rol')
            ->orderBy('id', 'desc')
            ->get();

        // Total general de usuarios
        $totalUsuarios = $usuarios->count();

        return view('admin.usuarios', compact('usuarios', 'totalUsuarios'));
    }

    public function vender()
    {
        return view('admin/ventas/vender');
    }
    public function lista_ventas()
    {
        return view('admin/ventas/lista-ventas');
    }
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre_completo' => 'required|string|max:150',
                'correo' => 'required|email|unique:usuarios,correo',
                'cedula' => 'required|string|max:20|unique:usuarios,cedula',
                'rol' => 'required|in:Administrador,Contador,Recepcionista',
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->mixedCase()
                        ->letters()
                        ->numbers()
                        ->symbols()
                ],
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('usuarios/fotos', 'public');
            }

            $usuario = Usuario::create([
                'nombre_completo' => $request->nombre_completo,
                'correo' => $request->correo,
                'cedula' => $request->cedula,
                'telefono' => $request->telefono,
                'rol' => $request->rol,
                'password' => Hash::make($request->password),
                'foto' => $fotoPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado correctamente.',
                'usuario' => $usuario,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
            ], 500);
        }
    }

    public function editUsuario($id)
    {
        $usuario = \App\Models\Usuario::findOrFail($id);

        // Devolver JSON con campos específicos y ruta completa de la imagen
        return response()->json([
            'id' => $usuario->id,
            'nombre_completo' => $usuario->nombre_completo,
            'correo' => $usuario->correo,
            'rol' => $usuario->rol,
            'telefono' => $usuario->telefono,
            'cedula' => $usuario->cedula,
            'foto' => $usuario->foto
                ? asset('storage/' . $usuario->foto)
                : asset('storage/usuarios/default.png'),
        ]);
    }
    public function updateUsuario(Request $request, $id)
    {
        $usuario = \App\Models\Usuario::findOrFail($id);

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'correo' => 'required|email|unique:usuarios,correo,' . $id,
            'rol' => 'required|string',
            'telefono' => 'nullable|string|max:10',
            'cedula' => 'required|string|max:20|unique:usuarios,cedula,' . $id,
            'foto' => 'nullable|image|max:2048',
        ]);

        $usuario->nombre_completo = $request->nombre_completo;
        $usuario->correo = $request->correo;
        $usuario->rol = $request->rol;
        $usuario->telefono = $request->telefono;
        $usuario->cedula = $request->cedula;

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('usuarios/fotos', 'public');
            $usuario->foto = $path;
        }

        if ($request->filled('password')) {
            $usuario->password = bcrypt($request->password);
        }

        $usuario->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
        ]);
    }

    public function destroyUsuario($id)
    {
        try {
            $user = Usuario::findOrFail($id);

            // Si tiene foto distinta a la default, eliminar archivo
            if ($user->foto && $user->foto !== 'usuarios/default.png') {
                Storage::delete('public/' . $user->foto);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'El usuario ha sido eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el usuario.'
            ], 500);
        }
    }

    public function registerUsuario(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre_completo' => 'required|string|max:150',
                'correo' => 'required|email|unique:usuarios,correo',
                'cedula' => 'nullable|string|max:20|unique:usuarios,cedula',
                'telefono' => 'required|string|max:15',
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->mixedCase()
                        ->letters()
                        ->numbers()
                        ->symbols()
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $usuario = Usuario::create([
                'nombre_completo' => $request->nombre_completo,
                'correo' => $request->correo,
                'cedula' => $request->cedula,
                'telefono' => $request->telefono,
                'rol' => 'Usuario', // siempre Usuario
                'password' => Hash::make($request->password),
                'foto' => 'usuarios/default.png',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cuenta creada correctamente.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function loginUsuario(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email|exists:usuarios,correo',
            'password' => 'required|string',
        ], [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe ser válido.',
            'correo.exists' => 'No se encontró este correo en la base de datos.',
            'password.required' => 'La contraseña es obligatoria.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = [
            'correo' => $request->correo,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'redirect' => route('dashboard') // Ajusta a tu ruta interna
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Correo o contraseña incorrectos.'
        ], 401);
    }
}
