<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $incluirEliminados = $request->query('incluir_eliminados', '0') === '1';

        $query = Vehiculo::with('cliente')
            ->orderByDesc('id_vehiculo')
        ;

        if (!$incluirEliminados) {
            $query->where('estado', '!=', 'eliminado');
        }

        $vehiculos = $query->get();

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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_cliente' => 'required|integer|exists:clientes,id_cliente',
            'placa' => 'required|string|max:20|unique:vehiculos,placa',
            'descripcion' => 'required|string|max:500',
        ]);

        $vehiculo = Vehiculo::create([
            'id_cliente' => $validated['id_cliente'],
            'placa' => strtoupper(trim($validated['placa'])),
            'descripcion' => trim($validated['descripcion']),
            'estado' => 'activo',
            'marca' => '',
            'modelo' => '',
            'anio' => null,
        ]);

        $vehiculo->load('cliente');

        return response()->json([
            'message' => 'Vehículo creado correctamente',
            'data' => $vehiculo,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $vehiculo = Vehiculo::with('cliente')->find($id);

        if (!$vehiculo) {
            return response()->json([
                'message' => 'Vehículo no encontrado',
            ], 404);
        }

        $validated = $request->validate([
            'id_cliente' => 'sometimes|integer|exists:clientes,id_cliente',
            'placa' => 'sometimes|string|max:20|unique:vehiculos,placa,' . $id . ',id_vehiculo',
            'descripcion' => 'sometimes|string|max:500',
        ]);

        if (isset($validated['placa'])) {
            $validated['placa'] = strtoupper(trim($validated['placa']));
        }

        if (isset($validated['descripcion'])) {
            $validated['descripcion'] = trim($validated['descripcion']);
        }

        $vehiculo->update($validated);
        $vehiculo->load('cliente');

        return response()->json([
            'message' => 'Vehículo actualizado correctamente',
            'data' => $vehiculo,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            return response()->json([
                'message' => 'Vehículo no encontrado',
            ], 404);
        }

        $vehiculo->update([
            'estado' => 'eliminado',
        ]);

        return response()->json([
            'message' => 'Vehículo eliminado correctamente',
        ]);
    }
}