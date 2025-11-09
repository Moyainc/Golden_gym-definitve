<?php

namespace App\Http\Controllers;

use App\Models\Membresia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembresiaController extends Controller
{
  public function list()
  {
    $membresias = Membresia::all();

    $totalGeneral = $membresias->sum('precio');
    $totalActivas = $membresias->where('activo', 'activa')->count();
    $totalDiarias = $membresias->where('duracion_meses', 0)->sum('precio'); // Asumiendo duracion 0 = diaria
    $totalMembresias = $membresias->where('duracion_meses', '>', 0)->sum('precio');

    return response()->json([
      'membresias' => $membresias,
      'metrics' => [
        'total_general' => $totalGeneral,
        'activas' => $totalActivas,
        'diarias' => $totalDiarias,
        'membresias' => $totalMembresias,
      ],
    ]);
  }
  public function index()
  {
    try {
      $membresias = Membresia::orderByDesc('id')->get();
      return response()->json($membresias);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function edit($id)
  {
    try {
      $membresia = Membresia::findOrFail($id);
      return response()->json($membresia);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function store(Request $request)
  {
    $request->validate([
      'nombre' => 'required|string|max:100|unique:membresias,nombre',
      'duracion' => 'required|integer|min:1',
      'tipo_duracion' => 'required|in:dias,meses',
      'precio' => 'required|numeric|min:0',
    ]);

    $membresia = Membresia::create([
      'nombre' => $request->nombre,
      'duracion' => $request->duracion,
      'tipo_duracion' => $request->tipo_duracion,
      'precio' => $request->precio,
    ]);

    return response()->json([
      'success' => true,
      'membresia' => $membresia,
    ]);
  }

  public function update(Request $request, $id)
  {
    try {
      $membresia = Membresia::findOrFail($id);

      $request->validate([
        'nombre' => 'required|string|max:100|unique:membresias,nombre,' . $membresia->id,
        'duracion' => 'required|integer|min:1',
        'tipo_duracion' => 'required|in:dias,meses',
        'precio' => 'required|numeric|min:0',
      ]);

      $membresia->update([
        'nombre' => $request->nombre,
        'duracion' => $request->duracion,
        'tipo_duracion' => $request->tipo_duracion,
        'precio' => $request->precio,
      ]);

      return response()->json([
        'success' => true,
        'membresia' => $membresia,
      ]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $membresia = Membresia::findOrFail($id);
      $membresia->delete();

      return response()->json(['success' => true]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
  }

  public function estadisticas()
  {
    // Total de membresías
    $totalMembresias = \App\Models\Membresia::count();

    // Clientes activos: contarlos desde la tabla clientes (que tengan al menos un pago activo)
    $totalClientesActivos = \App\Models\Cliente::whereHas('pagosMembresias', function ($q) {
      $q->where('estado', 'activa');
    })->count();

    // Ingresos totales por membresías (suma de monto en pagos_membresias)
    $ingresosMembresias = \App\Models\PagoMembresia::sum(DB::raw('COALESCE(monto,0)'));
    $ingresosMembresias = (float) $ingresosMembresias;

    // Datos de membresías (nombre y precio) para el gráfico de precios
    $membresias = \App\Models\Membresia::select('id', 'nombre', 'precio')->get();

    // Popularidad: contar pagos por membresia_id y luego mapear sobre todas las membresías
    $counts = \App\Models\PagoMembresia::select('membresia_id', DB::raw('COUNT(*) as total'))
      ->groupBy('membresia_id')
      ->pluck('total', 'membresia_id') // retorna [membresia_id => total]
      ->toArray();

    $popularidad = $membresias->map(function ($m) use ($counts) {
      $total = isset($counts[$m->id]) ? (int) $counts[$m->id] : 0;
      return [
        'nombre' => $m->nombre,
        'total' => $total,
      ];
    })->values()->all();

    // Determinar la membresía más popular (por total)
    $membresiaMasPopular = null;
    if (!empty($popularidad)) {
      usort($popularidad, function ($a, $b) {
        return $b['total'] <=> $a['total'];
      });
      $top = $popularidad[0];
      $membresiaMasPopular = [
        'nombre' => $top['nombre'],
        'total' => (int) $top['total'],
      ];
    } else {
      $membresiaMasPopular = ['nombre' => null, 'total' => 0];
    }

    // Precios: devolver nombre y precio (asegurando tipo numérico)
    $precios = $membresias->map(function ($m) {
      return [
        'nombre' => $m->nombre,
        'precio' => (float) $m->precio,
      ];
    })->values()->all();

    return response()->json([
      'resumen' => [
        'total_membresias' => (int) $totalMembresias,
        'clientes_activos' => (int) $totalClientesActivos,
        'ingresos_membresias' => $ingresosMembresias,
        'membresia_mas_popular' => $membresiaMasPopular,
      ],
      'precios' => $precios,
      'popularidad' => $popularidad,
    ]);
  }
}
