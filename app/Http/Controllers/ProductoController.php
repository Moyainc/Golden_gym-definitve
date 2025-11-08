<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Traits\HasPrecioFormatted;

class ProductoController extends Controller
{
  use HasPrecioFormatted;

  // Listar todos los productos
  public function list()
  {
    $productos = Producto::with('categoria')->get()->map(function ($producto) {
      return [
        'id' => $producto->id,
        'nombre' => $producto->nombre,
        'categoria' => $producto->categoria,
        'categoria_id' => $producto->categoria_id,
        'stock' => $producto->stock,
        'precio' => $producto->precio,
        'precio_formatted' => $producto->precioFormatted($producto->precio),
        'estado' => $producto->estado,
      ];
    });

    return response()->json($productos);
  }


  // Crear un nuevo producto
  public function store(Request $request)
  {
    try {
      $validated = $request->validate([
        'nombre' => 'required|string|max:255|unique:productos,nombre,' . ($id->id ?? 'NULL'),
        'categoria_id' => 'required|exists:categorias,id',
        'stock' => 'required|integer|min:0',
        'precio' => 'required|numeric|min:0',
        'estado' => 'required|in:Activo,Inactivo',
      ], [
        'nombre.unique' => "El producto '{$request->nombre}' ya está registrado.",
      ]);


      $producto = Producto::create($validated);

      return response()->json([
        'message' => 'Producto creado exitosamente',
        'producto' => $producto
      ], 201);
    } catch (ValidationException $e) {
      // Retornar los errores de validación en formato que JS pueda usar
      return response()->json([
        'message' => implode(' ', $e->errors()['nombre'] ?? ['Datos inválidos']),
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Error al crear el producto. Intente nuevamente.'
      ], 500);
    }
  }

  // Actualizar un producto existente
  public function update(Request $request, Producto $id)
  {
    try {
      $validated = $request->validate([
        'nombre' => 'required|string|max:255|unique:productos,nombre,' . $id->id,
        'categoria_id' => 'required|exists:categorias,id',
        'stock' => 'required|integer|min:0',
        'precio' => 'required|numeric|min:0',
        'estado' => 'required|in:Activo,Inactivo',
      ]);

      $id->update($validated);

      return response()->json([
        'message' => 'Producto actualizado exitosamente',
        'producto' => $id
      ]);
    } catch (ValidationException $e) {
      return response()->json([
        'message' => implode(' ', $e->errors()['nombre'] ?? ['Datos inválidos']),
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Error al actualizar el producto. Intente nuevamente.'
      ], 500);
    }
  }

  // Eliminar un producto
  public function destroy(Producto $id)
  {
    try {
      $id->delete();
      return response()->json([
        'message' => 'Producto eliminado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Error al eliminar el producto. Intente nuevamente.'
      ], 500);
    }
  }
}
