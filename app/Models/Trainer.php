<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entrenador extends Model
{
    protected $table = 'entrenadores';

    protected $fillable = [
        'nombre',
        'imagen',
        'descripcion',
    ];
}
