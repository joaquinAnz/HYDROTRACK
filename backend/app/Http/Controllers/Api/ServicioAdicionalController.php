<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicioAdicional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
        $hasDescripcion = Schema::hasColumn('servicios_adicionales', 'descripcion');
        $hasCostoBase = Schema::hasColumn('servicios_adicionales', 'costo_base');
        $hasPrecio = Schema::hasColumn('servicios_adicionales', 'precio');

        $rules = [
            'nombre' => 'required|string|max:100|unique:servicios_adicionales,nombre',
            'estado' => 'nullable|boolean',
        ];

        if ($hasDescripcion) {
            $rules['descripcion'] = 'nullable|string';
        }

        if ($hasCostoBase) {
            $rules['costo_base'] = 'required_without:precio|numeric|min:0';
        }

        if ($hasPrecio) {
            $rules['precio'] = 'required_without:costo_base|numeric|min:0';
        }

        $request->validate($rules);

        $payload = [
            'nombre' => $request->nombre,
            'estado' => $request->estado ?? 1,
        ];

        if ($hasDescripcion) {
            $payload['descripcion'] = $request->descripcion;
        }

        if ($hasCostoBase) {
            $payload['costo_base'] = $request->input('costo_base', $request->input('precio', 0));
        }

        if ($hasPrecio) {
            $payload['precio'] = $request->input('precio', $request->input('costo_base', 0));
        }

        $servicio = ServicioAdicional::create($payload);

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

        $hasDescripcion = Schema::hasColumn('servicios_adicionales', 'descripcion');
        $hasCostoBase = Schema::hasColumn('servicios_adicionales', 'costo_base');
        $hasPrecio = Schema::hasColumn('servicios_adicionales', 'precio');

        $rules = [
            'nombre' => 'required|string|max:100|unique:servicios_adicionales,nombre,' . $id . ',id_servicio',
            'estado' => 'nullable|boolean',
        ];

        if ($hasDescripcion) {
            $rules['descripcion'] = 'nullable|string';
        }

        if ($hasCostoBase) {
            $rules['costo_base'] = 'required_without:precio|numeric|min:0';
        }

        if ($hasPrecio) {
            $rules['precio'] = 'required_without:costo_base|numeric|min:0';
        }

        $request->validate($rules);

        $payload = [
            'nombre' => $request->nombre,
            'estado' => $request->estado ?? $servicio->estado,
        ];

        if ($hasDescripcion) {
            $payload['descripcion'] = $request->descripcion;
        }

        if ($hasCostoBase) {
            $payload['costo_base'] = $request->input('costo_base', $request->input('precio', 0));
        }

        if ($hasPrecio) {
            $payload['precio'] = $request->input('precio', $request->input('costo_base', 0));
        }

        $servicio->update($payload);

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