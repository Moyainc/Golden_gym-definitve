<?php

namespace App\Http\Controllers;

use App\Models\PagoMembresia;
use App\Models\PagoDiario;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ResumenMembresiasController extends Controller
{
    public function index()
    {
        return view('admin.contabilidad.resumen-membresias');
    }

    public function getResumenContabilidad(Request $request)
    {
        $mes = $request->input('mes', date('m'));
        $anio = $request->input('anio', date('Y'));

        try {
            $pagosMembresias = PagoMembresia::where(function ($q) use ($mes, $anio) {
                $q->whereMonth('fecha_inicio', $mes)
                    ->whereYear('fecha_inicio', $anio);
            })
                ->orWhere('estado', 'anulado')
                ->with(['cliente', 'usuario'])
                ->get();


            $pagosDiarios = PagoDiario::whereMonth('fecha', $mes)
                ->whereYear('fecha', $anio)
                ->with('usuario')
                ->get();

            // Totales (sin incluir anuladas)
            $totalMembresias = $pagosMembresias->where('estado', '!=', 'anulado')->sum('monto');
            $totalDiarios = $pagosDiarios->sum('monto');
            $totalGeneral = $totalMembresias + $totalDiarios;
            $cantidadMembresias = $pagosMembresias->where('estado', '!=', 'anulado')->count();

            $movimientos = [];

            // === MOVIMIENTOS DE MEMBRESÍAS (INCLUYE ANULADAS) ===
            foreach ($pagosMembresias as $pago) {
                $estado = $pago->estado === 'anulado'
                    ? 'Anulado'
                    : $this->determinarEstado($pago->fecha_fin);

                $movimientos[] = [
                    'id' => $pago->id,
                    'tipo' => 'Membresía',
                    'nombre_completo' => $pago->cliente->nombre_completo ?? 'Sin cliente',
                    'nombre_usuario' => $pago->usuario->nombre_completo ?? 'Desconocido',
                    'metodo_pago' => $pago->metodo_pago ?? 'No especificado',
                    'valor' => $pago->monto,
                    'fecha' => $pago->fecha_inicio ?? $pago->created_at,
                    'estado' => ucfirst($estado),
                ];
            }

            // === MOVIMIENTOS DE PAGOS DIARIOS ===
            foreach ($pagosDiarios as $pago) {
                $movimientos[] = [
                    'id' => $pago->id,
                    'tipo' => 'Pago Diario',
                    'nombre_completo' => $pago->nombre_cliente ?? 'Sin nombre',
                    'nombre_usuario' => $pago->usuario->nombre_completo ?? 'Desconocido',
                    'metodo_pago' => $pago->metodo_pago ?? 'No especificado',
                    'valor' => $pago->monto ?? 0,
                    'fecha' => $pago->fecha ?? now(),
                    'estado' => 'Completado',
                ];
            }

            // Ordenar por fecha descendente
            usort($movimientos, fn($a, $b) => strtotime($b['fecha']) - strtotime($a['fecha']));

            $datosDistribucion = [
                'membresias' => $totalMembresias,
                'diarios' => $totalDiarios,
            ];

            $tendenciaDatos = $this->getTendenciaUltimos30Dias($mes, $anio);

            return response()->json([
                'success' => true,
                'cards' => [
                    'ingreso_membresias' => $totalMembresias,
                    'ingreso_diarios' => $totalDiarios,
                    'cantidad_membresias' => $cantidadMembresias,
                    'ingreso_total' => $totalGeneral,
                ],
                'movimientos' => $movimientos,
                'distribucion' => $datosDistribucion,
                'tendencia' => $tendenciaDatos,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getResumenContabilidad: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


    private function determinarEstado($fechaFin)
    {
        $ahora = Carbon::now();
        $fecha = Carbon::parse($fechaFin);
        $diasRestantes = $ahora->diffInDays($fecha, false);

        if ($diasRestantes < 0) {
            return 'Vencida';
        } elseif ($diasRestantes <= 5) {
            return 'Próxima a vencer';
        } else {
            return 'Activa';
        }
    }

    private function getTendenciaUltimos30Dias($mes, $anio)
    {
        $datos = [];
        for ($dia = 1; $dia <= 31; $dia++) {
            try {
                $fecha = Carbon::createFromDate($anio, $mes, $dia);
                if ($fecha->month != $mes) break;

                $membresias = PagoMembresia::whereDate('fecha_inicio', $fecha)->sum('monto');
                $diarios = PagoDiario::whereDate('fecha', $fecha)->sum('monto');
                $total = $membresias + $diarios;

                $datos[] = [
                    'dia' => $dia,
                    'total' => $total,
                ];
            } catch (\Exception $e) {
                continue;
            }
        }
        return $datos;
    }

    // ==================================================
    // EXPORTACIÓN PDF
    // ==================================================
    public function exportContabilidadPdf(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-01'));
        $fecha_fin = $request->input('fecha_fin', date('Y-m-d'));
        $incluirPagosDiarios = $request->input('incluir_pagos_diarios', 0);

        $pagosMembresias = PagoMembresia::with(['cliente', 'usuario'])
            ->whereBetween('fecha_inicio', [$fecha_inicio, $fecha_fin])
            ->get();

        foreach ($pagosMembresias as $pago) {
            $pago->estado = $this->determinarEstado($pago->fecha_fin);
        }

        $pagosDiarios = collect();
        if ($incluirPagosDiarios) {
            $pagosDiarios = PagoDiario::with('usuario')
                ->whereBetween('fecha', [$fecha_inicio, $fecha_fin])
                ->get();
        }

        $totalMembresias = $pagosMembresias->sum('monto');
        $totalDiarios = $pagosDiarios->sum('monto');
        $totalGeneral = $totalMembresias + $totalDiarios;

        $pdf = Pdf::loadView('reports.contabilidad-pdf', [
            'pagosMembresias' => $pagosMembresias,
            'pagosDiarios' => $pagosDiarios,
            'totalMembresias' => $totalMembresias,
            'totalDiarios' => $totalDiarios,
            'totalGeneral' => $totalGeneral,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'fecha_generacion' => now()->format('d/m/Y'),
        ]);

        return $pdf->download("contabilidad-membresias-{$fecha_inicio}-{$fecha_fin}.pdf");
    }

    // ==================================================
    // EXPORTACIÓN EXCEL
    // ==================================================
    public function exportContabilidadExcel(Request $request)
    {
        $mes = $request->input('mes', date('m'));
        $anio = $request->input('anio', date('Y'));
        $incluirPagosDiarios = $request->input('incluir_pagos_diarios', 0);

        $pagosMembresias = PagoMembresia::whereMonth('fecha_inicio', $mes)
            ->whereYear('fecha_inicio', $anio)
            ->with(['cliente', 'usuario'])
            ->get();

        $pagosDiarios = collect();
        if ($incluirPagosDiarios) {
            $pagosDiarios = PagoDiario::whereMonth('fecha', $mes)
                ->whereYear('fecha', $anio)
                ->with('usuario')
                ->get();
        }

        $totalMembresias = $pagosMembresias->sum('monto');
        $totalDiarios = $pagosDiarios->sum('monto');
        $totalGeneral = $totalMembresias + $totalDiarios;

        $fecha_inicio = Carbon::createFromDate($anio, $mes, 1)->startOfMonth()->format('d/m/Y');
        $fecha_fin = Carbon::createFromDate($anio, $mes, 1)->endOfMonth()->format('d/m/Y');

        $spreadsheet = new Spreadsheet();

        /*
    |-------------------------------------------------------------------------- 
    | Hoja 1 - Membresías 
    |-------------------------------------------------------------------------- 
    */
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Membresías');

        $sheet1->fromArray(
            ['ID', 'Cliente', 'Registrado por', 'Monto', 'Método de Pago', 'Fecha Inicio', 'Fecha Fin', 'Estado'],
            null,
            'A1'
        );

        $row = 2;
        foreach ($pagosMembresias as $pago) {
            $sheet1->fromArray([
                $pago->id,
                $pago->cliente->nombre_completo ?? 'Sin cliente',
                $pago->usuario->nombre_completo ?? 'Desconocido',
                $pago->monto,
                $pago->metodo_pago,
                Carbon::parse($pago->fecha_inicio)->format('d/m/Y'),
                Carbon::parse($pago->fecha_fin)->format('d/m/Y'),
                $this->determinarEstado($pago->fecha_fin)
            ], null, "A{$row}");
            $row++;
        }

        $sheet1->setCellValue("A{$row}", 'TOTAL');
        $sheet1->setCellValue("D{$row}", $totalMembresias);
        $sheet1->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);

        // Estilos hoja 1
        $sheet1->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet1->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');
        $sheet1->getStyle("A1:H{$row}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet1->getStyle("A1:H{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        foreach (range('A', 'H') as $col) $sheet1->getColumnDimension($col)->setAutoSize(true);

        /*
    |-------------------------------------------------------------------------- 
    | Hoja 2 - Pagos Diarios 
    |-------------------------------------------------------------------------- 
    */
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Pagos Diarios');

        $sheet2->fromArray(
            ['ID', 'Cliente', 'Registrado por', 'Monto', 'Método de Pago', 'Fecha', 'Estado'],
            null,
            'A1'
        );

        $row = 2;
        foreach ($pagosDiarios as $pago) {
            $sheet2->fromArray([
                $pago->id,
                $pago->nombre_cliente ?? 'Sin nombre',
                $pago->usuario->nombre_completo ?? 'Desconocido',
                $pago->monto,
                $pago->metodo_pago ?? 'No especificado',
                Carbon::parse($pago->fecha)->format('d/m/Y'),
                'Completado'
            ], null, "A{$row}");
            $row++;
        }

        $sheet2->setCellValue("A{$row}", 'TOTAL');
        $sheet2->setCellValue("D{$row}", $totalDiarios);
        $sheet2->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);

        // Estilos hoja 2
        $sheet2->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet2->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');
        $sheet2->getStyle("A1:G{$row}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle("A1:G{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        foreach (range('A', 'G') as $col) $sheet2->getColumnDimension($col)->setAutoSize(true);

        /*
    |-------------------------------------------------------------------------- 
    | Hoja 3 - Resumen General 
    |-------------------------------------------------------------------------- 
    */
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Resumen General');

        $sheet3->fromArray([
            ['Concepto', 'Total ($)'],
            ['Total Membresías', $totalMembresias],
            ['Total Pagos Diarios', $totalDiarios],
            ['Total General', $totalGeneral],
            ['Período', "{$fecha_inicio} al {$fecha_fin}"]
        ], null, 'A1');

        $sheet3->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet3->getStyle('A1:B1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFCD52B');
        $sheet3->getStyle('A1:B5')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet3->getStyle('A1:B5')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        foreach (range('A', 'B') as $col) $sheet3->getColumnDimension($col)->setAutoSize(true);

        /*
    |-------------------------------------------------------------------------- 
    | Exportar archivo 
    |-------------------------------------------------------------------------- 
    */
        $writer = new Xlsx($spreadsheet);
        $filename = "contabilidad_membresias_{$mes}_{$anio}.xlsx";

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }


    //AÑOS
    public function getAniosDisponibles()
    {
        $aniosMembresias = PagoMembresia::selectRaw('YEAR(fecha_inicio) as anio')->distinct()->pluck('anio');
        $aniosDiarios = PagoDiario::selectRaw('YEAR(fecha) as anio')->distinct()->pluck('anio');

        $anios = $aniosMembresias->merge($aniosDiarios)->unique()->sort()->values();

        return response()->json(['success' => true, 'anios' => $anios]);
    }
}
