<?php

namespace Database\Seeders;

use App\Models\EstadoOrden;
use Illuminate\Database\Seeder;

class EstadosOrdenSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            'PENDIENTE',
            'EN PROCESO',
            'FINALIZADO',
        ];

        foreach ($estados as $nombre) {
            EstadoOrden::updateOrCreate(
                ['nombre' => $nombre],
                ['nombre' => $nombre]
            );
        }
    }
}
