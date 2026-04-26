<?php

namespace Database\Seeders;

use App\Models\Repuesto;
use Illuminate\Database\Seeder;

class RepuestosSeeder extends Seeder
{
    public function run(): void
    {
        $repuestos = [
            [
                'nombre' => 'Filtro de aceite FRAM PH7317',
                'marca' => 'FRAM',
                'descripcion' => 'Filtro de aceite para motor gasolina 1.6-2.0',
                'stock_actual' => 25,
                'stock_minimo' => 8,
                'precio_compra' => 22.50,
                'precio_venta' => 35.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Filtro de aire Bosch S0245',
                'marca' => 'Bosch',
                'descripcion' => 'Filtro de aire para sedanes compactos',
                'stock_actual' => 18,
                'stock_minimo' => 6,
                'precio_compra' => 30.00,
                'precio_venta' => 48.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Pastillas de freno delanteras Ceramix',
                'marca' => 'Ceramix',
                'descripcion' => 'Juego de pastillas delanteras ceramicas',
                'stock_actual' => 14,
                'stock_minimo' => 5,
                'precio_compra' => 95.00,
                'precio_venta' => 140.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Bujia NGK Iridium IX',
                'marca' => 'NGK',
                'descripcion' => 'Bujia de alto rendimiento iridium',
                'stock_actual' => 40,
                'stock_minimo' => 12,
                'precio_compra' => 18.00,
                'precio_venta' => 32.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Correa de distribucion Gates K015',
                'marca' => 'Gates',
                'descripcion' => 'Kit de correa de distribucion con tensor',
                'stock_actual' => 9,
                'stock_minimo' => 3,
                'precio_compra' => 180.00,
                'precio_venta' => 260.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Amortiguador delantero Monroe 3321',
                'marca' => 'Monroe',
                'descripcion' => 'Amortiguador delantero para eje MacPherson',
                'stock_actual' => 7,
                'stock_minimo' => 2,
                'precio_compra' => 210.00,
                'precio_venta' => 320.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Bateria 12V 65Ah Titan',
                'marca' => 'Titan',
                'descripcion' => 'Bateria libre mantenimiento 12V 65Ah',
                'stock_actual' => 6,
                'stock_minimo' => 2,
                'precio_compra' => 430.00,
                'precio_venta' => 580.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Aceite 20W50 Mineral 1L',
                'marca' => 'Mobil',
                'descripcion' => 'Aceite mineral para motores con alto kilometraje',
                'stock_actual' => 55,
                'stock_minimo' => 15,
                'precio_compra' => 28.00,
                'precio_venta' => 42.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Refrigerante Long Life 1L',
                'marca' => 'Prestone',
                'descripcion' => 'Refrigerante concentrado larga duracion',
                'stock_actual' => 20,
                'stock_minimo' => 6,
                'precio_compra' => 24.00,
                'precio_venta' => 38.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Disco de freno delantero Ventilado',
                'marca' => 'Brembo',
                'descripcion' => 'Disco ventilado para mejor disipacion termica',
                'stock_actual' => 11,
                'stock_minimo' => 4,
                'precio_compra' => 120.00,
                'precio_venta' => 190.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Bombin de embrague Sachs',
                'marca' => 'Sachs',
                'descripcion' => 'Bombin hidraulico para sistema de embrague',
                'stock_actual' => 8,
                'stock_minimo' => 3,
                'precio_compra' => 150.00,
                'precio_venta' => 225.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Alternador 90A Remanufacturado',
                'marca' => 'Valeo',
                'descripcion' => 'Alternador remanufacturado 12V 90A',
                'stock_actual' => 4,
                'stock_minimo' => 1,
                'precio_compra' => 720.00,
                'precio_venta' => 980.00,
                'estado' => true,
            ],
        ];

        foreach ($repuestos as $item) {
            Repuesto::updateOrCreate(
                ['nombre' => $item['nombre']],
                $item
            );
        }
    }
}
