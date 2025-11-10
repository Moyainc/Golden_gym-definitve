<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    // Guarda el estado con primera letra en mayúscula
    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = ucfirst(strtolower($value));
    }


    /* Scopes */

    // Scope local (por si algún día quieres incluir anuladas)
    public function scopeActivas($query)
    {
        return $query->where('estado', '!=', 'Anulada');
    }

    protected static function booted(): void
    {
        // Este filtro se aplica a TODAS las consultas de Venta
        static::addGlobalScope('sinAnuladas', function (Builder $builder) {
            $builder->where('estado', '!=', 'Anulada');
        });
    }

    // Recalcula el total de los detalles
    public function actualizarTotal()
    {
        $this->total = $this->detalles()->sum('subtotal');
        $this->save();
    }
}
