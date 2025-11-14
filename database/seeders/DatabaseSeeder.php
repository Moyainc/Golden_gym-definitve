<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;


    public function run(): void
    {
        // // Crear un usuario de prueba si no existe
        // Usuario::firstOrCreate(
        //     ['correo' => 'admin@example.com'],
        //     [
        //         'nombre_completo' => 'Admin',
        //         'rol' => 'Administrador',
        //         'password' => bcrypt('password')
        //     ]
        // );

        // Aquí llamas al seeder de entrenadores
        $this->call(EntrenadoresSeeder::class);

        $this->call([
            ClientesSeeder::class,
            ProductosSeeder::class,
            MembresiasSeeder::class,
            VentasSeeder::class,
            PagosMembresiaSeeder::class,
            ComprobantesSeeder::class,
        ]);
    }
}
