<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Membresia;

class MembresiasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $membresias = [
            [
                'nombre' => 'Básica',
                'duracion' => 1,
                'tipo_duracion' => 'Meses',
                'precio' => 50000,
            ],
            [
                'nombre' => 'Estándar',
                'duracion' => 3,
                'tipo_duracion' => 'Meses',
                'precio' => 120000,
            ],
            [
                'nombre' => 'Premium',
                'duracion' => 6,
                'tipo_duracion' => 'Meses',
                'precio' => 200000,
            ],
            [
                'nombre' => 'VIP',
                'duracion' => 12,
                'tipo_duracion' => 'Meses',
                'precio' => 350000,
            ],
        ];

        foreach ($membresias as $membresia) {
            Membresia::create($membresia);
        }
    }
}
