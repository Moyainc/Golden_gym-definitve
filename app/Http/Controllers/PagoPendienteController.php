<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PagoDiario;
use App\Models\PagoMembresia;
use App\Models\Venta;
use App\Models\Comprobante;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;



class PagoPendienteController extends Controller
{
  // Mostrar la vista con pagos pendientes

  public function index()
  {
    return view('admin/pagos/confirmacion-pagos');
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
        $campo = 'id_pagos_diarios';
        break;
      case 'membresia':
        $pago = PagoMembresia::find($request->pago_id);
        if (!$pago) return response()->json(['error' => 'Pago de membresía no encontrado.'], 404);
        $campo = 'id_pago_membresia';
        break;
      case 'venta':
        $pago = Venta::find($request->pago_id);
        if (!$pago) return response()->json(['error' => 'Venta no encontrada.'], 404);
        $campo = 'id_ventas';
        break;
    }

    // Guardar archivo
    $filePath = $request->file('comprobante')->store('comprobantes', 'public');

    // Buscar si ya existe un comprobante para este pago
    $comprobanteExistente = Comprobante::where($campo, $request->pago_id)->first();

    if ($comprobanteExistente) {
      // Eliminar la imagen anterior si existe
      if ($comprobanteExistente->foto_comprobante && Storage::disk('public')->exists($comprobanteExistente->foto_comprobante)) {
        Storage::disk('public')->delete($comprobanteExistente->foto_comprobante);
      }

      // Actualizar solo la ruta y el updated_at (automático)
      $comprobanteExistente->update([
        'foto_comprobante' => $filePath,
      ]);

      return response()->json(['message' => 'Comprobante actualizado correctamente.']);
    }

    // Si no existe, crear el registro
    $comprobanteData = [
      'foto_comprobante' => $filePath,
      'id_usuario' => Auth::id(),
      'fecha_subida' => now(),
      $campo => $request->pago_id,
    ];

    Comprobante::create($comprobanteData);

    return response()->json(['message' => 'Comprobante subido correctamente.']);
  }


  // Obtener pagos pendientes en JSON
  public function getPagosPendientesJson()
  {
    $usuarios = Usuario::all()->keyBy('id');

    $pagosDiarios = PagoDiario::where('metodo_pago', 'Transferencia')
      ->where(function ($q) {
        $q->whereDoesntHave('comprobante')
          ->orWhereHas('comprobante', function ($sub) {
            $sub->whereNull('foto_comprobante');
          });
      })
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
          'transaccion' => 'Pago Diario',
          'foto_comprobante' => optional($p->comprobante)->foto_comprobante,
        ];
      });

    $pagosMembresias = PagoMembresia::where('metodo_pago', 'Transferencia')
      ->where(function ($q) {
        $q->whereDoesntHave('comprobante')
          ->orWhereHas('comprobante', function ($sub) {
            $sub->whereNull('foto_comprobante');
          });
      })
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
          'fecha_completa' => $p->fecha_inicio,
          'tipo' => 'membresia',
          'transaccion' => 'Pago Membresía',
          'foto_comprobante' => optional($p->comprobante)->foto_comprobante,
        ];
      });

    $ventas = Venta::where('metodo_pago', 'Transferencia')
      ->where(function ($q) {
        $q->whereDoesntHave('comprobante')
          ->orWhereHas('comprobante', function ($sub) {
            $sub->whereNull('foto_comprobante');
          });
      })
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
          'transaccion' => 'Venta',
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







  public function indexList()
  {
    return view('admin/pagos/lista_pagos');
  }

  public function listar(Request $request)
  {
    try {
      $user = Auth::user();

      // Consulta base de comprobantes
      $query = Comprobante::with([
        'usuario:id,nombre_completo',
        'cliente:id,nombre_completo',
        'venta.usuario:id,nombre_completo',
        'venta.cliente:id,nombre_completo',
        'pagoMembresia.cliente:id,nombre_completo',
        'pagoDiario'
      ])
        ->orderByDesc('created_at');

      // Si no es administrador, limitar a los últimos 10 comprobantes
      if ($user->rol !== 'Administrador') {
        $query->limit(10);
      }

      $comprobantes = $query->get();

      $data = [];

      foreach ($comprobantes as $c) {
        $detalle = null;

        // Detectar tipo según el ID presente
        if ($c->id_ventas) {
          $venta = $c->venta;
          $detalle = [
            'cliente' => $venta->cliente->nombre_completo ?? 'Desconocido',
            'vendedor' => $venta->usuario->nombre_completo ?? 'Desconocido',
            'metodo' => $venta->metodo_pago ?? 'N/A',
            'monto' => $venta->total ?? 0,
            'fecha' => optional($venta->fecha_venta)->format('Y-m-d') ?? $c->fecha_subida,
            'transaccion' => 'Venta',
          ];
        } elseif ($c->id_pago_membresia) {
          $membresia = $c->pagoMembresia;
          $detalle = [
            'cliente' => $membresia->cliente->nombre_completo ?? 'Desconocido',
            'vendedor' => 'Administrador', // No tiene usuario_id
            'metodo' => $membresia->metodo_pago ?? 'N/A',
            'monto' => $membresia->monto ?? 0,
            'fecha' => optional($membresia->fecha_inicio)->format('Y-m-d') ?? $c->fecha_subida,
            'transaccion' => 'Pago Membresía',
          ];
        } elseif ($c->id_pagos_diarios) {
          $pago = $c->pagoDiario;
          $detalle = [
            'cliente' => $pago->nombre_cliente ?? 'Desconocido',
            'vendedor' => $c->usuario->nombre_completo ?? 'Desconocido',
            'metodo' => $pago->metodo_pago ?? 'N/A',
            'monto' => $pago->monto ?? 0,
            'fecha' => optional($pago->fecha)->format('Y-m-d') ?? $c->fecha_subida,
            'transaccion' => 'Pago Diario',
          ];
        }

        if ($detalle) {
          $data[] = [
            'id' => $c->id,
            'cliente' => $detalle['cliente'],
            'vendedor' => $detalle['vendedor'],
            'metodo' => $detalle['metodo'],
            'monto' => $detalle['monto'],
            'fecha' => $detalle['fecha'],
            'transaccion' => $detalle['transaccion'],
            'foto_comprobante' => $c->foto_comprobante,
          ];
        }
      }

      return response()->json($data, 200);
    } catch (\Exception $e) {
      return response()->json([
        'error' => 'Error al cargar los pagos confirmados: ' . $e->getMessage(),
      ], 500);
    }
  }


  public function eliminarComprobante(Request $request)
  {
    try {
      $comprobante = Comprobante::find($request->id);

      if (!$comprobante) {
        return response()->json([
          'error' => 'El comprobante no existe.'
        ], 404);
      }

      // Eliminar archivo físico si existe
      if ($comprobante->foto_comprobante && Storage::exists('public/' . $comprobante->foto_comprobante)) {
        Storage::delete('public/' . $comprobante->foto_comprobante);
      }

      // Actualizar campo en la base de datos
      $comprobante->foto_comprobante = null;
      $comprobante->save();

      return response()->json([
        'message' => 'Comprobante eliminado correctamente.',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'error' => 'Error al eliminar el comprobante: ' . $e->getMessage(),
      ], 500);
    }
  }
}
