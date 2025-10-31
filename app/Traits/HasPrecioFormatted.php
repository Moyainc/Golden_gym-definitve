<?php

namespace App\Traits;

trait HasPrecioFormatted
{
  public function getPrecioFormattedAttribute()
  {
    $camposMonetarios = ['precio', 'precio_unitario', 'subtotal', 'total', 'costo', 'valor'];
    $valor = 0;

    foreach ($camposMonetarios as $campo) {
      if (isset($this->$campo)) {
        $valor = $this->$campo;
        break;
      }
    }

    return '$' . number_format($valor, 0, ',', '.');
  }
}
