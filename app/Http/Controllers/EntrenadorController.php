<?php

namespace App\Http\Controllers;

use App\Models\Entrenador;
use Illuminate\Http\Request;

class EntrenadorController extends Controller
{

    public function index()
    {
        $entrenadores = Entrenador::orderBy('id')->get();
        $total = Entrenador::count();

        return view('admin/edit-index', compact('entrenadores', 'total'));
    }

    public function store(Request $request)
    {
        if (Entrenador::count() >= 6) {
            return back()->with('error', 'Máximo permitido: 6 entrenadores');
        }

        $request->validate([
            'nombre' => 'required|max:150',
            'descripcion' => 'required|max:500',
            'imagen' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['nombre', 'descripcion']);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('entrenadores', 'public');
        }

        Entrenador::create($data);

        return back()->with('success', 'Entrenador agregado correctamente.');
    }

    public function update(Request $request, Entrenador $entrenador)
    {
        $request->validate([
            'nombre' => 'required|max:150',
            'descripcion' => 'required|max:500',
            'imagen' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['nombre', 'descripcion']);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('entrenadores', 'public');
        }

        $entrenador->update($data);

        return back()->with('success', 'Entrenador actualizado correctamente.');
    }

    public function destroy(Entrenador $entrenador)
    {
        $entrenador->delete();

        return back()->with('success', 'Entrenador eliminado.');
    }

    public function toggleVisibility(Entrenador $entrenador)
    {
        $entrenador->update([
            'visible' => !$entrenador->visible
        ]);

        return back()->with('success', 'Visibilidad actualizada.');
    }
}
