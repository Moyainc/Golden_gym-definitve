<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoDiario extends Model
{
    use HasFactory;

    protected $table = 'pagos_diarios';

    protected $fillable = [
        'nombre',
        'monto',
        'fecha_pago',
        'metodo_pago',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
    ];
}
