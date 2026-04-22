<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use Illuminate\Http\JsonResponse;

class VehiculoController extends Controller
{
    public function index(): JsonResponse
    {
        $vehiculos = Vehiculo::with('cliente')
            ->orderByDesc('id_vehiculo')
            ->get();

        return response()->json([
            'message' => 'Lista de vehículos',
            'data' => $vehiculos
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $vehiculo = Vehiculo::with('cliente')->find($id);

        if (!$vehiculo) {
            return response()->json([
                'message' => 'Vehículo no encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Detalle del vehículo',
            'data' => $vehiculo
        ]);
    }
}