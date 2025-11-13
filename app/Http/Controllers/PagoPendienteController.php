<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PagoDiario;
use App\Models\PagoMembresia;
use App\Models\Venta;
use App\Models\Comprobante;
use App\Models\Usuario;
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
    // Validaciones del request
    $request->validate([
      'pago_id' => 'required|integer',
      'tipo' => 'required|in:diario,membresia,venta',
      'comprobante' => 'required|image|max:2048',
    ], [
      'pago_id.required' => 'No se encontró el pago a subir.',
      'comprobante.required' => 'Debes seleccionar un archivo de imagen.',
      'comprobante.image' => 'El archivo debe ser una imagen válida.',
      'comprobante.max' => 'El archivo no debe superar los 2 MB.',
    ]);

    // Verificar que el pago exista según tipo
    switch ($request->tipo) {
      case 'diario':
        $pago = PagoDiario::find($request->pago_id);
        if (!$pago) return response()->json(['error' => 'Pago diario no encontrado.'], 404);
        break;
      case 'membresia':
        $pago = PagoMembresia::find($request->pago_id);
        if (!$pago) return response()->json(['error' => 'Pago de membresía no encontrado.'], 404);
        break;
      case 'venta':
        $pago = Venta::find($request->pago_id);
        if (!$pago) return response()->json(['error' => 'Venta no encontrada.'], 404);
        break;
    }

    // Guardar archivo
    $filePath = $request->file('comprobante')->store('comprobantes', 'public');

    // Crear registro de comprobante
    $comprobanteData = [
      'foto_comprobante' => $filePath,
      'id_usuario' => Auth::id(),
      'fecha_subida' => now(),
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

  // Eliminar comprobante
  public function eliminarComprobante(Request $request)
  {
    $request->validate([
      'pago_id' => 'required|integer',
      'tipo' => 'required|in:diario,membresia,venta',
    ], [
      'pago_id.required' => 'No se especificó el pago a eliminar.',
      'tipo.required' => 'El tipo de pago es obligatorio.',
      'tipo.in' => 'El tipo de pago debe ser diario, membresia o venta.',
    ]);

    $comprobante = Comprobante::where(function ($q) use ($request) {
      switch ($request->tipo) {
        case 'diario':
          $q->where('id_pagos_diarios', $request->pago_id);
          break;
        case 'membresia':
          $q->where('id_pago_membresia', $request->pago_id);
          break;
        case 'venta':
          $q->where('id_ventas', $request->pago_id);
          break;
      }
    })->first();

    if (!$comprobante) {
      return response()->json(['error' => 'No se encontró el comprobante a eliminar.'], 404);
    }

    $comprobante->delete();

    return response()->json(['message' => 'Comprobante eliminado correctamente.']);
  }

  // Obtener pagos pendientes en JSON
  public function getPagosPendientesJson()
  {
    $usuarios = Usuario::all()->keyBy('id');

    $pagosDiarios = PagoDiario::where('metodo_pago', 'Transferencia')
      ->whereDoesntHave('comprobante')
      ->get()
      ->map(function ($p) {
        return [
          'id' => $p->id,
          'cliente' => $p->nombre_cliente ?? 'Desconocido',
          'vendedor' => 'Desconocido',
          'monto' => number_format($p->monto, 0, ',', '.'),
          'metodo' => $p->metodo_pago,
          'fecha' => $p->fecha->format('Y-m-d'),
          'fecha_completa' => $p->fecha,
          'tipo' => 'diario',
          'foto_comprobante' => optional($p->comprobante)->foto_comprobante,
        ];
      });

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
          'fecha_completa' => $p->fecha,
          'tipo' => 'membresia',
          'foto_comprobante' => optional($p->comprobante)->foto_comprobante,
        ];
      });

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
          'fecha_completa' => $v->fecha_venta,
          'tipo' => 'venta',
          'foto_comprobante' => optional($v->comprobante)->foto_comprobante,
        ];
      });

    $pagosPendientes = $pagosDiarios
      ->concat($pagosMembresias)
      ->concat($ventas)
      ->sortByDesc('fecha_completa')
      ->values()
      ->map(function ($p) {
        unset($p['fecha_completa']);
        return $p;
      });

    return response()->json($pagosPendientes);
  }
}
