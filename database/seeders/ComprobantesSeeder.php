<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comprobante;
use App\Models\Venta;
use App\Models\PagoMembresia;
use App\Models\Cliente;
use App\Models\Usuario;

class ComprobantesSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();
        $usuarios = Usuario::all();
        $ventas = Venta::all();
        $pagos_membresia = PagoMembresia::all();

        foreach ($ventas as $venta) {
            Comprobante::create([
                'foto_comprobante' => 'placeholder.png',
                'id_usuario' => $usuarios->random()->id ?? 1,
                'id_cliente' => $venta->cliente_id,
                'id_ventas' => $venta->id,
                'fecha_subida' => now(),
            ]);
        }

        foreach ($pagos_membresia as $pago) {
            Comprobante::create([
                'foto_comprobante' => 'placeholder.png',
                'id_usuario' => $usuarios->random()->id ?? 1,
                'id_cliente' => $pago->cliente_id,
                'id_pago_membresia' => $pago->id,
                'fecha_subida' => now(),
            ]);
        }
    }
}
