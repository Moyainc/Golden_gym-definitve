<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
  public function list()
  {
    $productos = Producto::with('categoria')->get();
    return response()->json($productos);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'nombre' => 'required|string|max:255',
      'categoria_id' => 'required|exists:categorias,id',
      'stock' => 'required|integer|min:0',
      'precio' => 'required|numeric|min:0',
      'estado' => 'required|in:Activo,Inactivo',
    ]);

    $producto = Producto::create($validated);

    return response()->json(['message' => 'Producto creado exitosamente', 'producto' => $producto], 201);
  }

  public function update(Request $request, Producto $id)
  {
    $validated = $request->validate([
      'nombre' => 'required|string|max:255',
      'categoria_id' => 'required|exists:categorias,id',
      'stock' => 'required|integer|min:0',
      'precio' => 'required|numeric|min:0',
      'estado' => 'required|in:Activo,Inactivo',
    ]);

    $id->update($validated);

    return response()->json(['message' => 'Producto actualizado exitosamente', 'producto' => $id]);
  }

  public function destroy(Producto $id)
  {
    $id->delete();
    return response()->json(['message' => 'Producto eliminado exitosamente']);
  }
}
