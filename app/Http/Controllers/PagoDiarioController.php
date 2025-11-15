<?php

namespace App\Http\Controllers;

use App\Models\PagoDiario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PagoGeneral;
use Illuminate\Support\Facades\DB; // Asegúrate de tener esto
class PagoDiarioController extends Controller
{
    public function index()
    {
        try {
            $page = (int) request()->query('page', 1) ?: 1;
            $perPage = (int) request()->query('per_page', 5) ?: 5;
            $skip = ($page - 1) * $perPage;

            $pagos = PagoDiario::orderByDesc('fecha')
                ->orderByDesc('id')
                ->skip($skip)
                ->take($perPage)
                ->get();

            $total = PagoDiario::count();
            $totalPages = ceil($total / $perPage);

            return response()->json([
                'data' => $pagos,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar pagos diarios: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cargar pagos diarios'], 500);
        }
    }

    public function store(Request $request)
    {
        // Validación
        $request->validate([
            'nombre_cliente' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta',
            'usuario_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Crear el registro en pagos diarios
            $pagoDiario = PagoDiario::create([
                'nombre_cliente' => $request->nombre_cliente,
                'monto' => $request->monto,
                'fecha' => $request->fecha,
                'metodo_pago' => $request->metodo_pago,
                'usuario_id' => $request->usuario_id,
            ]);

            // 2️⃣ Crear el registro en pagos generales usando morphs
            PagoGeneral::create([
                'origen_id' => $pagoDiario->id,
                'origen_type' => PagoDiario::class, // obligatorio para morph
                'tipo_pago' => 'pagos_diarios',
                'monto' => $request->monto,
                'metodo_pago' => $request->metodo_pago,
                'usuario_id' => $request->usuario_id ?? null,
                'fecha_pago' => Carbon::parse($request->fecha),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago diario registrado correctamente',
                'data' => $pagoDiario
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar pago diario: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el pago diario: ' . $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $pago = PagoDiario::findOrFail($id);
            $pago->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pago diario eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar pago diario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el pago diario'
            ], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

            $pagos = PagoDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->get();

            $data = [
                'pagos' => $pagos,
                'fecha_inicio' => $fechaInicio->format('d/m/Y'),
                'fecha_fin' => $fechaFin->format('d/m/Y'),
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'total' => $pagos->count(),
                'total_monto' => $pagos->sum('monto'),
                'logo_url' => asset('images/logo-gym.png'),
            ];

            $pdf = Pdf::loadView('reports.pagos-diarios-pdf', $data);
            return $pdf->download('reporte-pagos-diarios-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
