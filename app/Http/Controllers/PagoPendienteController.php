<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PagoDiario;
use App\Models\PagoMembresia;
use App\Models\Venta;
use App\Models\Comprobante;
use App\Models\Usuario;
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
    $request->validate([
      'pago_id' => 'required',
      'tipo' => 'required|in:diario,membresia,venta',
      'comprobante' => 'required|image|max:2048',
    ]);

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

  // Obtener pagos pendientes en JSON
  public function getPagosPendientesJson()
  {
    // Traer todos los usuarios de una sola vez
    $usuarios = Usuario::all()->keyBy('id');

    // Pagos diarios
    $pagosDiarios = PagoDiario::where('metodo_pago', 'Transferencia')
      ->whereDoesntHave('comprobante')
      ->get()
      ->map(function ($p) {
        return [
          'id' => $p->id,
          'cliente' => $p->nombre_cliente,
          'vendedor' => 'NN',
          'monto' => number_format($p->monto, 0, ',', '.'),
          'metodo' => $p->metodo_pago,
          'fecha' => $p->fecha->format('Y-m-d'),
          'tipo' => 'diario',
          'foto_comprobante' => optional($p->comprobante)->foto_comprobante,
        ];
      });

    // Pagos membresías
    $pagosMembresias = PagoMembresia::where('metodo_pago', 'Transferencia')
      ->whereDoesntHave('comprobante')
      ->with('cliente')
      ->get()
      ->map(function ($p) use ($usuarios) {
        $vendedor = $usuarios->get($p->usuario_id);
        return [
          'id' => $p->id,
          'cliente' => $p->cliente->nombre_completo ?? 'Desconocido',
          'vendedor' => $vendedor->nombre_completo ?? 'Desconocido',
          'monto' => number_format($p->monto, 0, ',', '.'),
          'metodo' => $p->metodo_pago,
          'fecha' => $p->fecha_inicio->format('Y-m-d'),
          'tipo' => 'membresia',
          'foto_comprobante' => optional($p->comprobante)->foto_comprobante,
        ];
      });

    // Ventas
    $ventas = Venta::where('metodo_pago', 'Transferencia')
      ->whereDoesntHave('comprobante')
      ->with('cliente')
      ->get()
      ->map(function ($v) use ($usuarios) {
        $vendedor = $usuarios->get($v->usuario_id);
        return [
          'id' => $v->id,
          'cliente' => $v->cliente->nombre_completo ?? 'Desconocido',
          'vendedor' => $vendedor->nombre_completo ?? 'Desconocido',
          'monto' => number_format($v->total, 0, ',', '.'),
          'metodo' => $v->metodo_pago,
          'fecha' => $v->fecha_venta->format('Y-m-d'),
          'tipo' => 'venta',
          'foto_comprobante' => optional($v->comprobante)->foto_comprobante,
        ];
      });

    $pagosPendientes = $pagosDiarios
      ->concat($pagosMembresias)
      ->concat($ventas)
      ->sortByDesc('fecha')
      ->values();

    return response()->json($pagosPendientes);
  }
}
