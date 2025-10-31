<?php

namespace App\Http\Controllers;

use App\Models\PagoDiario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagoDiarioController extends Controller
{
    public function index()
    {
        try {
            $pagos = PagoDiario::orderByDesc('fecha_pago')
                ->orderByDesc('id')
                ->limit(15)
                ->get();

            return response()->json($pagos);
        } catch (\Exception $e) {
            Log::error('Error al listar pagos diarios: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cargar pagos diarios'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'monto' => 'required|numeric|min:0',
                'fecha_pago' => 'required|date',
                'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta',
            ]);

            $pago = PagoDiario::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Pago diario registrado correctamente',
                'data' => $pago
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al guardar pago diario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el pago diario'
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
}
