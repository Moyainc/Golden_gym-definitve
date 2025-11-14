<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PagoMembresia;
use App\Models\Cliente;
use App\Models\Usuario;

class PagosMembresiaSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();
        $usuarios = Usuario::all();

        foreach ($clientes as $cliente) {
            $pagos_count = rand(1, 2);
            for ($i = 0; $i < $pagos_count; $i++) {
                PagoMembresia::create([
                    'cliente_id' => $cliente->id,
                    'membresia_id' => rand(1, 5),
                    'usuario_id' => $usuarios->random()->id ?? 1,
                    'fecha_inicio' => now()->subMonths(rand(0, 6)),
                    'fecha_fin' => now()->addMonths(rand(1, 6)),
                    'monto' => rand(50000, 200000),
                    'estado' => ['Activo', 'Inactivo'][rand(0, 1)],
                    'metodo_pago' => ['Efectivo', 'Transferencia'][rand(0, 1)],
                ]);
            }
        }
    }
}
