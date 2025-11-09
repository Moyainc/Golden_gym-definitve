<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPrecioFormatted;

class PagoGeneral extends Model
{
    use HasFactory, HasPrecioFormatted;

    protected $table = 'pagos_generales';

    protected $fillable = [
        'tipo_pago',
        'origen_id',
        'origen_type',
        'monto',
        'metodo_pago',
        'usuario_id',
        'fecha_pago',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:1',
    ];

    // Relaciones opcionales
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    public function origen()
    {
        return $this->morphTo(null, 'origen_type', 'origen_id');
    }
}
