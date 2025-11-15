<?php

namespace App\Http\Controllers;

use App\Models\PagoGeneral;
use App\Models\PagoMembresia;
use App\Models\PagoDiario;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;


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
      ->where(function ($q) {
        $q->whereNotIn(DB::raw('LOWER(estado)'), ['anulada', 'anulado'])
          ->orWhereNull('estado');
      })


      ->latest('fecha_pago')
      ->get();


    // === Totales generales ===
    $totalGeneral = $pagos->sum('monto');

    $totalMembresias = $pagos->filter(function ($pago) {
      if ($pago->tipo_pago !== 'pagos_membresias') return false;
      if (!$pago->origen_id) return true;
      $membresia = PagoMembresia::find($pago->origen_id);
      return $membresia && !in_array(strtolower($membresia->estado), ['anulada', 'anulado']);
    })->sum('monto');

    $totalProductos = $pagos->where('tipo_pago', 'venta_producto')
      ->filter(fn($p) => !in_array(strtolower($p->estado), ['anulada', 'anulado']))
      ->sum('monto');

    $totalPagosDiarios = $pagos->where('tipo_pago', 'pagos_diarios')
      ->where('estado', '!=', 'Anulada')
      ->sum('monto');

    // === Datos mensuales (gráfico de barras) ===
    $mensuales = (clone $baseQuery)
      ->select(
        DB::raw('MONTH(fecha_pago) as mes'),
        DB::raw('SUM(monto) as total')
      )
      ->where(function ($q) {
        $q->where('estado', '!=', 'Anulada')
          ->orWhereNull('estado');
      })
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
    $ultimos = $pagos->reject(fn($pago) => strtolower($pago->estado) === 'anulada')
      ->take(6)
      ->map(function ($pago) {
        $estado = ucfirst($pago->estado ?? 'Completado');

      if ($pago->tipo_pago === 'pagos_membresias' && $pago->origen_id) {
        $membresia = PagoMembresia::find($pago->origen_id);

        if ($membresia) {
          $hoy = Carbon::now();

          // Verificar si faltan fechas
          if (!$membresia->fecha_inicio || !$membresia->fecha_fin) {
            $estado = 'Sin fechas';
          } elseif ($membresia->estado === 'anulada') {
            $estado = 'Anulada';
          } elseif ($hoy->gt(Carbon::parse($membresia->fecha_fin))) {
            $estado = 'Vencida';
          } elseif ($hoy->lt(Carbon::parse($membresia->fecha_inicio))) {
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
    $primerPago = PagoMembresia::whereNotNull('fecha_inicio')
      ->orderBy('fecha_inicio')
      ->first();

    if ($primerPago && $primerPago->fecha_inicio) {
      $inicioGrafico = Carbon::parse($primerPago->fecha_inicio)->copy()->startOfMonth();
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
  // =========================================
  // Exportar Excel
  // =========================================
  public function exportExcel(Request $request)
  {
    $request->validate([
      'inicio' => 'nullable|date',
      'fin' => 'nullable|date|after_or_equal:inicio',
      'anio' => 'nullable|string',
    ]);

    try {
      $inicio = $request->input('inicio');
      $fin = $request->input('fin');
      $anio = $request->input('anio');

      // Base query
      $baseQuery = PagoGeneral::query();

      if ($anio && $anio !== 'todos') {
        $baseQuery->whereYear('fecha_pago', $anio);
      }

      if ($inicio && $fin) {
        $baseQuery->whereBetween('fecha_pago', [$inicio, $fin]);
      }

      $pagos = $baseQuery->latest('fecha_pago')->get();

      // Totales
      $totalMembresias = $pagos->where('tipo_pago', 'pagos_membresias')->sum('monto');
      $totalProductos = $pagos->where('tipo_pago', 'venta_producto')->sum('monto');
      $totalPagosDiarios = $pagos->where('tipo_pago', 'pagos_diarios')->sum('monto');
      $totalGeneral = $totalMembresias + $totalProductos + $totalPagosDiarios;

      $spreadsheet = new Spreadsheet();

      // ===== HOJA 1: MEMBRESÍAS =====
      $sheet1 = $spreadsheet->getActiveSheet();
      $sheet1->setTitle('Membresías');
      $sheet1->fromArray([
        ['N°', 'Origen ID', 'Monto', 'Método de Pago', 'Fecha']
      ]);

      $row = 2;
      $numero = 1;
      foreach ($pagos->where('tipo_pago', 'pagos_membresias') as $pago) {
        $sheet1->fromArray([
          [
            $numero++,
            $pago->origen_id,
            $pago->monto,
            ucfirst($pago->metodo_pago ?? 'No especificado'),
            Carbon::parse($pago->fecha_pago)->format('d/m/Y')
          ]
        ], null, "A{$row}");
        $row++;
      }

      $lastRow1 = $row - 1;
      $sheet1->getStyle("A1:E{$lastRow1}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
      $sheet1->getStyle("A1:E1")->getFont()->setBold(true)->getColor()->setRGB('272727');
      $sheet1->getStyle("A1:E1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('fcd52b');
      $sheet1->getStyle("A1:E{$lastRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      foreach (range('A', 'E') as $col) $sheet1->getColumnDimension($col)->setAutoSize(true);

      // ===== HOJA 2: PRODUCTOS =====
      $sheet2 = $spreadsheet->createSheet();
      $sheet2->setTitle('Productos');
      $sheet2->fromArray([
        ['N°', 'Origen ID', 'Monto', 'Método de Pago', 'Fecha']
      ]);

      $row = 2;
      $numero = 1;
      foreach ($pagos->where('tipo_pago', 'venta_producto') as $pago) {
        $sheet2->fromArray([
          [
            $numero++,
            $pago->origen_id,
            $pago->monto,
            ucfirst($pago->metodo_pago ?? 'No especificado'),
            Carbon::parse($pago->fecha_pago)->format('d/m/Y')
          ]
        ], null, "A{$row}");
        $row++;
      }

      $lastRow2 = $row - 1;
      $sheet2->getStyle("A1:E{$lastRow2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
      $sheet2->getStyle("A1:E1")->getFont()->setBold(true)->getColor()->setRGB('272727');
      $sheet2->getStyle("A1:E1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('fcd52b');
      $sheet2->getStyle("A1:E{$lastRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      foreach (range('A', 'E') as $col) $sheet2->getColumnDimension($col)->setAutoSize(true);

      // ===== HOJA 3: PAGOS DIARIOS =====
      $sheet3 = $spreadsheet->createSheet();
      $sheet3->setTitle('Pagos Diarios');
      $sheet3->fromArray([
        ['N°', 'Origen ID', 'Monto', 'Método de Pago', 'Fecha']
      ]);

      $row = 2;
      $numero = 1;
      foreach ($pagos->where('tipo_pago', 'pagos_diarios') as $pago) {
        $sheet3->fromArray([
          [
            $numero++,
            $pago->origen_id,
            $pago->monto,
            ucfirst($pago->metodo_pago ?? 'No especificado'),
            Carbon::parse($pago->fecha_pago)->format('d/m/Y')
          ]
        ], null, "A{$row}");
        $row++;
      }

      $lastRow3 = $row - 1;
      $sheet3->getStyle("A1:E{$lastRow3}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
      $sheet3->getStyle("A1:E1")->getFont()->setBold(true)->getColor()->setRGB('272727');
      $sheet3->getStyle("A1:E1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('fcd52b');
      $sheet3->getStyle("A1:E{$lastRow3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      foreach (range('A', 'E') as $col) $sheet3->getColumnDimension($col)->setAutoSize(true);

      // ===== HOJA 4: RESUMEN GENERAL =====
      $sheet4 = $spreadsheet->createSheet();
      $sheet4->setTitle('Resumen General');
      $sheet4->fromArray([
        ['Concepto', 'Total'],
        ['Membresías', $totalMembresias],
        ['Productos', $totalProductos],
        ['Pagos Diarios', $totalPagosDiarios],
        ['Total General', $totalGeneral]
      ]);

      $sheet4->getStyle("A1:B5")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
      $sheet4->getStyle("A1:B1")->getFont()->setBold(true)->getColor()->setRGB('272727');
      $sheet4->getStyle("A1:B1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('fcd52b');
      $sheet4->getStyle("A1:B5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      foreach (range('A', 'B') as $col) $sheet4->getColumnDimension($col)->setAutoSize(true);

      // Descargar
      $filename = 'resumen-general-' . date('Y-m-d') . '.xlsx';
      $writer = new Xlsx($spreadsheet);
      return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
      }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      ]);
    } catch (\Exception $e) {
      Log::error('Error al generar Excel Resumen General: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error al generar el archivo: ' . $e->getMessage()
      ], 500);
    }
  }


  // =========================================
  // Exportar PDF
  // =========================================
  public function exportPdf(Request $request)
  {
    try {
      // ✅ Validar fechas obligatorias
      $request->validate([
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
      ]);

      $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
      $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
      $fecha_generacion = Carbon::now()->format('d/m/Y H:i');

      // === MEMBRESÍAS ===
      $pagosMembresias = PagoMembresia::with(['cliente', 'membresia'])
        ->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
        ->get();

      // ===== CALCULAR ESTADO AUTOMÁTICO =====
      $today = Carbon::now();
      foreach ($pagosMembresias as $pago) {
        if ($today->between($pago->fecha_inicio, $pago->fecha_fin)) {
          $pago->estado = 'Activa';
        } elseif ($today->lessThan($pago->fecha_inicio)) {
          $pago->estado = 'Próxima a vencer';
        } elseif ($today->greaterThan($pago->fecha_fin)) {
          $pago->estado = 'Vencida';
        } else {
          $pago->estado = 'Sin Membresía';
        }
      }

      $totalMembresias = $pagosMembresias->sum('monto');

      // === PRODUCTOS (VENTAS CON DETALLES) ===
      $pagosProductos = Venta::with(['cliente', 'detalles.producto'])
        ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
        ->get();

      // Si no tiene cliente (venta rápida), asignamos texto
      foreach ($pagosProductos as $venta) {
        if (!$venta->cliente) {
          $venta->cliente = (object)[
            'nombre_completo' => 'Venta Rápida'
          ];
        }

        // Asegurar compatibilidad con la vista
        $venta->fecha = $venta->fecha_venta;
        foreach ($venta->detalles as $detalle) {
          $detalle->precio_total = $detalle->subtotal ?? ($detalle->cantidad * $detalle->precio_unitario);
        }
      }

      $totalProductos = $pagosProductos->sum('total');

      // === PAGOS DIARIOS ===
      $pagosDiarios = PagoDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])
        ->get()
        ->map(function ($pago) {
          return (object)[
            'id' => $pago->id,
            'cliente' => (object)[
              'nombre_completo' => $pago->nombre_cliente ?? 'Sin cliente'
            ],
            'monto' => $pago->monto,
            'metodo_pago' => $pago->metodo_pago ?? 'N/A',
            'fecha_pago' => $pago->fecha,
          ];
        });

      $totalPagosDiarios = $pagosDiarios->sum('monto');

      // === TOTAL GENERAL ===
      $totalGeneral = $totalMembresias + $totalProductos + $totalPagosDiarios;

      // === DATA PARA LA VISTA ===
      $data = [
        'pagosMembresias' => $pagosMembresias,
        'pagosProductos' => $pagosProductos,
        'pagosDiarios' => $pagosDiarios,
        'totalMembresias' => $totalMembresias,
        'totalProductos' => $totalProductos,
        'totalPagosDiarios' => $totalPagosDiarios,
        'totalGeneral' => $totalGeneral,
        'fecha_inicio' => $fechaInicio->format('d/m/Y'),
        'fecha_fin' => $fechaFin->format('d/m/Y'),
        'fecha_generacion' => $fecha_generacion,
      ];

      // === GENERAR PDF ===
      $pdf = PDF::loadView('reports.resumen-general', $data)->setPaper('A4', 'portrait');
      $filename = 'resumen-general-' . now()->format('d-m-y_His') . '.pdf';

      // Forzar descarga
      return response()->streamDownload(function () use ($pdf) {
        echo $pdf->output();
      }, $filename, ['Content-Type' => 'application/pdf']);
    } catch (\Throwable $e) {
      Log::error('Error al generar PDF: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error al generar el PDF: ' . $e->getMessage(),
      ], 500);
    }
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
