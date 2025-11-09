<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin 1
        Usuario::updateOrCreate(
            ['correo' => 'adminMaster@gym.com'],
            [
                'nombre_completo' => 'Super Admin David',
                'cedula' => '123456789',
                'telefono' => '3000000000',
                'rol' => 'Administrador',
                'password' => Hash::make('SuperPass123!'),
                'oculto' => true,
            ]
        );
    }
}
