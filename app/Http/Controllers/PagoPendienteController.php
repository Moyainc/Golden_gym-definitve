<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PagoDiario;
use App\Models\PagoMembresia;
use App\Models\Venta;
use App\Models\Comprobante;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PagoPendienteController extends Controller
{
  // Mostrar la vista con pagos pendientes
  public function index()
  {
    return view('pagos.pendientes');
  }

  // Subir comprobante
  public function subirComprobante(Request $request)
  {
    // Validación básica
    $request->validate([
      'pago_id' => 'required',
      'tipo' => 'required|in:diario,membresia,venta',
      'comprobante' => 'required|image|max:2048',
    ]);

    // Validación de existencia del pago según el tipo
    switch ($request->tipo) {
      case 'diario':
        if (!PagoDiario::find($request->pago_id)) {
          return response()->json(['error' => 'Pago diario no encontrado'], 404);
        }
        break;
      case 'membresia':
        if (!PagoMembresia::find($request->pago_id)) {
          return response()->json(['error' => 'Pago de membresía no encontrado'], 404);
        }
        break;
      case 'venta':
        if (!Venta::find($request->pago_id)) {
          return response()->json(['error' => 'Venta no encontrada'], 404);
        }
        break;
    }

    // Guardar el comprobante
    $filePath = $request->file('comprobante')->store('comprobantes', 'public');

    $comprobanteData = [
      'foto_comprobante' => $filePath,
      'id_usuario' => Auth::id(),
      'estado' => 'Subido',
      'fecha_subida' => Carbon::now(),
    ];

    switch ($request->tipo) {
      case 'diario':
        $comprobanteData['id_pagos_diarios'] = $request->pago_id;
        break;
      case 'membresia':
        $comprobanteData['id_pago_membresia'] = $request->pago_id;
        break;
      case 'venta':
        $comprobanteData['id_ventas'] = $request->pago_id;
        break;
    }

    Comprobante::create($comprobanteData);

    return response()->json(['message' => 'Comprobante subido correctamente.']);
  }



  public function getPagosPendientesJson()
  {
    // Misma lógica de index pero devuelve JSON
    $pagosDiarios = PagoDiario::where('metodo_pago', 'Transferencia')
      ->whereDoesntHave('comprobante')
      ->get()
      ->map(function ($p) {
        return [
          'id' => $p->id,
          'cliente' => $p->nombre_cliente,
          'monto' => number_format($p->monto, 0, ',', '.'),
          'metodo' => $p->metodo_pago,
          'fecha' => $p->fecha->format('Y-m-d'),
          'tipo' => 'diario'
        ];
      });

    $pagosMembresias = PagoMembresia::where('metodo_pago', 'Transferencia')
      ->whereDoesntHave('comprobante')
      ->with('cliente')
      ->get()
      ->map(function ($p) {
        return [
          'id' => $p->id,
          'cliente' => $p->cliente->nombre ?? 'Desconocido',
          'monto' => number_format($p->monto, 0, ',', '.'),
          'metodo' => $p->metodo_pago,
          'fecha' => $p->fecha_inicio->format('Y-m-d'),
          'tipo' => 'membresia'
        ];
      });

    $ventas = Venta::where('metodo_pago', 'Transferencia')
      ->whereDoesntHave('comprobante')
      ->with('cliente')
      ->get()
      ->map(function ($v) {
        return [
          'id' => $v->id,
          'cliente' => $v->cliente->nombre ?? 'Desconocido',
          'monto' => number_format($v->total, 0, ',', '.'),
          'metodo' => $v->metodo_pago,
          'fecha' => $v->fecha_venta->format('Y-m-d'),
          'tipo' => 'venta'
        ];
      });

    $pagosPendientes = $pagosDiarios->concat($pagosMembresias)->concat($ventas)
      ->sortByDesc('fecha')
      ->values();

    return response()->json($pagosPendientes);
  }
}
