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
        'total' => 'decimal:1',
    ];

    protected $appends = ['precio_formatted'];

    // Relaciones
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

    // Método para actualizar el total automáticamente
    public function actualizarTotal()
    {
        $this->total = $this->detalles()->sum('subtotal');
        $this->save();
    }
}
