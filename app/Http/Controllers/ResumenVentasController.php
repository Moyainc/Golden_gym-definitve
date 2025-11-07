<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use Carbon\Carbon;

class ResumenVentasController extends Controller
{
  // ✅ Formatear números sin decimales, solo para mostrar
  private function formatNumber($value)
  {
    return round($value); // Devuelve solo entero
  }

  public function index()
  {
    $anios = Venta::selectRaw('YEAR(fecha_venta) as anio')
      ->distinct()
      ->orderBy('anio', 'asc')
      ->pluck('anio');

    return view('admin.contabilidad.resumen-ventas', compact('anios'));
  }

  public function data(Request $request)
  {
    $request->validate([
      'fecha_inicio' => 'nullable|date',
      'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
    ]);

    $query = Venta::query();

    if ($request->filled('anio')) $query->whereYear('fecha_venta', $request->anio);
    if ($request->filled('mes')) $query->whereMonth('fecha_venta', $request->mes);

    $ventas = $query->with(['detalles.producto.categoria'])->get();

    // ✅ Métricas principales
    $totalVentas = round($ventas->sum('total'));
    $numVentas = $ventas->count();
    $numProductos = $ventas->sum(fn($v) => $v->detalles->sum('cantidad'));
    $pendientes = $ventas->where('estado', 'Pendiente')->count();

    // Ventas por Categoría
    $ventasPorCategoria = [];
    foreach ($ventas as $venta) {
      foreach ($venta->detalles as $detalle) {
        $categoria = $detalle->producto->categoria->nombre ?? 'Sin Categoría';
        $ventasPorCategoria[$categoria] = ($ventasPorCategoria[$categoria] ?? 0) + $detalle->subtotal;
      }
    }
    $ventasPorCategoria = array_map(fn($v) => round($v), $ventasPorCategoria);

    // Ventas por Producto
    $ventasPorProducto = [];
    foreach ($ventas as $venta) {
      foreach ($venta->detalles as $detalle) {
        $producto = $detalle->producto->nombre ?? 'Producto Eliminado';
        $ventasPorProducto[$producto] = ($ventasPorProducto[$producto] ?? 0) + $detalle->subtotal;
      }
    }
    $ventasPorProducto = array_map(fn($v) => round($v), $ventasPorProducto);

    // Ventas por Mes
    $ventasPorMes = $ventas->groupBy(fn($v) => Carbon::parse($v->fecha_venta)->format('Y-m'))
      ->map(fn($v) => round($v->sum('total')))
      ->sortKeys();

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
          'total' => round($venta->total),
          'metodo_pago' => $venta->metodo_pago,
          'estado' => $venta->estado,
          'fecha_venta' => $venta->fecha_venta ? Carbon::parse($venta->fecha_venta)->format('Y-m-d') : null,
          'detalles' => $venta->detalles->map(fn($d) => [
            'producto' => $d->producto->nombre ?? 'Producto Eliminado',
            'categoria' => $d->producto->categoria->nombre ?? 'Sin Categoría',
            'cantidad' => (int) $d->cantidad,
            'subtotal' => round($d->subtotal),
            'precio_unitario' => round($d->precio_unitario),
          ]),
        ];
      });

    return response()->json([
      'success' => true,
      'ventas' => $ventas,
    ]);
  }
}
