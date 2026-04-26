<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdenActualizacion;
use App\Models\OrdenTrabajo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdenActualizacionController extends Controller
{
    public function indexByOrden(int $id): JsonResponse
    {
        $orden = OrdenTrabajo::find($id);

        if (!$orden) {
            return response()->json([
                'message' => 'Orden no encontrada.',
            ], 404);
        }

        $actualizaciones = OrdenActualizacion::with('usuario')
            ->where('id_orden', $id)
            ->orderByDesc('id_actualizacion')
            ->get();

        return response()->json([
            'message' => 'Historial de actualizaciones obtenido correctamente.',
            'data' => $actualizaciones,
        ]);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $orden = OrdenTrabajo::find($id);

        if (!$orden) {
            return response()->json([
                'message' => 'Orden no encontrada.',
            ], 404);
        }

        $request->validate([
            'descripcion' => 'required|string|max:3000',
            'tipo' => 'nullable|string|max:40',
            'id_usuario' => 'nullable|integer|exists:usuarios,id_usuario',
        ]);

        $actualizacion = OrdenActualizacion::create([
            'id_orden' => $id,
            'id_usuario' => $request->id_usuario,
            'tipo' => $request->tipo ?? 'actualizacion',
            'descripcion' => $request->descripcion,
        ]);

        $actualizacion->load('usuario');

        return response()->json([
            'message' => 'Actualizacion registrada correctamente.',
            'data' => $actualizacion,
        ], 201);
    }
}
