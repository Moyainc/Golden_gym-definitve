<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\PagoGeneral;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
  /**
   * Buscar cliente por identificación (AJAX)
   */
  public function buscarCliente(Request $request)
  {
    $identificacion = $request->query('identificacion');

    if (!$identificacion) {
      return response()->json([
        'success' => false,
        'message' => 'Identificación no recibida'
      ], 400);
    }

    $cliente = Cliente::select('id', 'nombre_completo', 'cedula')
      ->where('cedula', $identificacion)
      ->first();

    if (!$cliente) {
      return response()->json([
        'success' => false,
        'message' => 'Cliente no encontrado'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'id' => $cliente->id,
      'nombre' => $cliente->nombre_completo,
    ]);
  }

  /**
   * Sugerir clientes por identificación o nombre (para autocompletar)
   */
  public function sugerirClientes(Request $request)
  {
    $busqueda = $request->query('identificacion');

    if (!$busqueda || strlen($busqueda) < 3) {
      return response()->json([
        'success' => false,
        'clientes' => [],
      ]);
    }

    $clientes = Cliente::select('id', 'nombre_completo', 'cedula')
      ->where('cedula', 'like', "%{$busqueda}%")
      ->orWhere('nombre_completo', 'like', "%{$busqueda}%")
      ->limit(5)
      ->get();

    return response()->json([
      'success' => true,
      'clientes' => $clientes,
    ]);
  }

  /**
   * Registrar la venta y sus detalles
   */
  public function store(Request $request)
  {
    $request->validate([
      'cliente_id' => 'required|exists:clientes,id',
      'usuario_id' => 'required|exists:usuarios,id',
      'productos' => 'required|array|min:1',
      'productos.*.producto_id' => 'required|exists:productos,id',
      'productos.*.cantidad' => 'required|integer|min:1',
      'productos.*.subtotal' => 'required|numeric|min:0',
      'total' => 'required|numeric|min:0',
      'metodo_pago' => 'required|string|in:Efectivo,Transferencia',
    ]);

    DB::beginTransaction();

    try {
      // ✅ Validar stock antes de crear la venta
      foreach ($request->productos as $producto) {
        $productoDB = Producto::findOrFail($producto['producto_id']);

        if ($productoDB->stock < $producto['cantidad']) {
          return response()->json([
            'success' => false,
            'message' => "No hay suficiente stock para el producto: {$productoDB->nombre}. Stock actual: {$productoDB->stock}",
          ], 400);
        }
      }

      // ✅ Crear la venta principal
      $totalVenta = 0;

      $venta = Venta::create([
        'usuario_id' => $request->usuario_id,
        'cliente_id' => $request->cliente_id,
        'total' => 0, // se actualizará más adelante
        'metodo_pago' => $request->metodo_pago,
        'estado' => 'Completada',
        'descripcion' => 'Venta de productos',
      ]);

      // ✅ Registrar detalles y actualizar stock
      foreach ($request->productos as $producto) {
        $productoDB = Producto::findOrFail($producto['producto_id']);
        $subtotal = $producto['cantidad'] * $productoDB->precio;

        // Registrar detalle de venta
        DetalleVenta::create([
          'venta_id' => $venta->id,
          'producto_id' => $producto['producto_id'],
          'precio_unitario' => $productoDB->precio,
          'cantidad' => $producto['cantidad'],
          'subtotal' => $subtotal,
        ]);

        // ✅ Descontar stock
        $productoDB->decrement('stock', $producto['cantidad']);

        $totalVenta += $subtotal;
      }

      // ✅ Actualizar total real de la venta
      $venta->update(['total' => $totalVenta]);

      // ✅ Registrar el pago general (control contable)
      PagoGeneral::create([
        'tipo_pago' => 'venta_producto',
        'origen_id' => $venta->id,
        'monto' => $totalVenta,
        'metodo_pago' => $request->metodo_pago,
        'usuario_id' => $request->usuario_id,
      ]);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Venta registrada correctamente y stock actualizado',
        'venta_id' => $venta->id,
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error al registrar venta: ' . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error al registrar la venta: ' . $e->getMessage(),
      ], 500);
    }
  }

  public function storeFast(Request $request)
{
    $validated = $request->validate([
        'usuario_id' => 'required|integer|exists:usuarios,id',
        'productos' => 'required|array|min:1',
        'productos.*.producto_id' => 'required|exists:productos,id',
        'productos.*.cantidad' => 'required|integer|min:1',
        'metodo_pago' => 'required|string|in:Efectivo,Transferencia',
        'estado' => 'required|string|in:Completada,Cancelada',
    ], [
        'productos.required' => 'Debe agregar al menos un producto.',
        'productos.*.producto_id.exists' => 'El producto seleccionado no existe.',
        'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
    ]);

    try {
        DB::beginTransaction();

        $total = 0;

        // 🔍 Recalcular todo en el backend (seguro y confiable)
        foreach ($request->productos as $item) {
            $producto = Producto::findOrFail($item['producto_id']);

            if ($producto->stock < $item['cantidad']) {
                throw new \Exception("El producto '{$producto->nombre}' no tiene suficiente stock disponible.");
            }

            $subtotal = $producto->precio * $item['cantidad'];
            $total += $subtotal;
        }

        // 🧾 Registrar venta principal
        $venta = Venta::create([
            'usuario_id' => $request->usuario_id,
            'cliente_id' => null, // Venta rápida sin cliente
            'total' => $total,
            'estado' => $request->estado,
            'metodo_pago' => $request->metodo_pago,
            'fecha_venta' => now(),
        ]);

        // 💾 Registrar detalle y actualizar stock
        foreach ($request->productos as $item) {
            $producto = Producto::findOrFail($item['producto_id']);
            $cantidad = $item['cantidad'];
            $subtotal = $producto->precio * $cantidad;

            // Validación de stock antes de descontar
            if ($producto->stock < $cantidad) {
                throw new \Exception("Stock insuficiente para el producto '{$producto->nombre}'.");
            }

            // Guardar detalle
            DetalleVenta::create([
                'venta_id' => $venta->id,
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
                'precio_unitario' => $producto->precio,
                'subtotal' => $subtotal,
            ]);

            // Descontar del stock
            $producto->decrement('stock', $cantidad);
        }

        // 💰 Registrar pago
        PagoGeneral::create([
            'tipo_pago' => 'venta_producto',
            'origen_id' => $venta->id,
            'monto' => $total,
            'metodo_pago' => $request->metodo_pago,
            'usuario_id' => $request->usuario_id,
            'fecha_pago' => now(),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Venta rápida registrada correctamente.',
            'venta_id' => $venta->id,
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // ⚠️ Responder errores de validación (422)
        return response()->json([
            'success' => false,
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Error al registrar la venta rápida: '.$e->getMessage(),
        ], 500);
    }
}

}
