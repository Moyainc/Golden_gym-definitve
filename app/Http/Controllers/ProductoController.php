<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Traits\HasPrecioFormatted;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class ProductoController extends Controller
{
  use HasPrecioFormatted;

  // Listar todos las ventas
  public function listParaVenta()
  {
    $productos = Producto::with('categoria')->orderBy('id')->get();

    $productos = $productos->map(function ($producto) {
      return [
        'id' => $producto->id,
        'nombre' => $producto->nombre,
        'categoria_id' => $producto->categoria_id,
        'categoria' => $producto->categoria,
        'stock' => $producto->stock,
        'precio' => $producto->precio,
        'precio_formatted' => $producto->precioFormatted($producto->precio),
        'estado' => $producto->estado,
      ];
    });

    return response()->json($productos);
  }



  // Listar todos los productos
  public function list(Request $request)
  {
    $perPage = $request->input('per_page', 20); // productos por "página"
    $page = $request->input('page', 1);

    $query = Producto::with('categoria')->orderBy('id');

    $productos = $query->paginate($perPage, ['*'], 'page', $page);

    // Agregar precio formateado y otros campos
    $productos->getCollection()->transform(function ($producto) {
      return [
        'id' => $producto->id,
        'nombre' => $producto->nombre,
        'categoria' => $producto->categoria,
        'categoria_id' => $producto->categoria_id,
        'stock' => $producto->stock,
        'precio' => $producto->precio,
        'precio_formatted' => $producto->precioFormatted($producto->precio),
        'estado' => $producto->estado,
      ];
    });

    return response()->json($productos);
  }



  // Crear un nuevo producto
 public function store(Request $request)
{
    try {

        if ($request->has('precio')) {
            $request->merge([
                'precio' => preg_replace('/\D/', '', $request->precio)
            ]);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:productos,nombre',
            'categoria_id' => 'required|exists:categorias,id',
            'stock' => 'required|integer|min:0',
            'precio' => 'required|integer|min:0',
            'estado' => 'required|in:Activo,Inactivo',
        ], [
            'nombre.unique' => "El producto '{$request->nombre}' ya está registrado.",
        ]);

        $producto = Producto::create($validated);

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'producto' => $producto
        ], 201);

    } catch (ValidationException $e) {

        return response()->json([
            'message' => implode(' ', $e->errors()['nombre'] ?? ['Datos inválidos']),
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error al crear el producto. Intente nuevamente.'
        ], 500);
    }
}


  // Actualizar un producto existente
  public function update(Request $request, Producto $id)
  {
    try {

      if ($request->has('precio')) {
        $request->merge([
          'precio' => preg_replace('/\D/', '', $request->precio)
        ]);
      }

      $validated = $request->validate([
        'nombre' => 'required|string|max:255|unique:productos,nombre,' . $id->id,
        'categoria_id' => 'required|exists:categorias,id',
        'stock' => 'required|integer|min:0',
        'precio' => 'required|integer|min:0',
        'estado' => 'required|in:Activo,Inactivo',
      ]);

      $id->update($validated);

      return response()->json([
        'message' => 'Producto actualizado exitosamente',
        'producto' => $id
      ]);
    } catch (ValidationException $e) {
      return response()->json([
        'message' => implode(' ', $e->errors()['nombre'] ?? ['Datos inválidos']),
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Error al actualizar el producto. Intente nuevamente.'
      ], 500);
    }
  }

  // Eliminar un producto
  public function destroy(Producto $id)
  {
    try {
      $id->delete();
      return response()->json([
        'message' => 'Producto eliminado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Error al eliminar el producto. Intente nuevamente.'
      ], 500);
    }
  }


  public function exportInventarioPdf(Request $request)
  {
    try {
      $inicio = $request->input('inicio');
      $fin = $request->input('fin');

      if (!$inicio || !$fin) {
        return response()->json([
          'success' => false,
          'message' => 'Debe especificar el rango de fechas (inicio y fin).'
        ]);
      }

      // Filtrar productos creados o actualizados dentro del rango
      $productos = Producto::with('categoria')
        ->where(function ($query) use ($inicio, $fin) {
          $query->whereBetween('created_at', [$inicio, $fin])
            ->orWhereBetween('updated_at', [$inicio, $fin]);
        })
        ->orderBy('categoria_id')
        ->orderBy('nombre')
        ->get();

      // Totales globales
      $totalProductos = $productos->count();
      $totalCategorias = $productos->pluck('categoria_id')->unique()->count();

      // Resumen por categoría
      $porCategoria = $productos
        ->groupBy(fn($p) => $p->categoria->nombre ?? 'Sin categoría')
        ->map(fn($items) => [
          'cantidad' => $items->count(),
          'stock_total' => $items->sum('stock')
        ])
        ->sortKeys();

      $data = [
        'productos' => $productos,
        'porCategoria' => $porCategoria,
        'totalProductos' => $totalProductos,
        'totalCategorias' => $totalCategorias,
        'inicio' => $inicio,
        'fin' => $fin,
        'fecha_generacion' => now()->format('d/m/Y H:i'),
      ];

      // Cargar vista PDF (la vista debe manejar cuando no hay productos)
      $pdf = Pdf::loadView('reports.inventario-pdf', $data)
        ->setPaper('a4', 'portrait');

      $filename = 'reporte_inventario-' . now()->format('d-m-y') . '.pdf';
      return $pdf->download($filename);
    } catch (\Throwable $e) {
      Log::error('Error al generar reporte de inventario: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error al generar el PDF: ' . $e->getMessage()
      ]);
    }
  }


  public function exportInventarioExcel(Request $request)
  {
    $request->validate([
      'inicio' => 'required|date',
      'fin' => 'required|date|after_or_equal:inicio',
    ]);

    try {
      $inicio = $request->input('inicio');
      $fin = $request->input('fin');

      // === CONSULTA BASE ===
      $productos = Producto::with('categoria')
        ->where(function ($query) use ($inicio, $fin) {
          $query->whereBetween('created_at', [$inicio, $fin])
            ->orWhereBetween('updated_at', [$inicio, $fin]);
        })
        ->orderBy('categoria_id')
        ->orderBy('nombre')
        ->get();

      // === CALCULOS GLOBALES ===
      $totalProductos = $productos->count();
      $totalCategorias = $productos->pluck('categoria_id')->unique()->count();

      // === RESUMEN POR CATEGORÍA ===
      $porCategoria = $productos
        ->groupBy(fn($p) => $p->categoria->nombre ?? 'Sin categoría')
        ->map(fn($items) => [
          'cantidad' => $items->count(),
          'stock_total' => $items->sum('stock'),
          'precio_promedio' => $items->avg('precio'),
        ])
        ->sortKeys();

      // === CREAR EXCEL ===
      $spreadsheet = new Spreadsheet();

      // --------------------------------------------------
      // 🟡 HOJA 1: LISTADO DE PRODUCTOS
      // --------------------------------------------------
      $sheet1 = $spreadsheet->getActiveSheet();
      $sheet1->setTitle('Productos');

      $sheet1->fromArray([[
        'N°',
        'Producto',
        'Categoría',
        'Stock',
        'Precio ($)',
        'Creado',
        'Actualizado'
      ]]);

      $row = 2;
      $num = 1;

      if (!$productos->isEmpty()) {
        foreach ($productos as $p) {
          $sheet1->fromArray([[
            $num++,
            $p->nombre,
            $p->categoria->nombre ?? 'Sin categoría',
            $p->stock,
            $p->precio, // valor numérico
            Carbon::parse($p->created_at)->format('d/m/Y H:i'),
            Carbon::parse($p->updated_at)->format('d/m/Y H:i'),
          ]], null, "A{$row}");

          // Formato monetario para columna Precio
          $sheet1->getStyle("E{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');

          $row++;
        }
      } else {
        $sheet1->fromArray([['No hay productos en el rango de fechas seleccionado.']], null, "A{$row}");
      }

      $lastRow = $row - 1;

      $sheet1->getStyle("A1:G{$lastRow}")
        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

      $sheet1->getStyle("A1:G1")->getFont()->setBold(true)->getColor()->setRGB('272727');
      $sheet1->getStyle("A1:G1")->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('fcd52b');

      $sheet1->getStyle("A1:G{$lastRow}")
        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

      foreach (range('A', 'G') as $col)
        $sheet1->getColumnDimension($col)->setAutoSize(true);

      // --------------------------------------------------
      // 🟡 HOJA 2: RESUMEN POR CATEGORÍA
      // --------------------------------------------------
      $sheet2 = $spreadsheet->createSheet();
      $sheet2->setTitle('Por Categoría');

      $sheet2->fromArray([[
        'N°',
        'Categoría',
        'Productos (cantidad)',
        'Stock total',
        'Precio promedio ($)'
      ]]);

      $row = 2;
      $num = 1;

      if (!$porCategoria->isEmpty()) {
        foreach ($porCategoria as $categoria => $info) {
          $sheet2->fromArray([[
            $num++,
            $categoria,
            $info['cantidad'],
            $info['stock_total'],
            $info['precio_promedio'], // valor numérico
          ]], null, "A{$row}");

          // Formato monetario para columna Precio promedio
          $sheet2->getStyle("E{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');

          $row++;
        }
      } else {
        $sheet2->fromArray([['No hay categorías en el rango de fechas seleccionado.']], null, "A{$row}");
      }

      $lastRow2 = $row - 1;

      $sheet2->getStyle("A1:E{$lastRow2}")
        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

      $sheet2->getStyle("A1:E1")->getFont()->setBold(true)->getColor()->setRGB('272727');
      $sheet2->getStyle("A1:E1")->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('fcd52b');

      $sheet2->getStyle("A1:E{$lastRow2}")
        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

      foreach (range('A', 'E') as $col)
        $sheet2->getColumnDimension($col)->setAutoSize(true);

      // === DESCARGAR ===
      $filename = 'reporte-inventario-' . date('Y-m-d') . '.xlsx';
      $writer = new Xlsx($spreadsheet);

      return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
      }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      ]);
    } catch (\Exception $e) {
      Log::error('Error al generar Excel Inventario: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error al generar el archivo: ' . $e->getMessage()
      ], 500);
    }
  }
}
