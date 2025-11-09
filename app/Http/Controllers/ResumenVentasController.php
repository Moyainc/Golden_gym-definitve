<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ResumenVentasController extends Controller
{
  // ✅ Formatear números sin decimales
  private function formatNumber($value)
  {
    return round($value);
  }

  // ✅ Página principal
  public function index()
  {
    $anios = Venta::selectRaw('YEAR(fecha_venta) as anio')
      ->distinct()
      ->orderBy('anio', 'asc')
      ->pluck('anio');

    return view('admin.contabilidad.resumen-ventas', compact('anios'));
  }

  // ✅ Datos JSON seguros
  public function data(Request $request)
  {
    try {
      $request->validate([
        'fecha_inicio' => 'nullable|date',
        'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        'anio' => 'nullable|integer',
        'mes' => 'nullable|integer',
      ]);

      $query = Venta::query();

      if ($request->filled('anio')) $query->whereYear('fecha_venta', $request->anio);
      if ($request->filled('mes')) $query->whereMonth('fecha_venta', $request->mes);

      $ventas = $query->with(['detalles.producto.categoria'])->get();

      $totalVentas = round($ventas->sum('total'));
      $numVentas = $ventas->count();
      $numProductos = $ventas->sum(fn($v) => $v->detalles->sum('cantidad'));
      $pendientes = $ventas->where('estado', 'Pendiente')->count();

      $ventasPorCategoria = [];
      $ventasPorProducto = [];
      foreach ($ventas as $venta) {
        foreach ($venta->detalles as $detalle) {
          $categoria = $detalle->producto->categoria->nombre ?? 'Sin Categoría';
          $ventasPorCategoria[$categoria] = ($ventasPorCategoria[$categoria] ?? 0) + $detalle->subtotal;

          $producto = $detalle->producto->nombre ?? 'Producto Eliminado';
          $ventasPorProducto[$producto] = ($ventasPorProducto[$producto] ?? 0) + $detalle->subtotal;
        }
      }

      $ventasPorCategoria = array_map(fn($v) => round($v), $ventasPorCategoria);
      $ventasPorProducto = array_map(fn($v) => round($v), $ventasPorProducto);

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
    } catch (\Throwable $e) {
      Log::error('Error ResumenVentasController@data: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Ocurrió un error al obtener los datos de ventas.'
      ], 500);
    }
  }

  // ✅ Últimas ventas JSON seguras
  public function ultimasVentas()
  {
    try {
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
    } catch (\Throwable $e) {
      Log::error('Error ResumenVentasController@ultimasVentas: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Ocurrió un error al obtener las últimas ventas.'
      ], 500);
    }
  }

  // ✅ Export PDF
  public function exportVentasPdf(Request $request)
  {
    try {
      $mes = $request->input('mes', date('m'));
      $anio = $request->input('anio', date('Y'));

      $ventas = Venta::with(['detalles.producto.categoria'])
        ->whereYear('fecha_venta', $anio)
        ->whereMonth('fecha_venta', $mes)
        ->orderByDesc('fecha_venta')
        ->get();

      $totalVentas = $ventas->sum('total');
      $numVentas = $ventas->count();
      $numProductos = $ventas->sum(fn($v) => $v->detalles->sum('cantidad'));
      $pendientes = $ventas->where('estado', 'Pendiente')->count();

      $pdf = Pdf::loadView('reports.ventas-pdf', [
        'ventas' => $ventas,
        'totalVentas' => $totalVentas,
        'numVentas' => $numVentas,
        'numProductos' => $numProductos,
        'pendientes' => $pendientes,
        'mes' => $mes,
        'anio' => $anio,
      ]);

      return $pdf->download("ventas-{$mes}-{$anio}.pdf");
    } catch (\Throwable $e) {
      Log::error('Error ResumenVentasController@exportVentasPdf: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error generando el PDF.');
    }
  }

  // ✅ Export Excel (CSV)
  public function exportVentasExcel(Request $request)
  {
    try {
      $mes = $request->input('mes', date('m'));
      $anio = $request->input('anio', date('Y'));

      $ventas = Venta::with(['detalles.producto.categoria'])
        ->whereYear('fecha_venta', $anio)
        ->whereMonth('fecha_venta', $mes)
        ->orderByDesc('fecha_venta')
        ->get();

      $csv = "ID,Producto,Categoría,Método de Pago,Precio,Fecha,Estado\n";

      foreach ($ventas as $venta) {
        foreach ($venta->detalles as $detalle) {
          $producto = $detalle->producto->nombre ?? 'Producto Eliminado';
          $categoria = $detalle->producto->categoria->nombre ?? 'Sin Categoría';
          $csv .= "{$venta->id},{$producto},{$categoria},{$venta->metodo_pago},{$detalle->subtotal},{$venta->fecha_venta},{$venta->estado}\n";
        }
      }

      return response()->streamDownload(function () use ($csv) {
        echo "\xEF\xBB\xBF" . $csv;
      }, "ventas-{$mes}-{$anio}.csv", ['Content-Type' => 'text/csv']);
    } catch (\Throwable $e) {
      Log::error('Error ResumenVentasController@exportVentasExcel: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error generando el Excel.');
    }
  }
}
