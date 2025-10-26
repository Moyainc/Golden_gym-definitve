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

  public function store(Request $request)
  {
    $request->validate([
      'nombre' => 'required|string|max:255',
      'precio' => 'required|numeric|min:0',
      'duracion_meses' => 'required|integer|min:0',
      'activo' => 'required|in:Activa,Inactiva',
    ]);

    $membresia = Membresia::create($request->all());

    return response()->json([
      'message' => 'Membresía creada exitosamente',
      'membresia' => $membresia,
    ], 201);
  }

  public function update(Request $request, Membresia $id)
  {
    $request->validate([
      'nombre' => 'required|string|max:255',
      'precio' => 'required|numeric|min:0',
      'duracion_meses' => 'required|integer|min:0',
      'activo' => 'required|in:Activa,inactiva',
    ]);

    $id->update($request->all());

    return response()->json([
      'message' => 'Membresía actualizada exitosamente',
      'membresia' => $id,
    ]);
  }

  public function destroy(Membresia $id)
  {
    $id->delete();

    return response()->json([
      'message' => "Membresía '{$id->nombre}' eliminada exitosamente",
    ]);
  }
}
