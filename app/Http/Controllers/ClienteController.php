<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\PagoMembresia;
use App\Models\PagoDiario;
use App\Models\PagoGeneral;
use App\Mail\BienvenidaCliente;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Retorna el listado completo de clientes con su membresía actual
     */
    public function getClientes(Request $request)
    {
        $page = (int) $request->query('page', 1) ?: 1;
        $perPage = (int) request()->query('per_page', 10) ?: 10;
        $skip = ($page - 1) * $perPage;

        $clientes = Cliente::with(['membresiaActual.membresia'])
            ->orderByDesc('id')
            ->skip($skip)
            ->take($perPage)
            ->get()
            ->map(function ($cliente) {
                $pagoActual = $cliente->membresiaActual;

                if ($pagoActual) {
                    // Si está anulado, lo mostramos explícitamente
                    if ($pagoActual->estado === 'anulado') {
                        $cliente->estado = 'anulado';
                        $cliente->membresia_nombre = optional($pagoActual->membresia)->nombre ?? 'N/A';
                        $cliente->fecha_inicio = null;
                        $cliente->fecha_fin = null;
                        $cliente->ultimo_pago = 'N/A';
                    } else {
                        // Si no está anulado, comportamiento normal
                        $cliente->estado = $this->calcularEstadoMembresia(
                            $pagoActual->fecha_inicio,
                            $pagoActual->fecha_fin
                        );
                        $cliente->membresia_nombre = optional($pagoActual->membresia)->nombre ?? 'N/A';
                        $cliente->fecha_inicio = optional($pagoActual->fecha_inicio)->format('Y-m-d');
                        $cliente->fecha_fin = optional($pagoActual->fecha_fin)->format('Y-m-d');
                        $cliente->ultimo_pago = $pagoActual->fecha_inicio
                            ? $pagoActual->fecha_inicio->format('d/m/Y')
                            : 'N/A';
                    }
                } else {
                    $cliente->estado = 'sin_membresia';
                    $cliente->membresia_nombre = 'Sin membresía';
                    $cliente->fecha_inicio = null;
                    $cliente->fecha_fin = null;
                    $cliente->ultimo_pago = 'N/A';
                }

                return $cliente;
            });


        $total = Cliente::count();
        $totalPages = ceil($total / $perPage);

        return response()->json([
            'data' => $clientes,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages
            ]
        ]);
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
            'eps' => 'nullable|string|max:100',
            'numero_emergencia' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
            'membresia_id' => 'required|exists:membresias,id',
            'usuario_id' => 'required|exists:usuarios,id'
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
                'eps' => $request->eps,
                'numero_emergencia' => $request->numero_emergencia,
                'foto' => $fotoPath,
            ]);

            $membresia = Membresia::findOrFail($request->membresia_id);
            $fechaInicio = Carbon::now()->startOfDay();
            $fechaFin = $this->calcularFechaFin($membresia, $fechaInicio);
            $estado = $this->calcularEstadoMembresia($fechaInicio, $fechaFin);

            $pagoMembresia =     PagoMembresia::create([
                'cliente_id' => $cliente->id,
                'membresia_id' => $membresia->id,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'monto' => $membresia->precio,
                'estado' => $estado,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                'usuario_id' => $request->usuario_id ?? null,
            ]);
            Mail::to($cliente->correo)->send(new BienvenidaCliente($cliente, $pagoMembresia));
            // Registrar en pagos generales usando morph
            PagoGeneral::create([
                'origen_id' => $pagoMembresia->id,            // apunta al pago de membresía
                'origen_type' => PagoMembresia::class,        // indica la clase para morph
                'tipo_pago' => 'pagos_membresias',
                'monto' => $membresia->precio,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                'usuario_id' => $request->usuario_id ?? null,
                'fecha_pago' => Carbon::now(),
            ]);

            return response()->json(['success' => true, 'cliente' => $cliente]);
        } catch (\Exception $e) {
            Log::error('Error al registrar cliente: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno al registrar el cliente.'], 500);
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
            'eps' => 'nullable|string|max:100',
            'numero_emergencia' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
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
                'eps' => $request->eps,
                'numero_emergencia' => $request->numero_emergencia,
            ]);


            return response()->json(['success' => true, 'cliente' => $cliente]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar cliente: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar el cliente.'], 500);
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

            // Crear pago de membresía
            $pagoMembresia = PagoMembresia::create([
                'cliente_id' => $cliente->id,
                'membresia_id' => $membresia->id,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'monto' => $membresia->precio,
                'estado' => $estado,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                'usuario_id' => Auth::user()?->id,
            ]);

            // Registrar también en pagos generales
            PagoGeneral::create([
                'origen_id' => $pagoMembresia->id,
                'origen_type' => PagoMembresia::class,
                'tipo_pago' => 'pagos_membresias',
                'monto' => $membresia->precio,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                'usuario_id' => Auth::user()?->id,
                'fecha_pago' => Carbon::now(),
                'estado' => 'Completada',
            ]);

            Mail::to($cliente->correo)->send(new \App\Mail\RenovacionMembresia($cliente, $pagoMembresia));

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
            'estado' => 'nullable|string|in:activa,proxima,vencida,anulado,sin_membresia',
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

            // === OBTENER CLIENTES REGISTRADOS EN EL RANGO ===
            $clientesRegistrados = Cliente::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->with(['pagosMembresias.membresia'])
                ->orderBy('id', 'desc')
                ->get();

            // === VALIDAR SI HAY CLIENTES ===
            if ($clientesRegistrados->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron clientes registrados en el rango de fechas seleccionado.'
                ], 404);
            }

            // === AGREGAR DETALLES DE MEMBRESÍAS ===
            $clientes = $clientesRegistrados->map(function ($cliente) {
                $ultimoPago = $cliente->pagosMembresias()
                    ->with('membresia')
                    ->latest('fecha_inicio')
                    ->first();

                if ($ultimoPago) {
                    if ($ultimoPago->estado === 'anulado') {
                        $cliente->estado = 'anulado';
                        $cliente->membresia_nombre = 'Sin Membresía';
                        $cliente->fecha_inicio = 'N/A';
                        $cliente->fecha_fin = 'N/A';
                    } else {
                        $cliente->estado = $this->calcularEstadoMembresia(
                            $ultimoPago->fecha_inicio,
                            $ultimoPago->fecha_fin
                        );
                        $cliente->membresia_nombre = $ultimoPago->membresia->nombre ?? 'N/A';
                        $cliente->fecha_inicio = $ultimoPago->fecha_inicio->format('d/m/Y');
                        $cliente->fecha_fin = $ultimoPago->fecha_fin->format('d/m/Y');
                    }
                } else {
                    $cliente->membresia_nombre = 'Sin Membresía';
                    $cliente->fecha_inicio = 'N/A';
                    $cliente->fecha_fin = 'N/A';
                    $cliente->estado = 'sin_membresia';
                }

                // ✅ Fecha de registro
                $cliente->fecha_registro = $cliente->created_at
                    ? $cliente->created_at->format('d/m/Y')
                    : 'N/A';

                return $cliente;
            });

            // === FILTRAR POR ESTADO SI SE INDICA ===
            if ($request->estado) {
                $clientes = $clientes->filter(fn($cliente) => $cliente->estado === $request->estado);
            }

            // === PAGOS DIARIOS ===
            $pagosDiarios = collect();
            if ($request->has('incluir_pagos_diarios') && $request->incluir_pagos_diarios == 1) {
                $pagosDiarios = PagoDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->orderBy('fecha', 'desc')
                    ->get();
            }

            // === CONTADORES ===
            $total_clientes = $clientes->count();
            $total_pagos_diarios = $pagosDiarios->count();
            $total_registros = $total_clientes + $total_pagos_diarios;

            // === DATOS PARA LA VISTA ===
            $data = [
                'clientes' => $clientes,
                'pagos_diarios' => $pagosDiarios,
                'fecha_inicio' => $fechaInicio->format('d/m/Y'),
                'fecha_fin' => $fechaFin->format('d/m/Y'),
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'total_clientes' => $total_clientes,
                'total_pagos_diarios' => $total_pagos_diarios,
                'total_registros' => $total_registros,
                'logo_url' => asset('images/logo-gym.png'),
            ];

            // === GENERAR PDF ===
            $pdf = Pdf::loadView('reports.clientes-pdf', $data);

            $filename = 'reporte-clientes_' . $fechaInicio->format('Y-m-d') . '_al_' . $fechaFin->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Exportar clientes a Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => 'nullable|string|in:activa,proxima,vencida,anulado,sin_membresia',
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

            // === OBTENER CLIENTES REGISTRADOS EN EL RANGO ===
            $clientesRegistrados = Cliente::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->orderBy('id', 'desc')
                ->get();

            if ($clientesRegistrados->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron clientes registrados en el rango de fechas seleccionado.'
                ], 404);
            }

            // === AGREGAR DETALLES DE MEMBRESÍAS ===
            $clientes = $clientesRegistrados->map(function ($cliente) {
                $ultimoPago = $cliente->pagosMembresias()->with('membresia')->latest('fecha_inicio')->first();

                if ($ultimoPago) {
                    if ($ultimoPago->estado === 'anulado') {
                        $cliente->estado = 'anulado';
                        $cliente->membresia_nombre = 'Sin Membresía';
                        $cliente->fecha_inicio = 'N/A';
                        $cliente->fecha_fin = 'N/A';
                    } else {
                        $cliente->estado = $this->calcularEstadoMembresia($ultimoPago->fecha_inicio, $ultimoPago->fecha_fin);
                        $cliente->membresia_nombre = $ultimoPago->membresia->nombre ?? 'N/A';
                        $cliente->fecha_inicio = $ultimoPago->fecha_inicio->format('d/m/Y');
                        $cliente->fecha_fin = $ultimoPago->fecha_fin->format('d/m/Y');
                    }
                } else {
                    $cliente->membresia_nombre = 'Sin Membresía';
                    $cliente->fecha_inicio = 'N/A';
                    $cliente->fecha_fin = 'N/A';
                    $cliente->estado = 'sin_membresia';
                }

                return $cliente;
            });

            // === FILTRAR POR ESTADO SI SE INDICA ===
            if ($request->estado) {
                $clientes = $clientes->filter(fn($cliente) => $cliente->estado === $request->estado);
            }

            // === PAGOS DIARIOS (MANTENEMOS IGUAL) ===
            $pagosDiarios = PagoDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->orderBy('fecha', 'desc')
                ->get();

            // === CONTADORES ===
            $totalClientes = $clientes->count();
            $totalPagosDiarios = $pagosDiarios->count();

            // === CREAR DOCUMENTO EXCEL ===
            $spreadsheet = new Spreadsheet();

            // ===== HOJA 1: MEMBRESÍAS =====
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Membresías');
            $sheet1->fromArray([
                ['N°', 'Nombre Completo', 'Cédula', 'Correo', 'Teléfono', 'Membresía', 'Fecha Inicio', 'Fecha Fin', 'Estado', 'Fecha Registro']
            ]);

            $row = 2;
            $numero = 1;
            foreach ($clientes as $cliente) {
                $estadoTexto = match ($cliente->estado) {
                    'activa' => 'Activa',
                    'proxima' => 'Próxima a vencer',
                    'vencida' => 'Vencida',
                    'anulado' => 'Anulado',
                    default => 'Sin Membresía'
                };

                $sheet1->fromArray([[
                    $numero++,
                    $cliente->nombre_completo,
                    $cliente->cedula ?? 'N/A',
                    $cliente->correo ?? 'N/A',
                    $cliente->telefono ?? 'N/A',
                    $cliente->membresia_nombre,
                    $cliente->fecha_inicio,
                    $cliente->fecha_fin,
                    $estadoTexto,
                    $cliente->created_at ? $cliente->created_at->format('d/m/Y') : 'N/A'
                ]], null, "A{$row}");
                $row++;
            }

            $lastRow1 = $row - 1;
            $sheet1->getStyle("A1:J{$lastRow1}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet1->getStyle("A1:J1")->getFont()->setBold(true)->getColor()->setRGB('272727');
            $sheet1->getStyle("A1:J1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('fcd52b');
            $sheet1->getStyle("A1:J1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            foreach (range('A', 'J') as $col) $sheet1->getColumnDimension($col)->setAutoSize(true);

            // ===== HOJA 2: PAGOS DIARIOS =====
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('Pagos Diarios');
            $sheet2->fromArray([
                ['N°', 'Nombre Cliente', 'Método de Pago', 'Fecha']
            ]);

            $row = 2;
            $numero = 1;
            foreach ($pagosDiarios as $pago) {
                $sheet2->fromArray([[
                    $numero++,
                    $pago->nombre_cliente ?? 'Sin nombre',
                    ucfirst($pago->metodo_pago ?? 'No especificado'),
                    Carbon::parse($pago->fecha)->format('d/m/Y')
                ]], null, "A{$row}");
                $row++;
            }

            $lastRow2 = $row - 1;
            $sheet2->getStyle("A1:D{$lastRow2}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet2->getStyle("A1:D1")->getFont()->setBold(true)->getColor()->setRGB('272727');
            $sheet2->getStyle("A1:D1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('fcd52b');
            $sheet2->getStyle("A1:D1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            foreach (range('A', 'D') as $col) $sheet2->getColumnDimension($col)->setAutoSize(true);

            // ===== HOJA 3: RESUMEN GENERAL =====
            $sheet3 = $spreadsheet->createSheet();
            $sheet3->setTitle('Resumen General');
            $sheet3->fromArray([
                ['Tipo', 'Cantidad de Registros'],
                ['Clientes con Membresía o Sin Membresía', $totalClientes],
                ['Pagos Diarios', $totalPagosDiarios],
            ]);

            $sheet3->getStyle("A1:B3")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet3->getStyle("A1:B1")->getFont()->setBold(true)->getColor()->setRGB('272727');
            $sheet3->getStyle("A1:B1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('fcd52b');
            $sheet3->getStyle("A1:B3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            foreach (range('A', 'B') as $col) $sheet3->getColumnDimension($col)->setAutoSize(true);

            // === DESCARGAR ARCHIVO ===
            $filename = 'reporte-clientes-' . date('Y-m-d') . '.xlsx';
            $writer = new Xlsx($spreadsheet);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al generar Excel: ' . $e->getMessage());
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

    // ANULAR MEMBRESÍA

    public function anularMembresia(Request $request, $id)
    {
        // Puedes validar permisos aquí si usas política / middleware

        try {
            $cliente = Cliente::findOrFail($id);

            // Obtener el último pago de membresía (por id o created_at)
            $ultimoPago = $cliente->pagosMembresias()->latest('id')->first();

            if (! $ultimoPago) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una membresía para anular.'
                ], 404);
            }

            DB::beginTransaction();

            // Actualizar a "anulado" y limpiar fechas
            $ultimoPago->update([
                'estado' => 'anulado',
                'fecha_inicio' => null,
                'fecha_fin' => null,
                // opcional: 'metodo_pago' => null,
                // opcional: 'membresia_id' => null,
            ]);


            PagoGeneral::create([
                'origen_id' => $ultimoPago->id,
                'origen_type' => PagoMembresia::class,
                'tipo_pago' => 'anulacion_membresia',
                'monto' => 0,
                'metodo_pago' => 'Anulacion',
                'usuario_id' => Auth::user()?->id,
                'fecha_pago' => now(),
                'estado' => 'Anulada',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Membresía anulada correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al anular membresía: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al anular la membresía: ' . $e->getMessage()
            ], 500);
        }
    }
}
