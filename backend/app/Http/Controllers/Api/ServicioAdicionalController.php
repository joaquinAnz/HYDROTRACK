<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicioAdicional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicioAdicionalController extends Controller
{
    public function index(): JsonResponse
    {
        $servicios = ServicioAdicional::orderByDesc('id_servicio')->get();

        return response()->json([
            'message' => 'Lista de servicios adicionales',
            'data' => $servicios
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:servicios_adicionales,nombre',
            'descripcion' => 'nullable|string',
            'costo_base' => 'required|numeric|min:0',
            'estado' => 'nullable|boolean',
        ]);

        $servicio = ServicioAdicional::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'costo_base' => $request->costo_base,
            'estado' => $request->estado ?? 1,
        ]);

        return response()->json([
            'message' => 'Servicio adicional creado correctamente',
            'data' => $servicio
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $servicio = ServicioAdicional::find($id);

        if (!$servicio) {
            return response()->json([
                'message' => 'Servicio adicional no encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Detalle del servicio adicional',
            'data' => $servicio
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $servicio = ServicioAdicional::find($id);

        if (!$servicio) {
            return response()->json([
                'message' => 'Servicio adicional no encontrado'
            ], 404);
        }

        $request->validate([
            'nombre' => 'required|string|max:100|unique:servicios_adicionales,nombre,' . $id . ',id_servicio',
            'descripcion' => 'nullable|string',
            'costo_base' => 'required|numeric|min:0',
            'estado' => 'nullable|boolean',
        ]);

        $servicio->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'costo_base' => $request->costo_base,
            'estado' => $request->estado ?? $servicio->estado,
        ]);

        return response()->json([
            'message' => 'Servicio adicional actualizado correctamente',
            'data' => $servicio
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $servicio = ServicioAdicional::find($id);

        if (!$servicio) {
            return response()->json([
                'message' => 'Servicio adicional no encontrado'
            ], 404);
        }

        $servicio->delete();

        return response()->json([
            'message' => 'Servicio adicional eliminado correctamente'
        ]);
    }
}