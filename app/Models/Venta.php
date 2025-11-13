<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPrecioFormatted;

class Venta extends Model
{
    use HasFactory, HasPrecioFormatted;

    protected $table = 'ventas';

    protected $fillable = [
        'usuario_id',
        'cliente_id',
        'total',
        'metodo_pago',
        'estado',
        'descripcion',
        'fecha_venta'
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'total' => 'decimal:0',
    ];

    protected $appends = ['precio_formatted'];

    // Valores válidos para estado y método de pago
    const ESTADOS_VALIDOS = ['Completada', 'Anulada', 'Pendiente'];
    const METODOS_VALIDOS = ['Efectivo', 'Transferencia'];

    /* Relaciones */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    /* Mutators */

    // Valida el estado antes de guardar
    public function setEstadoAttribute($value)
    {
        $estado = ucfirst(strtolower($value));

        if (!in_array($estado, self::ESTADOS_VALIDOS)) {
            $estado = 'Pendiente'; // Valor por defecto
        }

        $this->attributes['estado'] = $estado;
    }

    // Valida el método de pago antes de guardar
    public function setMetodoPagoAttribute($value)
    {
        $metodo = ucfirst(strtolower($value));

        if (!in_array($metodo, self::METODOS_VALIDOS)) {
            $metodo = 'Efectivo'; // Valor por defecto
        }

        $this->attributes['metodo_pago'] = $metodo;
    }

    /* Scopes */

    // Scope: solo las activas (no anuladas)
    public function scopeActivas($query)
    {
        return $query->where('estado', '!=', 'Anulada');
    }

    /* Eventos */
    protected static function booted(): void
    {
        static::creating(function ($venta) {
            if (empty($venta->estado)) {
                $venta->estado = 'Pendiente';
            }

            if (empty($venta->metodo_pago)) {
                $venta->metodo_pago = 'Efectivo';
            }
        });
    }

    /* Métodos adicionales */

    // Recalcula el total con base en los detalles
    public function actualizarTotal()
    {
        $this->total = $this->detalles()->sum('subtotal');
        $this->save();
    }

    public function comprobante()
    {
        return $this->hasOne(Comprobante::class, 'id_ventas');
    }
}
