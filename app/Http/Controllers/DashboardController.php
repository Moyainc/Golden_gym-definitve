<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\PagoMembresia;
use App\Models\PagoDiario;
use App\Models\PagoGeneral;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
  public function index()
  {
    return view('admin.dashboard'); // tu Blade
  }

  // Datos para las cards
  public function cardsData(Request $request)
  {
    $filter = $request->get('filter', 'week'); // week o month
    $start = $filter === 'week' ? Carbon::now()->startOfWeek() : Carbon::now()->startOfMonth();
    $end = Carbon::now();

    // Productos vendidos
    $ventas = Venta::whereBetween('fecha_venta', [$start, $end])->sum('total');

    // Membresías (PagoMembresia + PagoDiario)
    $membresias = PagoMembresia::whereBetween('fecha_inicio', [$start, $end])->sum('monto') +
      PagoDiario::whereBetween('fecha', [$start, $end])->sum('monto');

    // Total (pagos_generales)
    $total = PagoGeneral::whereBetween('fecha_pago', [$start, $end])->sum('monto');

    // Nuevos clientes
    $clientes = Cliente::whereBetween('created_at', [$start, $end])->count();

    // Comparaciones
    $prevStart = $filter === 'week' ? Carbon::now()->subWeek()->startOfWeek() : Carbon::now()->subMonth()->startOfMonth();
    $prevEnd = $filter === 'week' ? Carbon::now()->subWeek()->endOfWeek() : Carbon::now()->subMonth()->endOfMonth();

    $comparisons = [
      'ventas' => $this->percentageChange(
        $ventas,
        Venta::whereBetween('fecha_venta', [$prevStart, $prevEnd])->sum('total')
      ),
      'membresias' => $this->percentageChange($membresias, PagoMembresia::whereBetween('fecha_inicio', [$prevStart, $prevEnd])->sum('monto') +
        PagoDiario::whereBetween('fecha', [$prevStart, $prevEnd])->sum('monto')),
      'total' => $this->percentageChange($total, PagoGeneral::whereBetween('fecha_pago', [$prevStart, $prevEnd])->sum('monto')),
      'clientes' => $this->percentageChange($clientes, Cliente::whereBetween('created_at', [$prevStart, $prevEnd])->count()),
    ];

    return response()->json([
      'ventas' => $ventas,
      'membresias' => $membresias,
      'total' => $total,
      'clientes' => $clientes,
      'comparisons' => $comparisons,
    ]);
  }

  // Datos del gráfico de ingresos totales mensuales
  public function monthlyChart()
  {
    $data = PagoGeneral::select(
      DB::raw('MONTH(fecha_pago) as month'),
      DB::raw('SUM(monto) as total')
    )
      ->whereYear('fecha_pago', Carbon::now()->year)
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    $labels = $data->pluck('month')->map(fn($m) => Carbon::create()->month($m)->format('M'));
    $totals = $data->pluck('total');

    return response()->json([
      'labels' => $labels,
      'totals' => $totals,
    ]);
  }

  // Gráfico ingresos por membresías
  public function membershipChart()
  {
    $data = PagoMembresia::select(
      DB::raw('MONTH(fecha_inicio) as month'),
      DB::raw('SUM(monto) as total')
    )
      ->whereYear('fecha_inicio', Carbon::now()->year)
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    $labels = $data->pluck('month')->map(fn($m) => Carbon::create()->month($m)->format('M'));
    $totals = $data->pluck('total');

    return response()->json([
      'labels' => $labels,
      'totals' => $totals,
    ]);
  }

  // Últimos movimientos
  public function lastMovements()
  {
    $movements = PagoGeneral::with('origen')
      ->orderByDesc('fecha_pago')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        return [
          'tipo' => $item->tipo_pago,
          'cliente' => $item->origen?->cliente?->nombre_completo ?? '-',
          'monto' => $item->monto,
          'metodo_pago' => $item->metodo_pago,
          'fecha' => $item->fecha_pago->format('d M Y'),
        ];
      });

    return response()->json($movements);
  }

  // Función auxiliar porcentaje
  private function percentageChange($current, $previous)
  {
    if ($previous == 0) return 100;
    return round((($current - $previous) / $previous) * 100, 2);
  }
}
