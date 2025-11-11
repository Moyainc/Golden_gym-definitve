<?php

namespace App\Http\Controllers;

use App\Models\PagoGeneral;
use App\Models\PagoMembresia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResumenGeneralController extends Controller
{
  public function obtenerDatos(Request $request)
  {
    $inicio = $request->input('inicio');
    $fin = $request->input('fin');
    $anio = $request->input('anio');

    // Validar fechas (si existen)
    if ($inicio && $fin) {
      try {
        $inicio = Carbon::parse($inicio)->startOfDay();
        $fin = Carbon::parse($fin)->endOfDay();
      } catch (\Exception $e) {
        return response()->json(['error' => 'Fechas inválidas.'], 422);
      }
    }

    // Base query común para pagos generales
    $baseQuery = PagoGeneral::query();

    if ($anio && $anio !== 'todos') {
      $baseQuery->whereYear('fecha_pago', $anio);
    }

    if ($inicio && $fin) {
      $baseQuery->whereBetween('fecha_pago', [$inicio, $fin]);
    }

    // === Cargar pagos relevantes ===
    $pagos = (clone $baseQuery)
      ->select('id', 'tipo_pago', 'origen_id', 'monto', 'metodo_pago', 'fecha_pago', 'estado')
      ->latest('fecha_pago')
      ->get();

    // === Totales generales ===
    $totalGeneral = $pagos->sum('monto');
    $totalMembresias = $pagos->filter(function ($pago) {
      if ($pago->tipo_pago !== 'pagos_membresias') return false;

      if (!$pago->origen_id) return true;

      $membresia = PagoMembresia::find($pago->origen_id);
      if (!$membresia) return true;
      return $membresia->estado !== 'Anulada';
    })->sum('monto');

    $totalProductos = $pagos->where('tipo_pago', 'venta_producto')->sum('monto');
    $totalPagosDiarios = $pagos->where('tipo_pago', 'pagos_diarios')->sum('monto');

    // === Datos mensuales (gráfico de barras) ===
    $mensuales = (clone $baseQuery)
      ->select(
        DB::raw('MONTH(fecha_pago) as mes'),
        DB::raw('SUM(monto) as total')
      )
      ->groupBy('mes')
      ->orderBy('mes')
      ->get();

    // === Distribución para gráfico pastel ===
    $distribucion = [
      'Membresías' => $totalMembresias,
      'Productos' => $totalProductos,
      'Pagos diarios' => $totalPagosDiarios,
    ];

    // === Últimos registros ===
    $ultimos = $pagos->take(6)->map(function ($pago) {
      $estado = ucfirst($pago->estado ?? 'Completado');

      // Solo para pagos de membresías
      if ($pago->tipo_pago === 'pagos_membresias' && $pago->origen_id) {
        $membresia = PagoMembresia::find($pago->origen_id);
        if ($membresia) {
          $hoy = Carbon::now();
          if ($membresia->estado === 'anulada') {
            $estado = 'Anulada';
          } elseif ($hoy->gt($membresia->fecha_fin)) {
            $estado = 'Vencida';
          } elseif ($hoy->lt($membresia->fecha_inicio)) {
            $estado = 'Próxima';
          } else {
            $estado = 'Activa';
          }
        }
      }

      return [
        'id' => $pago->id,
        'fecha' => $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'N/D',
        'concepto' => ucfirst(str_replace('_', ' ', $pago->tipo_pago)),
        'monto' => $pago->monto,
        'valor' => $this->formatearCOP($pago->monto),
        'metodo' => $pago->metodo_pago ?? 'Desconocido',
        'estado' => $estado,
      ];
    });


    // === Datos de Clientes para gráfico "Activos vs Nuevos" (últimos 12 meses) ===
    $primerPago = PagoMembresia::orderBy('fecha_inicio')->first();
    if ($primerPago) {
      $inicioGrafico = $primerPago->fecha_inicio->copy()->startOfMonth();
    } else {
      $inicioGrafico = Carbon::now()->startOfMonth();
    }

    // Construir 12 meses consecutivos desde el primer mes con datos
    $mesesGrafico = collect();
    for ($i = 0; $i < 12; $i++) {
      $mesesGrafico->push($inicioGrafico->copy()->addMonths($i));
    }

    $clientesData = [
      'meses' => [],
      'activos' => [],
      'nuevos' => [],
    ];

    foreach ($mesesGrafico as $mes) {
      $anioMes = $mes->year;
      $inicioMes = $mes->copy()->startOfMonth();
      $finMes = $mes->copy()->endOfMonth();

      // Etiqueta: abreviatura en español + año
      $mapMes = [
        1 => 'Ene',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Abr',
        5 => 'May',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Ago',
        9 => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Dic'
      ];
      $clientesData['meses'][] = $mapMes[$mes->month] . ' ' . $anioMes;

      // Clientes activos: membresía vigente en el mes
      $activos = PagoMembresia::whereDate('fecha_inicio', '<=', $finMes)
        ->whereDate('fecha_fin', '>=', $inicioMes)
        ->distinct('cliente_id')
        ->count('cliente_id');
      $clientesData['activos'][] = $activos;

      // Clientes nuevos: solo primera membresía que inicia en ese mes
      $nuevos = PagoMembresia::select('cliente_id')
        ->groupBy('cliente_id')
        ->havingRaw('MIN(fecha_inicio) >= ? AND MIN(fecha_inicio) <= ?', [$inicioMes, $finMes])
        ->count();

      // Agregar al array para enviar al frontend
      $clientesData['nuevos'][] = $nuevos;
    }

    // === Retornar respuesta completa ===
    return response()->json([
      'totales' => [
        'membresias' => $totalMembresias,
        'productos' => $totalProductos,
        'pagosDiarios' => $totalPagosDiarios,
        'general' => $totalGeneral,
      ],
      'mensuales' => $mensuales,
      'distribucion' => $distribucion,
      'ultimos' => $ultimos,
      'clientes' => $clientesData, // <- aquí van los clientes
    ]);
  }

  /**
   * Formatea valores numéricos en formato de moneda COP.
   */
  private function formatearCOP($valor)
  {
    if (!is_numeric($valor)) return '$0';
    return '$' . number_format($valor, 0, ',', '.');
  }
}
