<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoMembresia extends Model
{
    use HasFactory;

    protected $table = 'pagos_membresias';

    protected $fillable = [
        'cliente_id',
        'membresia_id',
        'fecha_inicio',
        'fecha_fin',
        'monto',
        'estado',
        'metodo_pago',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto' => 'decimal:2',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function membresia()
    {
        return $this->belongsTo(Membresia::class);
    }
}

