<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre_completo',
        'correo',
        'cedula',
        'telefono',
        'foto',
        'rol',
        'password',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'ultimo_acceso' => 'datetime',
    ];

    protected $dates = ['ultimo_acceso'];

    public function getUltimoAccesoFormateadoAttribute()
    {
        return $this->ultimo_acceso
            ? Carbon::parse($this->ultimo_acceso)
            ->locale('es')
            ->isoFormat('DD/MM/YYYY HH:mm')
            : 'N/A';
    }
}

