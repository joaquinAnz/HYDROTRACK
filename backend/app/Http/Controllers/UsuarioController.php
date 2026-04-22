<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    private const EMPLOYEE_ROLE_NAMES = ['admin', 'administrador', 'tecnico', 'ventas'];

    public function index()
    {
        try {
            $usuarios = Usuario::with('rol')
                ->where('estado', 1)
                ->orderByDesc('id_usuario')
                ->get()
                ->map(function (Usuario $usuario) {
                    // Solo mostrar usuarios con roles válidos
                    $rolNombre = $usuario->rol?->nombre;
                    if (!$rolNombre || !in_array(strtolower($rolNombre), self::EMPLOYEE_ROLE_NAMES, true)) {
                        return null;
                    }
                    
                    return [
                        'id_usuario' => $usuario->id_usuario,
                        'nombres' => $usuario->nombres ?? '',
                        'apellidos' => $usuario->apellidos ?? '',
                        'telefono' => $usuario->telefono ?? '',
                        'usuario' => $usuario->usuario ?? '',
                        'id_rol' => $usuario->id_rol,
                        'rol' => $rolNombre,
                        'estado' => (bool) $usuario->estado,
                        'fecha_creacion' => $usuario->fecha_creacion,
                        'ultima_modificacion' => $usuario->ultima_modificacion,
                    ];
                })
                ->filter() // Elimina los null
                ->values(); // Reindexa el array

            return response()->json($usuarios);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'required|string|max:20',
            'usuario' => 'required|string|max:100|unique:usuarios,usuario',
            'password' => 'required|string|min:6|max:100',
            'id_rol' => 'required|integer|exists:roles,id_rol',
            'estado' => 'sometimes|boolean',
        ], [
            'usuario.unique' => 'Este usuario ya existe.',
        ]);

        $rol = Rol::findOrFail($validated['id_rol']);
        if (!in_array(strtolower($rol->nombre), self::EMPLOYEE_ROLE_NAMES, true)) {
            return response()->json([
                'message' => 'Solo se permite crear empleados con rol admin, tecnico o ventas',
            ], 422);
        }

        $usuario = Usuario::create([
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'telefono' => $validated['telefono'],
            'usuario' => $validated['usuario'],
            'password_hash' => Hash::make($validated['password']),
            'id_rol' => $rol->id_rol,
            'estado' => $validated['estado'] ?? true,
        ]);

        $usuario->load('rol');

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'usuario' => [
                'id_usuario' => $usuario->id_usuario,
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'telefono' => $usuario->telefono,
                'usuario' => $usuario->usuario,
                'id_rol' => $usuario->id_rol,
                'rol' => $usuario->rol?->nombre,
                'estado' => (bool) $usuario->estado,
                'fecha_creacion' => $usuario->fecha_creacion,
                'ultima_modificacion' => $usuario->ultima_modificacion,
            ],
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $validated = $request->validate([
            'nombres' => 'sometimes|string|max:100',
            'apellidos' => 'sometimes|string|max:100',
            'telefono' => 'sometimes|string|max:20',
            'usuario' => 'sometimes|string|max:100|unique:usuarios,usuario,' . $id . ',id_usuario',
            'id_rol' => 'sometimes|integer|exists:roles,id_rol',
            'estado' => 'sometimes|boolean',
            'password' => 'sometimes|string|min:6|max:100',
        ], [
            'usuario.unique' => 'Este usuario ya existe.',
        ]);

        if (isset($validated['id_rol'])) {
            $rol = Rol::findOrFail($validated['id_rol']);
            if (!in_array(strtolower($rol->nombre), self::EMPLOYEE_ROLE_NAMES, true)) {
                return response()->json([
                    'message' => 'Solo se permite asignar roles admin, tecnico o ventas',
                ], 422);
            }
        }

        if (isset($validated['password'])) {
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);
        }

        $usuario->update($validated);
        $usuario->load('rol');

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'usuario' => [
                'id_usuario' => $usuario->id_usuario,
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'telefono' => $usuario->telefono,
                'usuario' => $usuario->usuario,
                'id_rol' => $usuario->id_rol,
                'rol' => $usuario->rol?->nombre,
                'estado' => (bool) $usuario->estado,
                'fecha_creacion' => $usuario->fecha_creacion,
                'ultima_modificacion' => $usuario->ultima_modificacion,
            ],
        ]);
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);

        $usuario->update([
            'estado' => false,
        ]);

        return response()->json([
            'message' => 'Empleado inactivado correctamente',
        ]);
    }
}
