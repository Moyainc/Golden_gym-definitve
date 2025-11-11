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
        'monto' => 'decimal:0',
    ];

    // Valores permitidos
    public const METODOS_PAGO = ['Efectivo', 'Transferencia'];
    public const ESTADOS = ['Activo', 'Vencida', 'Próxima', 'Anulada'];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function membresia()
    {
        return $this->belongsTo(Membresia::class);
    }

    // Mutator para validar metodo_pago
    public function setMetodoPagoAttribute($value)
    {
        if (!in_array($value, self::METODOS_PAGO)) {
            throw new \InvalidArgumentException("Método de pago inválido.");
        }
        $this->attributes['metodo_pago'] = $value;
    }

    // Mutator para validar estado
    public function setEstadoAttribute($value)
    {
        if (!in_array($value, self::ESTADOS)) {
            throw new \InvalidArgumentException("Estado inválido.");
        }
        $this->attributes['estado'] = $value;
    }
}
