<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClientesSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            ['nombre_completo' => 'Juan Pérez', 'cedula' => '100100100', 'correo' => 'juan@example.com', 'telefono' => '3001112222', 'eps' => 'Salud Total', 'numero_emergencia' => '3101234567', 'foto' => 'placeholder.png'],
            ['nombre_completo' => 'Ana Gómez', 'cedula' => '200200200', 'correo' => 'ana@example.com', 'telefono' => '3003334444', 'eps' => 'Sura', 'numero_emergencia' => '3107654321', 'foto' => 'placeholder.png'],
            ['nombre_completo' => 'Carlos Martínez', 'cedula' => '300300300', 'correo' => 'carlos@example.com', 'telefono' => '3005556666', 'eps' => 'Sanitas', 'numero_emergencia' => '3111234567', 'foto' => 'placeholder.png'],
            ['nombre_completo' => 'Laura Torres', 'cedula' => '400400400', 'correo' => 'laura@example.com', 'telefono' => '3007778888', 'eps' => 'Coomeva', 'numero_emergencia' => '3117654321', 'foto' => 'placeholder.png'],
            ['nombre_completo' => 'Pedro Ruiz', 'cedula' => '500500500', 'correo' => 'pedro@example.com', 'telefono' => '3009990000', 'eps' => 'Nueva EPS', 'numero_emergencia' => '3109876543', 'foto' => 'placeholder.png'],
        ];

        foreach ($clientes as $c) {
            Cliente::firstOrCreate(
                ['cedula' => $c['cedula']], // Busca por cédula
                $c // Si no existe, crea con todos los datos
            );
        }
    }
}
