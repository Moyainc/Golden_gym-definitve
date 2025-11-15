<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\PagoGeneral;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Mail\Ventas;
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
    $request->validate(
      [
        'cliente_id' => 'required|exists:clientes,id',
        'usuario_id' => 'required|exists:usuarios,id',
        'productos' => 'required|array|min:1',
        'productos.*.producto_id' => 'required|exists:productos,id',
        'productos.*.cantidad' => 'required|integer|min:1',
        'productos.*.subtotal' => 'required|numeric|min:0',
        'total' => 'required|numeric|min:0',
        'metodo_pago' => 'required|string|in:Efectivo,Transferencia',
      ],
      [
        'productos.required' => 'Debe agregar al menos un producto.',
        'productos.*.producto_id.exists' => 'El producto seleccionado no existe.',
        'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
        'estado.in' => 'El estado no valido.',
        'metodo_pago.in' => 'El método de pago debe ser Efectivo o Transferencia.',

      ]
    );

    DB::beginTransaction();

    try {
      $totalVenta = 0;

      //Determinar el estado según el método de pago
      $estado = $request->metodo_pago === 'Efectivo' ? 'Completada' : 'Pendiente';

      // Validar que el estado esté dentro de los valores esperados
      $estadosPermitidos = ['Completada', 'Pendiente', 'Anulada'];
      if (!in_array($estado, $estadosPermitidos)) {
        return response()->json([
          'success' => false,
          'alert' => [
            'icon' => 'error',
            'title' => 'Error',
            'text' => "El estado '{$estado}' no es válido.",
            'background' => '#1a1a1a',
            'color' => '#fff',
            'confirmButtonColor' => '#fcd52b',
            'confirmButtonText' => 'Aceptar',
            'customClass' => [
              'confirmButton' => 'text-black font-bold',
            ],
          ]
        ], 422);
      }

      //  Crear venta principal
      $venta = Venta::create([
        'usuario_id' => $request->usuario_id,
        'cliente_id' => $request->cliente_id,
        'total' => 0, // Se actualizará luego
        'metodo_pago' => $request->metodo_pago,
        'estado' => $estado,
        'descripcion' => 'Venta detallada de productos',
        'fecha_venta' => now(),
      ]);


      // Registrar detalles y actualizar stock
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
        'origen_type' => Venta::class,
        'origen_id' => $venta->id,
        'monto' => $totalVenta,
        'metodo_pago' => $request->metodo_pago,
        'usuario_id'  => $request->usuario_id,
        'fecha_pago'  => now(),
        'estado'      => $venta->estado, // ← Se envía el estado directamente
      ]);

      // ✅ Enviar correo si la venta es mayor a 100.000 COP
      if ($venta->total > 100000) {
        Mail::to($venta->cliente->correo)
          ->send(new \App\Mail\Ventas($venta));
      }

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
        'alert' => [
            'icon' => 'error',
            'title' => 'Error',
            'text' => 'Ocurrió un error al registrar la venta. Intente de nuevo.',
            'background' => '#1a1a1a',
            'color' => '#fff',
            'confirmButtonColor' => '#fcd52b',
            'confirmButtonText' => 'Aceptar',
            'customClass' => [
                'confirmButton' => 'text-black font-bold',
            ],
        ]
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
      'estado' => 'nullable|string|in:Completada,Pendiente,Anulada',
    ], [
      'productos.required' => 'Debe agregar al menos un producto.',
      'productos.*.producto_id.exists' => 'El producto seleccionado no existe.',
      'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
      'estado.in' => 'El estado no valido.',
      'metodo_pago.in' => 'El método de pago debe ser Efectivo o Transferencia.',

    ]);

    try {
      DB::beginTransaction();

      $total = 0;

      // Calcular el total validando los productos
      foreach ($request->productos as $item) {
        $producto = Producto::findOrFail($item['producto_id']);

        if ($producto->estado !== 'Activo') {
          throw new \Exception("El producto '{$producto->nombre}' está 'Inactivo' y no puede venderse.");
        }

        if ($producto->stock < $item['cantidad']) {
          throw new \Exception("El producto '{$producto->nombre}' no tiene suficiente stock disponible.");
        }

        $subtotal = $producto->precio * $item['cantidad'];
        $total += $subtotal;
      }

      //Determinar estado automáticamente según método de pago
      $estado = match (strtolower($request->metodo_pago)) {
        'efectivo' => 'Completada',
        'transferencia' => 'Pendiente',
        default => $request->estado ?? 'Pendiente',
      };

      // Registrar la venta principal
      $venta = Venta::create([
        'usuario_id' => $request->usuario_id,
        'cliente_id' => null,
        'total' => $total,
        'estado' => $estado,
        'metodo_pago' => $request->metodo_pago,
        'fecha_venta' => now(),
      ]);

      //Registrar detalle y actualizar stock
      foreach ($request->productos as $item) {
        $producto = Producto::findOrFail($item['producto_id']);
        $cantidad = $item['cantidad'];
        $subtotal = $producto->precio * $cantidad;

        if ($producto->stock < $cantidad) {
          throw new \Exception("Stock insuficiente para el producto '{$producto->nombre}'.");
        }

        DetalleVenta::create([
          'venta_id' => $venta->id,
          'producto_id' => $producto->id,
          'cantidad' => $cantidad,
          'precio_unitario' => $producto->precio,
          'subtotal' => $subtotal,
        ]);

        $producto->decrement('stock', $cantidad);
      }

      //Registrar pago general asociado
      PagoGeneral::create([
        'tipo_pago'   => 'venta_producto',
        'origen_id'   => $venta->id,
        'origen_type' => Venta::class,
        'monto'       => $total,
        'metodo_pago' => $request->metodo_pago,
        'usuario_id'  => $request->usuario_id,
        'fecha_pago'  => now(),
        'estado'      => $venta->estado,
      ]);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Venta rápida registrada correctamente.',
        'venta_id' => $venta->id,
        'estado' => $venta->estado,
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'success' => false,
        'errors' => $e->errors(),
      ], 422);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error al registrar venta rápida: ' . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Ocurrió un error al registrar la venta rápida. Intente nuevamente.',
      ], 500);
    }
  }


  // Obtener las últimas 5 ventas

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
    $ventas = $query->take(7)->get();

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
    $estado = $request->query('estado'); // Nuevo: permite filtrar por estado
    $perPage = 15;

    // Base query
    $query = Venta::with(['detalles.producto.categoria', 'usuario'])
      ->orderBy('fecha_venta', 'desc');

    // Filtrar por estado (si se pasa un valor)
    if ($estado && strtolower($estado) !== 'todas') {
      $query->where('estado', ucfirst(strtolower($estado)));
    }

    // Filtrar por rango de fechas
    if ($fechaDesde) {
      $query->whereDate('fecha_venta', '>=', $fechaDesde);
    }
    if ($fechaHasta) {
      $query->whereDate('fecha_venta', '<=', $fechaHasta);
    }

    // Paginación
    $ventas = $query->paginate($perPage)->appends($request->query());

    // Mapear ventas y filtrar detalles por categoría si aplica
    $ventasFormateadas = $ventas->map(function ($venta) use ($categoriaId) {
      $detalles = $venta->detalles->map(function ($det) {
        return [
          'producto' => $det->producto->nombre ?? 'Sin nombre',
          'categoria' => $det->producto->categoria->nombre ?? 'N/A',
          'categoria_id' => $det->producto->categoria->id ?? null,
          'cantidad' => $det->cantidad,
          'precio_unitario' => $det->precio_unitario,
          'precio_unitario_formatted' => '$' . number_format($det->precio_unitario, 0, ',', '.'),
          'subtotal' => $det->subtotal,
          'subtotal_formatted' => '$' . number_format($det->subtotal, 0, ',', '.'),
        ];
      });

      // Filtrar detalles por categoría si se seleccionó una
      if ($categoriaId) {
        $detalles = $detalles->filter(fn($det) => $det['categoria_id'] == $categoriaId)->values();
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
      ->filter(fn($v) => $v['detalles']->count() > 0)
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



  // public function actualizarEstado(Request $request, $id)
  // {
  //   $request->validate([
  //     'estado' => 'required|string|in:Completada,Pendiente,Anulada'
  //   ]);

  //   $venta = Venta::findOrFail($id);
  //   $venta->estado = $request->estado;
  //   $venta->save();

  //   // Actualizar el pago relacionado
  //   $pago = PagoGeneral::where('origen_id', $venta->id)
  //     ->where('origen_type', Venta::class)
  //     ->first();

  //   if ($pago) {
  //     $pago->estado = $request->estado;
  //     $pago->save();
  //   }

  //   return response()->json([
  //     'success' => true,
  //     'venta_id' => $venta->id,
  //     'estado' => $venta->estado,
  //     'message' => 'Estado actualizado correctamente'
  //   ]);
  // }



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


  public function anularVenta(Request $request, Venta $venta)
  {
    $request->validate([
      'estado' => 'required|in:Anulada',
    ]);

    if ($venta->estado === 'Anulada') {
      return response()->json(['message' => 'La venta ya está anulada'], 400);
    }

    // Cambiar estado de la venta
    $venta->estado = 'Anulada';
    $venta->save();

    // Actualizar el registro asociado en PagosGenerales
    PagoGeneral::where('origen_type', Venta::class)
      ->where('origen_id', $venta->id)
      ->update(['estado' => 'Anulada']);

    return response()->json(['message' => 'Venta anulada correctamente']);
  }
}
