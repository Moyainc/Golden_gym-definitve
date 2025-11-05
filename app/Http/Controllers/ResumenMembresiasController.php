<?php

namespace App\Http\Controllers;

use App\Models\PagoMembresia;
use App\Models\PagoDiario;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

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
            // Obtener datos de membresías
            $pagosMembresias = PagoMembresia::whereMonth('fecha_inicio', $mes)
                ->whereYear('fecha_inicio', $anio)
                ->with('cliente')
                ->get();

            // Obtener datos de pagos diarios
            $pagosDiarios = PagoDiario::whereMonth('fecha', $mes)
                ->whereYear('fecha', $anio)
                ->get();

            // Calcular totales
            $totalMembresias = $pagosMembresias->sum('monto');
            $totalDiarios = $pagosDiarios->sum('monto');
            $totalGeneral = $totalMembresias + $totalDiarios;
            $cantidadMembresias = $pagosMembresias->count();

            // Preparar movimientos
            $movimientos = [];

            // Movimientos de membresías
            foreach ($pagosMembresias as $pago) {
                $movimientos[] = [
                    'id' => $pago->id,
                    'tipo' => 'Membresía',
                    'detalle' => $pago->cliente->nombre ?? 'Sin cliente',
                    'metodo_pago' => $pago->metodo_pago ?? 'No especificado',
                    'valor' => $pago->monto,
                    'fecha' => $pago->fecha_inicio,
                    'estado' => $this->determinarEstado($pago->fecha_fin),
                ];
            }

            // Movimientos de pagos diarios
            foreach ($pagosDiarios as $pago) {
                $movimientos[] = [
                    'id' => $pago->id,
                    'tipo' => 'Pago Diario',
                    'detalle' => $pago->nombre_cliente ?? 'Sin nombre',
                    'metodo_pago' => $pago->metodo_pago ?? 'No especificado',
                    'valor' => $pago->monto ?? 0,
                    'fecha' => $pago->fecha ?? now(),
                    'estado' => 'Completado',
                ];
            }

            // Ordenar por fecha descendente
            usort($movimientos, function ($a, $b) {
                return strtotime($b['fecha']) - strtotime($a['fecha']);
            });

            // Datos para gráfica de distribución
            $datosDistribucion = [
                'membresias' => $totalMembresias,
                'diarios' => $totalDiarios,
            ];

            // Datos para gráfica de tendencia (últimos 30 días)
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

    public function exportContabilidadPdf(Request $request)
    {
        $mes = $request->input('mes', date('m'));
        $anio = $request->input('anio', date('Y'));

        $pagosMembresias = PagoMembresia::whereMonth('fecha_inicio', $mes)
            ->whereYear('fecha_inicio', $anio)
            ->with('cliente')
            ->get();

        $pagosDiarios = PagoDiario::whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->get();

        $totalMembresias = $pagosMembresias->sum('monto');
        $totalDiarios = $pagosDiarios->sum('monto');
        $totalGeneral = $totalMembresias + $totalDiarios;

        $pdf = Pdf::loadView('reports.contabilidad-pdf', [
            'pagosMembresias' => $pagosMembresias,
            'pagosDiarios' => $pagosDiarios,
            'totalMembresias' => $totalMembresias,
            'totalDiarios' => $totalDiarios,
            'totalGeneral' => $totalGeneral,
            'mes' => $mes,
            'anio' => $anio,
        ]);

        return $pdf->download("contabilidad-{$mes}-{$anio}.pdf");
    }

    public function exportContabilidadExcel(Request $request)
    {
        $mes = $request->input('mes', date('m'));
        $anio = $request->input('anio', date('Y'));

        $pagosMembresias = PagoMembresia::whereMonth('fecha_inicio', $mes)
            ->whereYear('fecha_inicio', $anio)
            ->with('cliente')
            ->get();

        $pagosDiarios = PagoDiario::whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->get();

        $totalMembresias = $pagosMembresias->sum('monto');
        $totalDiarios = $pagosDiarios->sum('monto');

        $csv = "ID,Tipo,Detalle,Método de Pago,Valor,Fecha,Estado\n";

        foreach ($pagosMembresias as $pago) {
            $clienteNombre = $pago->cliente ? $pago->cliente->nombre : 'Sin cliente';
            $csv .= "{$pago->id},Membresía,{$clienteNombre},{$pago->metodo_pago},\${$pago->monto},{$pago->fecha_inicio},{$this->determinarEstado($pago->fecha_fin)}\n";
        }

        foreach ($pagosDiarios as $pago) {
            $nombre = $pago->nombre_cliente ?? 'Sin nombre';
            $metodo = $pago->metodo_pago ?? 'No especificado';
            $csv .= "{$pago->id},Pago Diario,{$nombre},{$metodo},\${$pago->monto},{$pago->fecha},Completado\n";
        }

        $csv .= "\nTotal Membresías,,,\${$totalMembresias}\n";
        $csv .= "Total Pagos Diarios,,,\${$totalDiarios}\n";
        $csv .= "Total General,,,\$" . ($totalMembresias + $totalDiarios) . "\n";

        return response()->streamDownload(function () use ($csv) {
            echo "\xEF\xBB\xBF" . $csv;
        }, "contabilidad-{$mes}-{$anio}.csv", ['Content-Type' => 'text/csv']);
    }
}
