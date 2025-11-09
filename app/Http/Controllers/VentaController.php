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
use Illuminate\Support\Facades\Auth;
use App\Traits\HasPrecioFormatted;

class VentaController extends Controller
{
  use HasPrecioFormatted;


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
      $totalVenta = 0;

      // ✅ Crear venta principal
      $venta = Venta::create([
        'usuario_id' => $request->usuario_id,
        'cliente_id' => $request->cliente_id,
        'total' => 0, // Se actualizará luego
        'metodo_pago' => $request->metodo_pago,
        'estado' => 'Completada',
        'descripcion' => 'Venta detallada de productos',
        'fecha_venta' => now(),
      ]);

      // ✅ Registrar detalles y actualizar stock
      foreach ($request->productos as $producto) {
        $productoDB = Producto::findOrFail($producto['producto_id']);

        // Validar estado y stock
        if ($productoDB->estado !== 'Activo') {
          throw new \Exception("El producto '{$productoDB->nombre}' está inactivo.");
        }

        if ($productoDB->stock < $producto['cantidad']) {
          throw new \Exception("Stock insuficiente para '{$productoDB->nombre}'. Stock actual: {$productoDB->stock}");
        }

        $subtotal = $productoDB->precio * $producto['cantidad'];
        $totalVenta += $subtotal;

        // Crear detalle
        DetalleVenta::create([
          'venta_id' => $venta->id,
          'producto_id' => $productoDB->id,
          'cantidad' => $producto['cantidad'],
          'precio_unitario' => $productoDB->precio,
          'subtotal' => $subtotal,
        ]);

        // Descontar stock
        $productoDB->decrement('stock', $producto['cantidad']);
      }

      // ✅ Actualizar total de la venta
      $venta->update(['total' => $totalVenta]);

      // ✅ Registrar pago
      PagoGeneral::create([
        'tipo_pago' => 'venta_producto',
        'origen_id' => $venta->id,
        'monto' => $totalVenta,
        'metodo_pago' => $request->metodo_pago,
        'usuario_id' => $request->usuario_id,
        'fecha_pago' => now(),
      ]);

      DB::commit();

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Venta registrada correctamente',
        'venta_id' => $venta->id,
        'total' => $venta->total,
        'total_formatted' => $this->precioFormatted($venta->total),
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

        // ✅ Validar si el producto está activo
        if ($producto->estado !== 'Activo') {
          throw new \Exception("El producto '{$producto->nombre}' está en estado 'Inactivo' y no puede venderse.");
        }

        // ✅ Validar stock
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
        'message' => 'Error al registrar la venta rápida: ' . $e->getMessage(),
      ], 500);
    }
  }

  // Obtener las últimas 5 ventas (AJAX) + Verificar rol

  public function ultimasVentas()
  {
    $usuario = Auth::user();

    if (!$usuario) {
      return response()->json([
        'success' => false,
        'message' => 'Usuario no autenticado.'
      ], 401);
    }

    // Construcción de la consulta
    $query = DB::table('detalle_ventas')
      ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
      ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
      ->leftJoin('categorias', 'productos.categoria_id', '=', 'categorias.id')
      ->select(
        'productos.nombre as producto_nombre',
        'categorias.nombre as categoria_nombre',
        'ventas.metodo_pago',
        'productos.precio as producto_precio',
        'detalle_ventas.cantidad',
        'detalle_ventas.subtotal',
        'ventas.estado',
        'ventas.usuario_id',
        'ventas.created_at'
      )
      ->orderBy('ventas.created_at', 'desc');

    // Si no es admin, filtrar solo las ventas del usuario logueado
    if ($usuario->rol !== 'Administrador') {
      $query->where('ventas.usuario_id', $usuario->id);
    }

    // Solo las 5 más recientes
    $ventas = $query->take(5)->get();

    return response()->json([
      'success' => true,
      'ventas' => $ventas,
      'isAdmin' => $usuario->rol === 'Administrador',
    ]);
  }

  public function listVentas(Request $request)
  {
    $categoriaId = $request->query('categoria');
    $fechaDesde = $request->query('fecha_desde');
    $fechaHasta = $request->query('fecha_hasta');
    $perPage = 10;

    $query = Venta::with(['detalles.producto.categoria', 'usuario'])
      ->orderBy('fecha_venta', 'desc');

    // Filtrar por rango de fechas
    if ($fechaDesde) $query->whereDate('fecha_venta', '>=', $fechaDesde);
    if ($fechaHasta) $query->whereDate('fecha_venta', '<=', $fechaHasta);

    $ventas = $query->paginate($perPage)->appends($request->query());

    // 🔹 Mapear ventas y filtrar detalles por categoría si aplica
    $ventasFormateadas = $ventas->map(function ($venta) use ($categoriaId) {
      $detalles = $venta->detalles->map(function ($det) {
        return [
          'producto' => $det->producto->nombre ?? 'Sin nombre',
          'categoria' => $det->producto->categoria->nombre ?? 'N/A',
          'categoria_id' => $det->producto->categoria->id ?? null, // 🔹 agregamos ID
          'cantidad' => $det->cantidad,
          'precio_unitario' => $det->precio_unitario,
          'precio_unitario_formatted' => '$' . number_format($det->precio_unitario, 0, ',', '.'),
          'subtotal' => $det->subtotal,
          'subtotal_formatted' => '$' . number_format($det->subtotal, 0, ',', '.'),
        ];
      });

      // 🔹 Filtrar detalles por categoría si se seleccionó una
      if ($categoriaId) {
        $detalles = $detalles->filter(function ($det) use ($categoriaId) {
          return $det['categoria_id'] == $categoriaId;
        })->values(); // reset keys
      }

      return [
        'id' => $venta->id,
        'fecha_venta' => $venta->fecha_venta,
        'metodo_pago' => $venta->metodo_pago,
        'estado' => $venta->estado,
        'usuario' => $venta->usuario->nombre_completo ?? 'Desconocido',
        'total' => $venta->total,
        'total_formatted' => '$' . number_format($venta->total, 0, ',', '.'),
        'detalles' => $detalles,
      ];
    })
      ->filter(fn($v) => $v['detalles']->count() > 0) // solo ventas con detalles
      ->values();

    return response()->json([
      'ventas' => $ventasFormateadas,
      'pagination' => [
        'current_page' => $ventas->currentPage(),
        'last_page' => $ventas->lastPage(),
        'total' => $ventas->total(),
      ],
    ]);
  }




  public function showVentaDetalle($id)
  {
    $venta = Venta::with(['detalles.producto.categoria', 'usuario'])->find($id);

    if (!$venta) {
      return response()->json(['error' => 'Venta no encontrada'], 404);
    }

    $detalles = $venta->detalles->map(function ($det) {
      $productoNombre = optional($det->producto)->nombre ?? 'Sin nombre';
      $categoriaNombre = optional($det->producto->categoria)->nombre ?? 'N/A';

      return [
        'producto' => $productoNombre,
        'categoria' => $categoriaNombre,
        'cantidad' => $det->cantidad,
        'precio_unitario' => $det->precio_unitario,
        'precio_unitario_formatted' => $this->precioFormatted($det->precio_unitario),
        'subtotal' => $det->subtotal,
        'subtotal_formatted' => $this->precioFormatted($det->subtotal),
      ];
    });

    return response()->json([
      'id' => $venta->id,
      'fecha_venta' => $venta->fecha_venta,
      'metodo_pago' => $venta->metodo_pago,
      'estado' => $venta->estado,
      'usuario' => optional($venta->usuario)->nombre_completo ?? 'Desconocido',
      'total' => $venta->total,
      'total_formatted' => $this->precioFormatted($venta->total),
      'detalles' => $detalles
    ]);
  }
}
