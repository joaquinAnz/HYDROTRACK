<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $adminRol = Rol::query()->where('nombre', 'admin')->first();

        if (!$adminRol) {
            return;
        }

        Usuario::query()->updateOrCreate(
            ['usuario' => 'admin'],
            [
                'nombres' => 'Admin',
                'apellidos' => 'Sistema',
                'telefono' => '70000000',
                'password_hash' => Hash::make('admin123'),
                'id_rol' => $adminRol->id_rol,
                'estado' => true,
            ]
        );
    }
}
