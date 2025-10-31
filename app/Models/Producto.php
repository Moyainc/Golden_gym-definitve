<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPrecioFormatted;

class Producto extends Model
{
    use HasPrecioFormatted;
    use HasFactory;

    protected $fillable = [
        'nombre',
        'categoria_id',
        'stock',
        'precio',
        'foto',
        'estado',
    ];

    protected $appends = ['precio_formatted'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
