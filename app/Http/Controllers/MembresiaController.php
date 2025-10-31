<?php

namespace App\Http\Controllers;

use App\Models\Membresia;
use Illuminate\Http\Request;

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
}
