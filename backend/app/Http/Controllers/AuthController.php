<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string'
        ]);

        $usuario = Usuario::with('rol')
            ->where('usuario', $request->usuario)
            ->first();

        if (!$usuario) {
            return response()->json([
                'message' => 'El usuario no existe'
            ], 401);
        }

        if (!Hash::check($request->password, $usuario->password_hash)) {
            return response()->json([
                'message' => 'Contraseña incorrecta'
            ], 401);
        }

        if ((int) $usuario->estado !== 1) {
            return response()->json([
                'message' => 'Usuario inactivo'
            ], 403);
        }

        return response()->json([
            'message' => 'Login exitoso',
            'usuario' => [
                'id_usuario' => $usuario->id_usuario,
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'usuario' => $usuario->usuario,
                'id_rol' => $usuario->id_rol,
                'rol' => $usuario->rol?->nombre
            ]
        ]);
    }
}