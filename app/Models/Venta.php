<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPrecioFormatted;

class Venta extends Model
{
    use HasPrecioFormatted;
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'usuario_id',
        'cliente_membresia_id',
        'total',
        'metodo_pago',
        'descripcion',
        'fecha_venta',
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

    // public function clienteMembresia()
    // {
    //     return $this->belongsTo(ClienteMembresia::class, 'cliente_membresia_id');
    // }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }
}
