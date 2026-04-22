<?php

namespace App\Http\Controllers\Api;

use App\Models\Rol;
use App\Http\Controllers\Controller;

class RolController extends Controller
{
    public function index()
    {
        return response()->json(
            Rol::query()->orderBy('nombre')->get(['id_rol', 'nombre'])
        );
    }
}
