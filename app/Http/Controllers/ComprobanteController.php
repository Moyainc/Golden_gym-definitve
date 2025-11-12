<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comprobante;
use App\Models\Venta;
use Illuminate\Support\Facades\Storage;

class ComprobanteController extends Controller
{
  // Mostrar la vista con el QR
  public function mostrarQR($idVenta)
  {
    $venta = Venta::findOrFail($idVenta);

    return view('comprobantes.scan', [
      'idVenta' => $venta->id,
      'idCliente' => $venta->cliente_id,
      'idUsuario' => $venta->usuario_id
    ]);
  }

  // Mostrar formulario cuando se escanea el QR
  public function formularioSubida($idVenta)
  {
    $venta = Venta::findOrFail($idVenta);

    return view('comprobantes.scan', [
      'idVenta' => $venta->id,
      'idCliente' => $venta->cliente_id,
      'idUsuario' => $venta->usuario_id
    ]);
  }

  // Guardar comprobante
  public function store(Request $request)
  {
    $request->validate([
      'foto_comprobante' => 'required|image|max:5120', // max 5MB
      'id_venta' => 'required|exists:ventas,id',
      'id_cliente' => 'required|exists:clientes,id',
      'id_usuario' => 'required|exists:usuarios,id'
    ]);

    $path = $request->file('foto_comprobante')->store('comprobantes', 'public');

    $comprobante = Comprobante::create([
      'foto_comprobante' => $path,
      'id_ventas' => $request->id_venta,
      'id_cliente' => $request->id_cliente,
      'id_usuario' => $request->id_usuario,
      'estado' => 'Pendiente',
      'fecha_subida' => now()
    ]);

    return redirect()->back()->with('success', 'Comprobante subido correctamente.');
  }
}
