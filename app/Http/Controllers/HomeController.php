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

Class HomeController extends Controller
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
    public function contabilidad()
    {
        return view('admin/contabilidad');
    }
    public function dashboard()
    {
        return view('admin/dashboard');
    }
    public function membresias()
    {
        return view('admin/membresia');
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
        return view('admin/sign-in');
    }
    public function usuarios()
    {
        return view('admin/usuarios');
    }   
    public function resumen_ventas()
    {
        return view('admin/ventas/resumen-ventas');
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
        // 🔹 Validar los datos recibidos
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
        ], [
            // 🔹 Mensajes personalizados
            'nombre_completo.required' => 'El nombre completo es obligatorio. ',
            'correo.required' => 'El correo electrónico es obligatorio. ',
            'correo.email' => 'Debe ingresar un correo válido. ',
            'correo.unique' => 'El correo ya está registrado. ',
            'cedula.required' => 'La cédula es obligatoria. ',
            'cedula.unique' => 'La cédula ya está registrada. ',
            'rol.required' => 'Debe seleccionar un rol.',
            'password.required' => 'Debe ingresar una contraseña. ',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.mixed' => 'La contraseña debe incluir mayúsculas y minúsculas.',
            'password.letters' => 'La contraseña debe contener al menos una letra.',
            'password.numbers' => 'La contraseña debe incluir al menos un número.',
            'password.symbols' => 'La contraseña debe incluir al menos un carácter especial.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'foto.image' => 'El archivo debe ser una imagen (JPG, JPEG o PNG). ',
            'foto.max' => 'La imagen no debe superar los 2MB. ',
        ]);

        // 🔹 Si hay errores, responder con JSON si la petición es AJAX
        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // 🔹 Subir la foto si existe
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('usuarios/fotos', 'public');
        }

        // 🔹 Crear el usuario
        $usuario = Usuario::create([
            'nombre_completo' => $request->nombre_completo,
            'correo' => $request->correo,
            'cedula' => $request->cedula,
            'rol' => $request->rol,
            'password' => Hash::make($request->password),
            'foto' => $fotoPath,
        ]);

        // 🔹 Responder según el tipo de solicitud
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado correctamente.',
                'usuario' => $usuario,
            ], 201);
        }

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario registrado correctamente.');
    }

}


?>