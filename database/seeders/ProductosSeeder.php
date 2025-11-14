<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Producto;

class ProductosSeeder extends Seeder
{
    public function run(): void
    {
        // 20 categorías de prueba con nombre y descripción
        $categorias = [
            ['nombre' => 'Suplementos', 'descripcion' => 'Suplementos deportivos'],
            ['nombre' => 'Ropa deportiva', 'descripcion' => 'Camisetas, leggins y más'],
            ['nombre' => 'Calzado', 'descripcion' => 'Zapatillas para entrenamiento'],
            ['nombre' => 'Accesorios de entrenamiento', 'descripcion' => 'Accesorios varios'],
            ['nombre' => 'Pesas y mancuernas', 'descripcion' => 'Pesas, mancuernas y barras'],
            ['nombre' => 'Bandas de resistencia', 'descripcion' => 'Bandas elásticas de diferentes resistencias'],
            ['nombre' => 'Botellas y shakers', 'descripcion' => 'Para tus bebidas y proteínas'],
            ['nombre' => 'Gorras y viseras', 'descripcion' => 'Protección y estilo'],
            ['nombre' => 'Mochilas deportivas', 'descripcion' => 'Transporta tu equipo con comodidad'],
            ['nombre' => 'Guantes de entrenamiento', 'descripcion' => 'Protección para manos'],
            ['nombre' => 'Esterillas y colchonetas', 'descripcion' => 'Yoga, pilates y entrenamiento'],
            ['nombre' => 'Cuerdas para saltar', 'descripcion' => 'Cardio y coordinación'],
            ['nombre' => 'Balones y pelotas', 'descripcion' => 'Balones para entrenamiento y recreación'],
            ['nombre' => 'Rodilleras y muñequeras', 'descripcion' => 'Protección articular'],
            ['nombre' => 'Pulseras y relojes deportivos', 'descripcion' => 'Monitoreo y estilo'],
            ['nombre' => 'Snacks saludables', 'descripcion' => 'Barras y snacks nutritivos'],
            ['nombre' => 'Proteínas en polvo', 'descripcion' => 'Whey, vegana y más'],
            ['nombre' => 'Vitaminas y suplementos', 'descripcion' => 'Vitaminas y minerales'],
            ['nombre' => 'Equipamiento cardio', 'descripcion' => 'Cintas, bicicletas y elípticas'],
            ['nombre' => 'Camisetas técnicas', 'descripcion' => 'Ropa ligera y transpirable'],
        ];

        // Crear categorías y guardar IDs para asignar productos
        $categoria_ids = [];
        foreach ($categorias as $c) {
            $categoria = Categoria::firstOrCreate(['nombre' => $c['nombre']], $c);
            $categoria_ids[] = $categoria->id;
        }

        // 30 productos de prueba
        $productos = [
            'Whey Protein 1kg',
            'Creatina Monohidratada 300g',
            'BCAA 500g',
            'Camiseta Dry Fit Azul',
            'Leggings Mujer Negro',
            'Zapatillas Running Hombre',
            'Guantes de Crossfit Talla M',
            'Bandas de Resistencia Set 5',
            'Esterilla Yoga 180x60 cm',
            'Botella Shaker 700ml',
            'Cuerda para Saltar Ajustable',
            'Balón Medicinal 5kg',
            'Rodillera Deportiva Ajustable',
            'Mochila Gym 20L',
            'Gorro Deportivo Transpirable',
            'Pulsera Fitness con Medidor de Ritmo',
            'Snack Proteico Bar 50g',
            'Vitaminas Multivitamínicas 60 caps',
            'Kit Mancuernas 10kg',
            'Camiseta Técnica Hombre Gris',
            'Zapatillas Entrenamiento Mujer',
            'Balón de Fútbol Sala',
            'Muñequeras con Soporte',
            'Banda Elástica Larga',
            'Botella Termo 500ml',
            'Guantes de Pesas Talla L',
            'Esterilla Pilates 170x60 cm',
            'Snack Saludable Mix Frutos',
            'Kit Cardio (Cuerda + Conos)',
            'Proteína Vegana 1kg'
        ];

        // Crear productos asignando categoría aleatoria
        foreach ($productos as $prod) {
            Producto::create([
                'nombre' => $prod,
                'categoria_id' => $categoria_ids[array_rand($categoria_ids)],
                'stock' => rand(5, 100),
                'precio' => rand(10000, 500000),
                'foto' => 'placeholder.png',
                'estado' => 'Activo',
            ]);
        }
    }
}
