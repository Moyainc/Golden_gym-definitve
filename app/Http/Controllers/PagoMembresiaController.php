<?php

namespace App\Http\Controllers;

use App\Models\PagoMembresia;
use App\Models\Cliente;
use App\Models\Membresia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Traits\HasPrecioFormatted;

class PagoMembresiaController extends Controller
{
    use HasPrecioFormatted;

    // 📋 Listar todos los pagos (para JSON/AJAX)
    public function index()
    {
        $pagos = PagoMembresia::with(['cliente', 'membresia'])->orderByDesc('id')->get();
        return response()->json($pagos);
    }

    // 📄 Mostrar un pago específico
    public function show($id)
    {
        $pago = PagoMembresia::with(['cliente', 'membresia'])->findOrFail($id);
        return response()->json($pago);
    }

    // 💰 Registrar un nuevo pago de membresía
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'membresia_id' => 'required|exists:membresias,id',
            'metodo_pago' => 'nullable|string|max:50',
        ]);

        try {
            $membresia = Membresia::findOrFail($request->membresia_id);

            // 📆 Calcular fechas de inicio y fin
            $fechaInicio = Carbon::now();
            $fechaFin = Carbon::parse($fechaInicio);

            if ($membresia->tipo_duracion === 'meses') {
                $fechaFin->addMonths($membresia->duracion);
            } else {
                $fechaFin->addDays($membresia->duracion);
            }

            // 💾 Crear el registro
            try {
                $pago = PagoMembresia::create([
                    'cliente_id'   => $request->cliente_id,
                    'membresia_id' => $membresia->id,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin'    => $fechaFin,
                    'monto'        => $membresia->precio,
                    'metodo_pago'  => $request->metodo_pago ?? 'Efectivo',
                    'estado'       => $request->estado ?? 'Activo',
                ]);
            } catch (\InvalidArgumentException $e) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 422);
            }


            return response()->json([
                'success' => true,
                'message' => 'Pago de membresía registrado correctamente',
                'pago' => $pago,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en store PagoMembresia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno al crear el pago de membresía',
            ], 500);
        }
    }

    // ✏️ Actualizar un pago existente
    public function update(Request $request, $id)
    {
        $pago = PagoMembresia::findOrFail($id);

        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'nullable|string|max:50',
        ]);

        try {
            $pago->update($request->only(['fecha_inicio', 'fecha_fin', 'monto', 'metodo_pago']));
            return response()->json(['success' => true, 'pago' => $pago]);
        } catch (\Exception $e) {
            Log::error('Error update PagoMembresia: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    // 🗑️ Eliminar un pago
    public function destroy($id)
    {
        try {
            $pago = PagoMembresia::findOrFail($id);
            $pago->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error destroy PagoMembresia: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
