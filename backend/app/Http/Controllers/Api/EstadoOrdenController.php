<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EstadoOrden;
use Illuminate\Http\Request;

class EstadoOrdenController extends Controller
{
    public function index()
    {
        $estados = EstadoOrden::orderBy('id_estado', 'asc')->get();

        return response()->json([
            'message' => 'Lista de estados de orden',
            'data' => $estados
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:estados_orden,nombre',
        ]);

        $estado = EstadoOrden::create([
            'nombre' => $request->nombre
        ]);

        return response()->json([
            'message' => 'Estado creado correctamente',
            'data' => $estado
        ], 201);
    }

    public function show($id)
    {
        $estado = EstadoOrden::find($id);

        if (!$estado) {
            return response()->json([
                'message' => 'Estado no encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Detalle del estado',
            'data' => $estado
        ]);
    }

    public function update(Request $request, $id)
    {
        $estado = EstadoOrden::find($id);

        if (!$estado) {
            return response()->json([
                'message' => 'Estado no encontrado'
            ], 404);
        }

        $request->validate([
            'nombre' => 'required|string|max:50|unique:estados_orden,nombre,' . $id . ',id_estado',
        ]);

        $estado->update([
            'nombre' => $request->nombre
        ]);

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'data' => $estado
        ]);
    }

    public function destroy($id)
    {
        $estado = EstadoOrden::find($id);

        if (!$estado) {
            return response()->json([
                'message' => 'Estado no encontrado'
            ], 404);
        }

        $estado->delete();

        return response()->json([
            'message' => 'Estado eliminado correctamente'
        ]);
    }
}