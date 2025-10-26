<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // Listar todas las categorías (puedes usarlo en select o tabla)
    public function getCategorias()
    {
        return response()->json(Categoria::orderBy('id', 'desc')->get());
    }

    // Guardar nueva categoría
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:categorias,nombre',
                'descripcion' => 'nullable|string|max:500',
            ], [
                'nombre.required' => 'El nombre de la categoría es obligatorio.',
                'nombre.unique' => 'La categoría ya existe.',
            ]);

            $categoria = Categoria::create($validated);

            return response()->json([
                'message' => 'Categoría creada exitosamente.',
                'categoria' => $categoria,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 🔥 Laravel atrapará aquí si ya existe la categoría
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Por si ocurre otro error inesperado
            return response()->json([
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }


    // Mostrar una categoría específica (opcional)
    public function show(Categoria $id)
    {
        return response()->json($id);
    }

    // Actualizar una categoría
    public function update(Request $request, Categoria $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $id->id,
            'descripcion' => 'nullable|string|max:500',
        ]);

        $id->update($validated);

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'categoria' => $id,
        ]);
    }

    // Eliminar una categoría
    public function destroy(Categoria $id)
    {
        $id->delete();

        return response()->json([
            'message' => 'Categoría eliminada exitosamente.',
        ]);
    }
}
