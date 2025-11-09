<?php

namespace App\Http\Controllers;

use App\Models\PagoGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResumenGeneralController extends Controller
{

  public function obtenerDatos(Request $request)
  {
    $inicio = $request->input('inicio');
    $fin = $request->input('fin');
    $anio = $request->input('anio');

    // Base query común
    $baseQuery = PagoGeneral::query();

    if ($anio && $anio !== 'todos') {
      $baseQuery->whereYear('fecha_pago', $anio);
    }

    if ($inicio && $fin) {
      $baseQuery->whereBetween('fecha_pago', [$inicio, $fin]);
    }

    // Clonamos la query para evitar conflictos entre los cálculos
    $pagos = (clone $baseQuery)
      ->select('id', 'tipo_pago', 'origen_id', 'monto', 'metodo_pago', 'fecha_pago')
      ->latest('fecha_pago')
      ->get();

    // Totales generales
    $totalGeneral = $pagos->sum('monto');
    $totalMembresias = $pagos->where('tipo_pago', 'pagos_membresias ')->sum('monto');
    $totalProductos = $pagos->where('tipo_pago', 'venta_producto')->sum('monto');
    $totalPagosDiarios = $pagos->where('tipo_pago', 'pagos_diarios')->sum('monto');

    // Datos mensuales (gráfica de barras)
    $mensuales = (clone $baseQuery)
      ->select(
        DB::raw('MONTH(fecha_pago) as mes'),
        DB::raw('SUM(monto) as total')
      )
      ->groupBy('mes')
      ->orderBy('mes')
      ->get();

    // Distribución para gráfico pastel
    $distribucion = [
      'Membresías' => $totalMembresias,
      'Productos' => $totalProductos,
      'Pagos diarios' => $totalPagosDiarios,
    ];

    // Tendencia (últimos 12 meses)
    $tendencia = (clone $baseQuery)
      ->select(
        DB::raw('DATE_FORMAT(fecha_pago, "%Y-%m") as periodo'),
        DB::raw('SUM(monto) as total')
      )
      ->groupBy('periodo')
      ->orderBy('periodo')
      ->get();

    // Últimos registros
    $ultimos = $pagos->take(6)->map(function ($pago) {
      return [
        'id' => $pago->id,
        'fecha' => $pago->fecha_pago->format('d/m/Y'),
        'concepto' => ucfirst(str_replace('_', ' ', $pago->tipo_pago)),
        'valor' => $pago->precioFormatted($pago->monto),
        'monto' => $pago->monto,
        'metodo' => $pago->metodo_pago,
      ];
    });

    return response()->json([
      'totales' => [
        'membresias' => $totalMembresias,
        'productos' => $totalProductos,
        'pagosDiarios' => $totalPagosDiarios,
        'general' => $totalGeneral,
      ],
      'mensuales' => $mensuales,
      'distribucion' => $distribucion,
      'tendencia' => $tendencia,
      'ultimos' => $ultimos,
    ]);
  }
}
