<?php

namespace Database\Seeders;

use App\Models\TipoServicio;
use Illuminate\Database\Seeder;

class TiposServicioSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'Servicio tecnico',
                'descripcion' => 'Orden de trabajo con diagnostico y mano de obra',
                'estado' => true,
            ],
            [
                'nombre' => 'Servicio solo venta',
                'descripcion' => 'Venta directa de repuestos sin orden tecnica completa',
                'estado' => true,
            ],
        ];

        foreach ($tipos as $item) {
            TipoServicio::updateOrCreate(
                ['nombre' => $item['nombre']],
                $item
            );
        }
    }
}
