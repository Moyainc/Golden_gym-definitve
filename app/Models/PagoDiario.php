<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoDiario extends Model
{
    use HasFactory;

    protected $table = 'pagos_diarios';

    protected $fillable = [
        'nombre_cliente',
        'monto',
        'fecha',
        'metodo_pago',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'monto' => 'decimal:2',
    ];

    public function comprobante()
    {
        return $this->hasOne(Comprobante::class, 'id_pagos_diarios');

    }
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
