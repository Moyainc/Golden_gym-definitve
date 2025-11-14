<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Usuario;

class VentasSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();
        $usuarios = Usuario::all();
        $productos = Producto::all();

        foreach ($clientes as $cliente) {
            // Crear entre 1 y 3 ventas por cliente
            $ventas_count = rand(1, 3);

            for ($i = 0; $i < $ventas_count; $i++) {
                $venta = Venta::create([
                    'usuario_id' => $usuarios->random()->id ?? 1,
                    'cliente_id' => $cliente->id,
                    'total' => 0,
                    'metodo_pago' => ['Efectivo', 'Transferencia'][rand(0, 1)],
                    'estado' => ['Completada', 'Pendiente', 'Anulada'][rand(0, 2)],
                    'descripcion' => 'Venta de prueba',
                    'fecha_venta' => now()->subDays(rand(0, 30)),
                ]);

                // Agregar entre 1 y 5 detalles de productos
                $productos_sample = $productos->random(rand(1, 5));
                foreach ($productos_sample as $producto) {
                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $producto->id,
                        'cantidad' => rand(1, 5),
                        'precio_unitario' => $producto->precio,
                        'subtotal' => 0, // se calcula automáticamente
                    ]);
                }
            }
        }
    }
}
