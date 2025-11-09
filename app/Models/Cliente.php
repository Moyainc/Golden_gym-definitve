<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_completo',
        'cedula',
        'correo',
        'telefono',
        'eps',
        'numero_emergencia',
        'foto',
    ];

    public function membresiaActual()
    {
        return $this->hasOne(PagoMembresia::class, 'cliente_id')->latest('created_at');
    }

    public function pagosMembresias()
    {
        return $this->hasMany(PagoMembresia::class);
    }
}
