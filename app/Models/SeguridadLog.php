<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguridadLog extends Model
{
    protected $fillable = [
        'usuario_id',
        'correo_intentado',
        'exitoso',
        'ip',
        'dispositivo',
        'ubicacion',
        'fecha_intento',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
