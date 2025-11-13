<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comprobante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comprobantes';

    protected $fillable = [
        'foto_comprobante',
        'id_usuario',
        'id_cliente',
        'id_pago_membresia',
        'id_pagos_diarios',
        'id_ventas',
        'fecha_subida',
    ];


    /* Relaciones */

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function pagoMembresia()
    {
        return $this->belongsTo(PagoMembresia::class, 'id_pago_membresia');
    }

    public function pagoDiario()
    {
        return $this->belongsTo(PagoDiario::class, 'id_pagos_diarios');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_ventas');
    }
}
