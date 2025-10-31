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
        'foto',
    ];

    public function pagosMembresias()
    {
        return $this->hasMany(PagoMembresia::class);
    }
    public function membresiaActual()
    {
        return $this->hasOne(PagoMembresia::class)
            ->latest('fecha_inicio')
            ->with('membresia');
    }
}

