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
                    $cliente->ultimo_pago = $cliente->membresiaActual->fecha_inicio->format('d/m/Y');
                } else {
                    $cliente->estado = 'sin_membresia';
                    $cliente->membresia_nombre = 'Sin membresía';
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
            // 📸 Subir foto (si existe)
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('clientes/fotos', 'public');
            }

            // 👤 Crear cliente
            $cliente = Cliente::create([
                'nombre_completo' => $request->nombre_completo,
                'cedula' => $request->cedula,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'foto' => $fotoPath,
            ]);

            // 🔁 Obtener membresía
            $membresia = Membresia::findOrFail($request->membresia_id);
            $fechaInicio = Carbon::now();
            
            $fechaFin = $this->calcularFechaFin($membresia, $fechaInicio);
            $estado = $this->calcularEstadoMembresia($fechaInicio, $fechaFin);

            // 💾 Crear pago de membresía
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
        $cliente->estado = $cliente->membresiaActual
            ? $this->calcularEstadoMembresia($cliente->membresiaActual->fecha_inicio, $cliente->membresiaActual->fecha_fin)
            : 'sin membresía';
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

            // Actualizar foto si se envió una nueva
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

            // Si cambió la membresía, crear nuevo registro de pago
            if ($request->membresia_id != $cliente->membresiaActual?->membresia_id) {
                $membresia = Membresia::findOrFail($request->membresia_id);
                $fechaInicio = Carbon::now();
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
            
            // La fecha de inicio es HOY
            $fechaInicio = Carbon::now();
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
     * Eliminar cliente y sus pagos asociados
     */
    public function destroy($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            $cliente->pagosMembresias()->delete();
            $cliente->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error eliminando cliente: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Retorna las membresías disponibles (para llenar el select)
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
            $fechaFin = Carbon::parse($request->fecha_fin);

            // Obtener clientes filtrados por rango de fechas
            $query = Cliente::with(['membresiaActual.membresia'])
                ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->orderBy('nombre_completo');

            $clientes = $query->get()->map(function ($cliente) {
                if ($cliente->membresiaActual) {
                    $cliente->estado = $this->calcularEstadoMembresia(
                        $cliente->membresiaActual->fecha_inicio,
                        $cliente->membresiaActual->fecha_fin
                    );
                    $cliente->membresia_nombre = $cliente->membresiaActual->membresia->nombre ?? 'N/A';
                    $cliente->fecha_inicio = $cliente->membresiaActual->fecha_inicio->format('d/m/Y');
                    $cliente->fecha_fin = $cliente->membresiaActual->fecha_fin->format('d/m/Y');
                } else {
                    $cliente->estado = 'sin_membresia';
                    $cliente->membresia_nombre = 'Sin membresía';
                    $cliente->fecha_inicio = 'N/A';
                    $cliente->fecha_fin = 'N/A';
                }
                return $cliente;
            });

            // Filtrar por estado si se especificó
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
            $fechaFin = Carbon::parse($request->fecha_fin);

            // Obtener clientes filtrados
            $query = Cliente::with(['membresiaActual.membresia'])
                ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->orderBy('nombre_completo');

            $clientes = $query->get()->map(function ($cliente) {
                if ($cliente->membresiaActual) {
                    $cliente->estado = $this->calcularEstadoMembresia(
                        $cliente->membresiaActual->fecha_inicio,
                        $cliente->membresiaActual->fecha_fin
                    );
                    $cliente->membresia_nombre = $cliente->membresiaActual->membresia->nombre ?? 'N/A';
                    $cliente->fecha_inicio = $cliente->membresiaActual->fecha_inicio->format('d/m/Y');
                    $cliente->fecha_fin = $cliente->membresiaActual->fecha_fin->format('d/m/Y');
                    $cliente->monto = $cliente->membresiaActual->monto ?? 0;
                    $cliente->metodo_pago = $cliente->membresiaActual->metodo_pago ?? 'N/A';
                } else {
                    $cliente->estado = 'sin_membresia';
                    $cliente->membresia_nombre = 'Sin membresía';
                    $cliente->fecha_inicio = 'N/A';
                    $cliente->fecha_fin = 'N/A';
                    $cliente->monto = 0;
                    $cliente->metodo_pago = 'N/A';
                }
                return $cliente;
            });

            // Filtrar por estado si se especificó
            if ($request->estado) {
                $clientes = $clientes->filter(function ($cliente) use ($request) {
                    return $cliente->estado === $request->estado;
                });
            }

            // Crear archivo CSV
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
                
                // BOM para UTF-8 (para que Excel reconozca los acentos)
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Encabezados
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

                // Datos
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
     * Calcula el estado de la membresía según la fecha actual
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
