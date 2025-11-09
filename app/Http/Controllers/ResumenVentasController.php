<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use Carbon\Carbon;

class ResumenVentasController extends Controller
{
  public function data(Request $request)
  {
    // ✅ Validar fechas correctamente
    $request->validate([
      'fecha_inicio' => 'nullable|date',
      'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
    ]);

    // ✅ Query base
    $query = Venta::query();

    if ($request->filled('fecha_inicio')) {
      $query->whereDate('fecha_venta', '>=', $request->fecha_inicio);
    }
    if ($request->filled('fecha_fin')) {
      $query->whereDate('fecha_venta', '<=', $request->fecha_fin);
    }

    $ventas = $query->with(['detalles.producto.categoria'])->get();

    // ✅ Métricas principales
    $totalVentas = (float) $ventas->sum('total');
    $numVentas = $ventas->count();
    $numProductos = (int) $ventas->sum(fn($v) => $v->detalles->sum('cantidad'));
    $pendientes = $ventas->where('estado', 'Pendiente')->count();

    // ✅ Ventas por Categoría (solo numéricos)
    $ventasPorCategoria = [];
    foreach ($ventas as $venta) {
      foreach ($venta->detalles as $detalle) {
        $categoria = $detalle->producto->categoria->nombre ?? 'Sin Categoría';
        $subtotal = (float) $detalle->subtotal;
        $ventasPorCategoria[$categoria] = ($ventasPorCategoria[$categoria] ?? 0) + $subtotal;
      }
    }

    // ✅ Ventas por Producto (solo numéricos)
    $ventasPorProducto = [];
    foreach ($ventas as $venta) {
      foreach ($venta->detalles as $detalle) {
        $producto = $detalle->producto->nombre ?? 'Producto Eliminado';
        $subtotal = (float) $detalle->subtotal;
        $ventasPorProducto[$producto] = ($ventasPorProducto[$producto] ?? 0) + $subtotal;
      }
    }

    // ✅ Ventas por Mes (formateado y numérico)
    // Total ventas por mes (formato ordenado)
    $ventasPorMes = $ventas->groupBy(function ($v) {
      return Carbon::parse($v->fecha_venta)->format('Y-m');
    })->map(fn($v) => (float) $v->sum('total'));

    $ventasPorMes = collect($ventasPorMes)->sortKeys();


    return response()->json([
      'success' => true,
      'totalVentas' => $totalVentas,
      'numVentas' => $numVentas,
      'numProductos' => $numProductos,
      'pendientes' => $pendientes,
      'ventasPorCategoria' => $ventasPorCategoria,
      'ventasPorProducto' => $ventasPorProducto,
      'ventasPorMes' => $ventasPorMes,
    ]);
  }

  // ✅ Últimas 5 ventas (valores numéricos)
  public function ultimasVentas()
  {
    $ventas = Venta::with(['detalles.producto.categoria'])
      ->orderByDesc('fecha_venta')
      ->take(5)
      ->get()
      ->map(function ($venta) {
        return [
          'id' => $venta->id,
          'usuario_id' => $venta->usuario_id,
          'cliente_id' => $venta->cliente_id,
          'total' => (float) $venta->total,
          'metodo_pago' => $venta->metodo_pago,
          'estado' => $venta->estado,
          'fecha_venta' => $venta->fecha_venta
            ? Carbon::parse($venta->fecha_venta)->format('Y-m-d')
            : null,
          'detalles' => $venta->detalles->map(fn($d) => [
            'producto' => $d->producto->nombre ?? 'Producto Eliminado',
            'categoria' => $d->producto->categoria->nombre ?? 'Sin Categoría',
            'cantidad' => (int) $d->cantidad,
            'subtotal' => (float) $d->subtotal,
            'precio_unitario' => (float) $d->precio_unitario,
          ]),
        ];
      });

    return response()->json([
      'success' => true,
      'ventas' => $ventas,
    ]);
  }
}
