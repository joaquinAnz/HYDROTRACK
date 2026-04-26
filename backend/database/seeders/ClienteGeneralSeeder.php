<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteGeneralSeeder extends Seeder
{
    public function run(): void
    {
        Cliente::updateOrCreate(
            ['carnet_identidad' => '0000000'],
            [
                'nombres' => 'CLIENTE',
                'apellidos' => 'GENERAL',
                'telefono' => '0000000',
                'estado' => true,
            ]
        );
    }
}
