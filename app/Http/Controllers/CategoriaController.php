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
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $categoria = Categoria::create($validated);

        return response()->json([
            'message' => 'Categoría creada exitosamente.',
            'categoria' => $categoria,
        ], 201);
    }

    // Mostrar una categoría específica (opcional)
    public function show(Categoria $categoria)
    {
        return response()->json($categoria);
    }

    // Actualizar una categoría
    public function updateCategoria(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string|max:500',
        ]);

        $categoria->update($validated);

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'categoria' => $categoria,
        ]);
    }

    // Eliminar una categoría
    public function destroyCategoria(Categoria $categoria)
    {
        $categoria->delete();

        return response()->json([
            'message' => 'Categoría eliminada exitosamente.',
        ]);
    }
}
