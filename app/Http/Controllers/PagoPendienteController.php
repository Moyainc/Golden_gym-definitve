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
    $request->validate([
      'pago_id' => 'required|integer',
      'tipo' => 'required|in:diario,membresia,venta',
      'comprobante' => 'required|image|max:2048',
    ]);

    $user = Auth::user();

    switch ($request->tipo) {
      case 'diario':
        $pago = PagoDiario::find($request->pago_id);
        if (!$pago) return response()->json(['error' => 'Pago diario no encontrado.'], 404);
        $campo = 'id_pagos_diarios';

        if ($user->rol !== 'Administrador' && $pago->id_usuario !== $user->id) {
          return response()->json(['error' => 'No tienes permiso para subir el comprobante de este pago.'], 403);
        }
        break;

      case 'membresia':
        $pago = PagoMembresia::find($request->pago_id);
        if (!$pago) return response()->json(['error' => 'Pago de membresía no encontrado.'], 404);
        $campo = 'id_pago_membresia';

        if ($user->rol !== 'Administrador' && $pago->usuario_id !== $user->id) {
          return response()->json(['error' => 'No tienes permiso para subir el comprobante de esta membresía.'], 403);
        }
        break;

      case 'venta':
        $pago = Venta::find($request->pago_id);
        if (!$pago) return response()->json(['error' => 'Venta no encontrada.'], 404);
        $campo = 'id_ventas';

        if ($user->rol !== 'Administrador' && $pago->usuario_id !== $user->id) {
          return response()->json(['error' => 'No tienes permiso para subir el comprobante de esta venta.'], 403);
        }
        break;
    }

    // Guardar archivo
    $filePath = $request->file('comprobante')->store('comprobantes', 'public');

    // Buscar comprobante existente
    $comprobanteExistente = Comprobante::where($campo, $request->pago_id)->first();

    if ($comprobanteExistente) {
      if ($comprobanteExistente->foto_comprobante && Storage::disk('public')->exists($comprobanteExistente->foto_comprobante)) {
        Storage::disk('public')->delete($comprobanteExistente->foto_comprobante);
      }
      $comprobanteExistente->update([
        'foto_comprobante' => $filePath,
      ]);

      if ($request->tipo === 'venta') {
        $pago->estado = 'Completada';
        $pago->save();
      }

      return response()->json(['message' => 'Comprobante actualizado correctamente.']);
    }

    // Crear nuevo comprobante
    Comprobante::create([
      'foto_comprobante' => $filePath,
      'id_usuario' => $user->id,
      'fecha_subida' => now(),
      $campo => $request->pago_id,
    ]);

    if ($request->tipo === 'venta') {
      $pago->estado = 'Completada';
      $pago->save();
    }


    return response()->json(['message' => 'Comprobante subido correctamente.']);
  }



  // Obtener pagos pendientes en JSON
  public function getPagosPendientesJson()
  {
    $usuarios = Usuario::all()->keyBy('id');

    // Pagos diarios pendientes
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
          'fecha' => $p->created_at->format('Y-m-d'),
          'fecha_completa' => $p->created_at,
          'tipo' => 'diario',
          'transaccion' => 'Pago Diario',
          'foto_comprobante' => optional($p->comprobante)->foto_comprobante,
        ];
      });

    // Pagos de membresía pendientes
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
          'fecha' => $p->created_at->format('Y-m-d'),
          'fecha_completa' => $p->created_at,
          'tipo' => 'membresia',
          'transaccion' => 'Pago Membresía',
          'foto_comprobante' => optional($p->comprobante)->foto_comprobante,
        ];
      });

    // Ventas pendientes
    $ventas = Venta::where('metodo_pago', 'Transferencia')
      ->where('estado', '!=', 'Anulada')
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
          'cliente' => $v->cliente->nombre_completo ?? 'Venta Rápida',
          'vendedor' => $vendedor->nombre_completo ?? 'Desconocido',
          'monto' => number_format($v->total, 0, ',', '.'),
          'metodo' => $v->metodo_pago,
          'fecha' => $v->created_at->format('Y-m-d'),
          'fecha_completa' => $v->created_at,
          'tipo' => 'venta',
          'transaccion' => 'Venta',
          'foto_comprobante' => optional($v->comprobante)->foto_comprobante,
        ];
      });

    // Combinar todas las transacciones y ordenar por created_at descendente
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
      if (!in_array($user->rol, ['Administrador', 'Contador'])) {
        $query->limit(10);
      }

      $comprobantes = $query->get();

      $data = [];

      foreach ($comprobantes as $c) {
        $detalle = null;

        // Detectar tipo según el ID presente
        if ($c->id_ventas) {
          $venta = $c->venta;

          if ($venta && $venta->estado === 'Anulada') {
            continue;
          }

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


      $usuario = Auth::user();

      // Validar que sea el mismo usuario que subió o sea Administrador
      if ($comprobante->id_usuario !== $usuario->id && $usuario->rol !== 'Administrador') {
        return response()->json([
          'error' => 'No tienes permisos suficientes para eliminar este comprobante.'
        ], 403);
      }

      // Eliminar archivo físico si existe
      if ($comprobante->foto_comprobante && Storage::exists('public/' . $comprobante->foto_comprobante)) {
        Storage::delete('public/' . $comprobante->foto_comprobante);
      }

      $idVenta = $comprobante->id_ventas;

      // Limpiar campo en BD
      $comprobante->foto_comprobante = null;
      $comprobante->save();

      if ($idVenta) {
        $venta = Venta::find($idVenta);
        if ($venta) {
          $venta->estado = 'Pendiente';
          $venta->save();
        }
      }

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
