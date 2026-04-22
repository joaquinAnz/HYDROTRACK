<?php

namespace App\Http\Controllers;

use App\Models\Rol;

class RolController extends Controller
{
    public function index()
    {
        return response()->json(
            Rol::query()->orderBy('nombre')->get(['id_rol', 'nombre'])
        );
    }
}
