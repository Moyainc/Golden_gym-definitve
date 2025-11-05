<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\PagoMembresia;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ClienteController extends Controller
{
    /**
     * Retorna el listado completo de clientes con su membresía actual
     */
    public function getClientes()
    {
        $clientes = Cliente::with(['membresiaActual.membresia'])
            ->orderByDesc('id')
            ->limit(15)
            ->get()
            ->map(function ($cliente) {
                if ($cliente->membresiaActual) {
                    $cliente->estado = $this->calcularEstadoMembresia(
                        $cliente->membresiaActual->fecha_inicio,
                        $cliente->membresiaActual->fecha_fin
                    );
                    $cliente->membresia_nombre = $cliente->membresiaActual->membresia->nombre ?? 'N/A';
                    $cliente->fecha_inicio = $cliente->membresiaActual->fecha_inicio ? $cliente->membresiaActual->fecha_inicio->format('Y-m-d') : null;
                    $cliente->fecha_fin = $cliente->membresiaActual->fecha_fin ? $cliente->membresiaActual->fecha_fin->format('Y-m-d') : null;
                    $cliente->ultimo_pago = $cliente->membresiaActual->fecha_inicio->format('d/m/Y');
                } else {
                    $cliente->estado = 'sin_membresia';
                    $cliente->membresia_nombre = 'Sin membresía';
                    $cliente->fecha_inicio = null;
                    $cliente->fecha_fin = null;
                    $cliente->ultimo_pago = 'N/A';
                }
                return $cliente;
            });

        return response()->json($clientes);
    }

    /**
     * Registra un nuevo cliente junto a su membresía activa
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'cedula' => 'required|string|max:20|unique:clientes,cedula',
            'correo' => 'required|email|unique:clientes,correo',
            'telefono' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
            'membresia_id' => 'required|exists:membresias,id',
        ]);

        try {
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('clientes/fotos', 'public');
            }

            $cliente = Cliente::create([
                'nombre_completo' => $request->nombre_completo,
                'cedula' => $request->cedula,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'foto' => $fotoPath,
            ]);

            $membresia = Membresia::findOrFail($request->membresia_id);
            $fechaInicio = Carbon::now()->startOfDay();
            
            $fechaFin = $this->calcularFechaFin($membresia, $fechaInicio);
            $estado = $this->calcularEstadoMembresia($fechaInicio, $fechaFin);

            PagoMembresia::create([
                'cliente_id' => $cliente->id,
                'membresia_id' => $membresia->id,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'monto' => $membresia->precio,
                'estado' => $estado,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
            ]);

            return response()->json(['success' => true, 'cliente' => $cliente]);
        } catch (\Exception $e) {
            Log::error('Error al registrar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error interno al registrar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar cliente específico con su membresía
     */
    public function show($id)
    {
        $cliente = Cliente::with(['membresiaActual.membresia'])->findOrFail($id);
        
        if ($cliente->membresiaActual) {
            $cliente->estado = $this->calcularEstadoMembresia(
                $cliente->membresiaActual->fecha_inicio,
                $cliente->membresiaActual->fecha_fin
            );
            $cliente->fecha_inicio = $cliente->membresiaActual->fecha_inicio ? $cliente->membresiaActual->fecha_inicio->format('Y-m-d') : null;
            $cliente->fecha_fin = $cliente->membresiaActual->fecha_fin ? $cliente->membresiaActual->fecha_fin->format('Y-m-d') : null;
            $cliente->membresia_actual = $cliente->membresiaActual;
        } else {
            $cliente->estado = 'sin membresía';
            $cliente->fecha_inicio = null;
            $cliente->fecha_fin = null;
            $cliente->membresia_actual = null;
        }
        
        return response()->json($cliente);
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'cedula' => 'required|string|max:20|unique:clientes,cedula,' . $id,
            'correo' => 'required|email|unique:clientes,correo,' . $id,
            'telefono' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
            'membresia_id' => 'required|exists:membresias,id',
            'metodo_pago' => 'nullable|string|max:50',
        ]);

        try {
            $cliente = Cliente::findOrFail($id);

            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('clientes/fotos', 'public');
                $cliente->foto = $fotoPath;
            }

            $cliente->update([
                'nombre_completo' => $request->nombre_completo,
                'cedula' => $request->cedula,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
            ]);

            if ($request->membresia_id != $cliente->membresiaActual?->membresia_id) {
                $membresia = Membresia::findOrFail($request->membresia_id);
                $fechaInicio = Carbon::now()->startOfDay();
                $fechaFin = $this->calcularFechaFin($membresia, $fechaInicio);
                $estado = $this->calcularEstadoMembresia($fechaInicio, $fechaFin);

                PagoMembresia::create([
                    'cliente_id' => $cliente->id,
                    'membresia_id' => $membresia->id,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'monto' => $membresia->precio,
                    'estado' => $estado,
                    'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                ]);
            }

            return response()->json(['success' => true, 'cliente' => $cliente]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renovar membresía
     */
    public function renovarMembresia(Request $request, $id)
    {
        $request->validate([
            'membresia_id' => 'required|exists:membresias,id',
            'metodo_pago' => 'nullable|string|max:50',
        ]);

        try {
            $cliente = Cliente::findOrFail($id);
            $membresia = Membresia::findOrFail($request->membresia_id);
            
            $fechaInicio = Carbon::now()->startOfDay();
            $fechaFin = $this->calcularFechaFin($membresia, $fechaInicio);
            $estado = $this->calcularEstadoMembresia($fechaInicio, $fechaFin);

            PagoMembresia::create([
                'cliente_id' => $cliente->id,
                'membresia_id' => $membresia->id,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'monto' => $membresia->precio,
                'estado' => $estado,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
            ]);

            return response()->json(['success' => true, 'message' => 'Membresía renovada correctamente']);
        } catch (\Exception $e) {
            Log::error('Error al renovar membresía: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al renovar la membresía: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar cliente sin borrar pagos (problema 3: prevenir eliminación de registros relacionados)
     */
    public function destroy($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            // No eliminar pagos membresías - solo marca como cliente eliminado
            // Los registros históricos deben mantenerse para auditoría
            $cliente->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error eliminando cliente: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Retorna las membresías disponibles
     */
    public function getMembresias()
    {
        $membresias = Membresia::select('id', 'nombre', 'duracion', 'tipo_duracion', 'precio')->get();
        return response()->json($membresias);
    }

    /**
     * Exportar clientes a PDF
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => 'nullable|string|in:activa,proxima,vencida',
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

            $query = PagoMembresia::with(['cliente', 'membresia'])
                ->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                ->orderBy('cliente_id')
                ->latest('id');

            $pagos = $query->get();

            $clientes = $pagos->map(function ($pago) {
                $cliente = $pago->cliente;
                $cliente->estado = $this->calcularEstadoMembresia(
                    $pago->fecha_inicio,
                    $pago->fecha_fin
                );
                $cliente->membresia_nombre = $pago->membresia->nombre ?? 'N/A';
                $cliente->fecha_inicio = $pago->fecha_inicio->format('d/m/Y');
                $cliente->fecha_fin = $pago->fecha_fin->format('d/m/Y');
                $cliente->monto = $pago->monto ?? 0;
                $cliente->metodo_pago = $pago->metodo_pago ?? 'N/A';
                return $cliente;
            })->unique('id');

            if ($request->estado) {
                $clientes = $clientes->filter(function ($cliente) use ($request) {
                    return $cliente->estado === $request->estado;
                });
            }

            $data = [
                'clientes' => $clientes,
                'fecha_inicio' => $fechaInicio->format('d/m/Y'),
                'fecha_fin' => $fechaFin->format('d/m/Y'),
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'total' => $clientes->count(),
                'logo_url' => asset('images/logo-gym.png'),
            ];

            $pdf = Pdf::loadView('reports.clientes-pdf', $data);
            return $pdf->download('reporte-clientes-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar clientes a Excel (CSV)
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => 'nullable|string|in:activa,proxima,vencida',
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

            $query = PagoMembresia::with(['cliente', 'membresia'])
                ->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                ->orderBy('cliente_id')
                ->latest('id');

            $pagos = $query->get();

            $clientes = $pagos->map(function ($pago) {
                $cliente = $pago->cliente;
                $cliente->estado = $this->calcularEstadoMembresia(
                    $pago->fecha_inicio,
                    $pago->fecha_fin
                );
                $cliente->membresia_nombre = $pago->membresia->nombre ?? 'N/A';
                $cliente->fecha_inicio = $pago->fecha_inicio->format('d/m/Y');
                $cliente->fecha_fin = $pago->fecha_fin->format('d/m/Y');
                $cliente->monto = $pago->monto ?? 0;
                $cliente->metodo_pago = $pago->metodo_pago ?? 'N/A';
                return $cliente;
            })->unique('id');

            if ($request->estado) {
                $clientes = $clientes->filter(function ($cliente) use ($request) {
                    return $cliente->estado === $request->estado;
                });
            }

            $filename = 'reporte-clientes-' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($clientes) {
                $file = fopen('php://output', 'w');
                
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($file, [
                    'N°',
                    'Nombre Completo',
                    'Cédula',
                    'Correo',
                    'Teléfono',
                    'Membresía',
                    'Fecha Inicio',
                    'Fecha Fin',
                    'Monto',
                    'Método de Pago',
                    'Estado',
                    'Fecha Registro'
                ], ';');

                $numero = 1;
                foreach ($clientes as $cliente) {
                    $estadoTexto = match($cliente->estado) {
                        'activa' => 'Activa',
                        'proxima' => 'Próxima a vencer',
                        'vencida' => 'Vencida',
                        default => 'Sin membresía'
                    };

                    fputcsv($file, [
                        $numero++,
                        $cliente->nombre_completo,
                        $cliente->cedula,
                        $cliente->correo,
                        $cliente->telefono ?? 'N/A',
                        $cliente->membresia_nombre,
                        $cliente->fecha_inicio,
                        $cliente->fecha_fin,
                        '$' . number_format($cliente->monto, 2),
                        ucfirst($cliente->metodo_pago),
                        $estadoTexto,
                        $cliente->created_at->format('d/m/Y')
                    ], ';');
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error al generar CSV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcula fecha de fin según tipo de membresía
     */
    private function calcularFechaFin($membresia, $fechaInicio)
    {
        $fechaFin = Carbon::parse($fechaInicio);
        
        if ($membresia->tipo_duracion === 'meses') {
            return $fechaFin->addMonths($membresia->duracion);
        } elseif ($membresia->tipo_duracion === 'dias') {
            return $fechaFin->addDays($membresia->duracion);
        }
        
        return $fechaFin;
    }

    /**
     * Calcula el estado de la membresía
     */
    private function calcularEstadoMembresia($fechaInicio, $fechaFin)
    {
        $hoy = Carbon::now();
        $diasRestantes = $hoy->diffInDays($fechaFin, false);

        if ($diasRestantes < 0) {
            return 'vencida';
        } elseif ($diasRestantes <= 5) {
            return 'proxima';
        }
        return 'activa';
    }
}
