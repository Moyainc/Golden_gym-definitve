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
        'usuario_id',
        'fecha_inicio',
        'fecha_fin',
        'monto',
        'estado',
        'metodo_pago',
        'usuario_id',
        'estado_notificado',
        'aviso_proximo',
        'aviso_vencido',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto' => 'decimal:0',
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

    public function usuario()
    {
        // Si el usuario se elimina, el pago se mantiene
        return $this->belongsTo(Usuario::class, 'usuario_id')->withDefault();
    }

    public function comprobante()
    {
        return $this->hasOne(Comprobante::class, 'id_pago_membresia');
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
