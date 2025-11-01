<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPrecioFormatted;

class DetalleVenta extends Model
{
    use HasFactory, HasPrecioFormatted;

    protected $table = 'detalle_ventas';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:1',
        'subtotal' => 'decimal:1',
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Hook para actualizar subtotal automáticamente al guardar
    protected static function booted()
    {
        static::saving(function ($detalle) {
            $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
        });

        // Después de crear o actualizar, actualizar total de la venta
        static::saved(function ($detalle) {
            $detalle->venta->actualizarTotal();
        });

        // Después de eliminar, actualizar total de la venta
        static::deleted(function ($detalle) {
            $detalle->venta->actualizarTotal();
        });
    }
}
