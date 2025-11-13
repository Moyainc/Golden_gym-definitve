<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
    public function soporte()
    {
        return view('admin/soporte');
    }
    public function pageBlank()
    {
        return view('errors/page-blank');
    }
    public function resumen_general()
    {
        return view('admin/contabilidad/resumen-general');
    }
    public function resumen_ventas()
    {
        return view('admin/contabilidad/resumen-ventas');
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
        $usuarios = Usuario::select('id', 'nombre_completo', 'correo', 'rol')
            ->where('oculto', false) // Solo usuarios visibles
            ->orderBy('id', 'desc')
            ->get();

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

            if ($user->id === auth('web')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propio usuario.'
                ], 403);
            }

            // Opcional: prevenir eliminar superadmins ocultos
            if ($user->oculto) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar este usuario.'
                ], 403);
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
}
