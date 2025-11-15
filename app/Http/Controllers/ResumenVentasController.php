<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ResumenVentasController extends Controller
{
  // ✅ Página principal
  public function index()
  {
    $anios = Venta::selectRaw('YEAR(fecha_venta) as anio')
      ->distinct()
      ->orderBy('anio', 'asc')
      ->pluck('anio');

    return view('admin.contabilidad.resumen-ventas', compact('anios'));
  }

  // ✅ Datos JSON seguros
  public function data(Request $request)
  {
    try {
      $request->validate([
        'fecha_inicio' => 'nullable|date',
        'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        'anio' => 'nullable|integer',
        'mes' => 'nullable|integer',
      ]);

      $query = Venta::query()
        ->where('estado', '!=', 'Anulada'); // Excluir ventas anuladas

      if ($request->filled('anio')) {
        $query->whereYear('fecha_venta', $request->anio);
      }

      if ($request->filled('mes')) {
        $query->whereMonth('fecha_venta', $request->mes);
      }

      if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
        $query->whereBetween('fecha_venta', [$request->fecha_inicio, $request->fecha_fin]);
      }

      $ventas = $query->with(['detalles.producto.categoria'])->get();

      // Totales generales
      $totalVentas = round($ventas->sum('total'));
      $numVentas = $ventas->count();
      $numProductos = $ventas->sum(fn($v) => $v->detalles->sum('cantidad'));
      $pendientes = $ventas->where('estado', 'Pendiente')->count();

      // Ventas agrupadas por categoría y producto
      $ventasPorCategoria = [];
      $ventasPorProducto = [];

      foreach ($ventas as $venta) {
        foreach ($venta->detalles as $detalle) {
          $categoria = $detalle->producto->categoria->nombre ?? 'Sin Categoría';
          $producto = $detalle->producto->nombre ?? 'Producto Eliminado';

          $ventasPorCategoria[$categoria] = ($ventasPorCategoria[$categoria] ?? 0) + $detalle->subtotal;
          $ventasPorProducto[$producto] = ($ventasPorProducto[$producto] ?? 0) + $detalle->subtotal;
        }
      }

      $ventasPorCategoria = array_map(fn($v) => round($v), $ventasPorCategoria);
      $ventasPorProducto = array_map(fn($v) => round($v), $ventasPorProducto);

      // Agrupación mensual (solo de ventas no anuladas)
      $ventasPorMes = $ventas
        ->groupBy(fn($v) => Carbon::parse($v->fecha_venta)->format('Y-m'))
        ->map(fn($v) => round($v->sum('total')))
        ->sortKeys();

      return response()->json([
        'success' => true,
        'totalVentas' => $totalVentas,
        'numVentas' => $numVentas,
        'numProductos' => $numProductos,
        'pendientes' => $pendientes,
        'ventasPorCategoria' => $ventasPorCategoria,
        'ventasPorProducto' => $ventasPorProducto,
        'ventasPorMes' => $ventasPorMes,
      ]);
    } catch (\Throwable $e) {
      Log::error('Error ResumenVentasController@data: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Ocurrió un error al obtener los datos de ventas.'
      ], 500);
    }
  }


  // ✅ Últimas ventas JSON seguras
  public function ultimasVentas()
  {
    try {
      $ventas = Venta::with(['detalles.producto.categoria'])
        ->orderByDesc('fecha_venta')
        ->take(5)
        ->get()
        ->map(function ($venta) {
          return [
            'id' => $venta->id,
            'usuario_id' => $venta->usuario_id,
            'cliente_id' => $venta->cliente_id,
            'total' => round($venta->total),
            'metodo_pago' => $venta->metodo_pago,
            'estado' => $venta->estado,
            'fecha_venta' => $venta->fecha_venta ? Carbon::parse($venta->fecha_venta)->format('Y-m-d') : null,
            'detalles' => $venta->detalles->map(fn($d) => [
              'producto' => $d->producto->nombre ?? 'Producto Eliminado',
              'categoria' => $d->producto->categoria->nombre ?? 'Sin Categoría',
              'cantidad' => (int) $d->cantidad,
              'subtotal' => round($d->subtotal),
              'precio_unitario' => round($d->precio_unitario),
            ]),
          ];
        });

      return response()->json([
        'success' => true,
        'ventas' => $ventas,
      ]);
    } catch (\Throwable $e) {
      Log::error('Error ResumenVentasController@ultimasVentas: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Ocurrió un error al obtener las últimas ventas.'
      ], 500);
    }
  }

  // ✅ Export PDF
  public function exportVentasPdf(Request $request)
  {
    try {
      $inicio = $request->input('fecha_inicio');
      $fin = $request->input('fecha_fin');

      // Consulta base con relaciones
      $query = Venta::with(['usuario', 'cliente', 'detalles.producto.categoria'])
        ->orderByDesc('fecha_venta');

      // Aplicar filtro de rango de fechas si se proporcionan
      if ($inicio && $fin) {
        $query->whereBetween('fecha_venta', [$inicio, $fin]);
      }

      $ventas = $query->get();

      // Procesar cada venta para agregar cliente, usuario y subtotal detalle
      foreach ($ventas as $venta) {
        // Cliente o "Venta rápida"
        if (!$venta->cliente) {
          $venta->cliente = (object)[
            'nombre_completo' => 'Venta rápida'
          ];
        }

        // Usuario que realizó la venta
        $venta->usuario_nombre = $venta->usuario->nombre_completo ?? 'N/A';

        // Asegurar subtotal de cada detalle
        foreach ($venta->detalles as $detalle) {
          $detalle->precio_total = $detalle->subtotal ?? ($detalle->cantidad * $detalle->precio_unitario);
        }
      }

      // Totales
      $totalVentas = $ventas->sum('total');
      $numVentas = $ventas->count();
      $numProductos = $ventas->sum(fn($v) => $v->detalles->sum('cantidad'));
      $pendientes = $ventas->where('estado', 'Pendiente')->count();

      // Generar PDF
      $pdf = Pdf::loadView('reports.ventas-pdf', [
        'ventas' => $ventas,
        'totalVentas' => $totalVentas,
        'numVentas' => $numVentas,
        'numProductos' => $numProductos,
        'pendientes' => $pendientes,
        'inicio' => $inicio,
        'fin' => $fin,
        'fecha_generacion' => now()->format('d/m/Y H:i'),
      ]);

      $filename = 'resumen-ventas-' . now()->format('d-m-y') . '.pdf';
      return $pdf->download($filename);
    } catch (\Throwable $e) {
      Log::error('Error ResumenVentasController@exportVentasPdf: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error generando el PDF.');
    }
  }


  // ✅ Export Excel (CSV)
  public function exportVentasExcel(Request $request)
  {
    $request->validate([
      'fecha_inicio' => 'required|date',
      'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
    ], [
      'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
      'fecha_fin.required' => 'La fecha de fin es obligatoria.',
      'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
    ]);

    try {
      $inicio = $request->input('fecha_inicio');
      $fin = $request->input('fecha_fin');

      // 🔹 Cargar datos con relaciones y filtro por rango
      $ventas = Venta::with(['usuario', 'cliente', 'detalles.producto.categoria'])
        ->whereBetween('fecha_venta', [$inicio, $fin])
        ->orderByDesc('fecha_venta')
        ->get();

      // 🔹 Crear Excel
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Ventas');

      // 🔹 Encabezados
      $headers = [
        'N°',
        'ID Venta',
        'Producto',
        'Categoría',
        'Cliente',
        'Usuario',
        'Método de Pago',
        'Cantidad',
        'Precio Unitario',
        'Subtotal',
        'Fecha',
        'Estado'
      ];
      $sheet->fromArray([$headers], null, 'A1');

      // 🔹 Estilos del encabezado
      $sheet->getStyle('A1:L1')->getFont()->setBold(true)->getColor()->setRGB('272727');
      $sheet->getStyle('A1:L1')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('fcd52b');
      $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

      // 🔹 Llenar datos
      $row = 2;
      $numero = 1;

      foreach ($ventas as $venta) {
        foreach ($venta->detalles as $detalle) {
          $producto = $detalle->producto->nombre ?? 'Producto Eliminado';
          $categoria = $detalle->producto->categoria->nombre ?? 'Sin Categoría';
          $cliente = $venta->cliente->nombre_completo ?? 'Venta rápida';
          $usuario = $venta->usuario->nombre_completo ?? 'N/A';
          $fecha = Carbon::parse($venta->fecha_venta)->format('d/m/Y');

          $sheet->fromArray([
            [
              $numero++,
              $venta->id,
              $producto,
              $categoria,
              $cliente,
              $usuario,
              ucfirst($venta->metodo_pago ?? 'No especificado'),
              $detalle->cantidad,
              $detalle->precio_unitario, // valor numérico
              $detalle->subtotal,        // valor numérico
              $fecha,
              $venta->estado,
            ]
          ], null, "A{$row}");

          // 🔹 Aplicar formato monetario a columnas Precio Unitario y Subtotal
          $sheet->getStyle("I{$row}:J{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');

          $row++;
        }
      }

      $lastRow = $row - 1;

      // 🔹 Totales
      $totalVentas = $ventas->sum('total');
      $numProductos = $ventas->sum(fn($v) => $v->detalles->sum('cantidad'));

      $sheet->fromArray([
        ['', '', '', '', '', '', '', '', 'TOTAL VENTAS:', $totalVentas, '', ''],
        ['', '', '', '', '', '', '', '', 'TOTAL PRODUCTOS:', $numProductos, '', ''],
      ], null, "A" . ($lastRow + 2));

      // 🔹 Formato monetario para total de ventas
      $sheet->getStyle("J" . ($lastRow + 2))
        ->getNumberFormat()
        ->setFormatCode('#,##0');

      // 🔹 Estilo general
      $sheet->getStyle("A1:L{$lastRow}")
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

      $sheet->getStyle("A1:L{$lastRow}")
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

      foreach (range('A', 'L') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
      }

      // 🔹 Descargar archivo
      $filename = 'resumen-ventas-' . now()->format('d-m-y') . '.xlsx';
      $writer = new Xlsx($spreadsheet);

      return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
      }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      ]);
    } catch (\Throwable $e) {
      Log::error('Error ResumenVentasController@exportVentasExcel: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error al generar el archivo: ' . $e->getMessage(),
      ], 500);
    }
  }
}
