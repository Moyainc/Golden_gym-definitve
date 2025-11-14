<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entrenador;

class EntrenadoresSeeder extends Seeder
{
  public function run(): void
  {
    $entrenadores = [
      [
        'nombre' => 'Carlos Mendoza',
        'descripcion' => 'Especialista en Fuerza y Acondicionamiento',
        'imagen' => null,
      ],
      [
        'nombre' => 'Ana Rodríguez',
        'descripcion' => 'Entrenadora de Yoga y Pilates',
        'imagen' => null,
      ],
      [
        'nombre' => 'Miguel Torres',
        'descripcion' => 'Experto en CrossFit y HIIT',
        'imagen' => null,
      ],
      [
        'nombre' => 'Laura Fernández',
        'descripcion' => 'Nutricionista y Entrenadora Personal',
        'imagen' => null,
      ],
    ];

    foreach ($entrenadores as $item) {
      Entrenador::create($item);
    }
  }
}
