<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoServicio;
use Illuminate\Http\Request;

class TipoServicioController extends Controller
{
    public function index()
    {
        $tipos = TipoServicio::orderBy('id_tipo_servicio', 'asc')->get();

        return response()->json([
            'message' => 'Lista de tipos de servicio',
            'data' => $tipos
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:tipos_servicio,nombre',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|boolean',
        ]);

        $tipo = TipoServicio::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado ?? 1,
        ]);

        return response()->json([
            'message' => 'Tipo de servicio creado correctamente',
            'data' => $tipo
        ], 201);
    }

    public function show($id)
    {
        $tipo = TipoServicio::find($id);

        if (!$tipo) {
            return response()->json([
                'message' => 'Tipo de servicio no encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Detalle del tipo de servicio',
            'data' => $tipo
        ]);
    }

    public function update(Request $request, $id)
    {
        $tipo = TipoServicio::find($id);

        if (!$tipo) {
            return response()->json([
                'message' => 'Tipo de servicio no encontrado'
            ], 404);
        }

        $request->validate([
            'nombre' => 'required|string|max:100|unique:tipos_servicio,nombre,' . $id . ',id_tipo_servicio',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|boolean',
        ]);

        $tipo->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado ?? $tipo->estado,
        ]);

        return response()->json([
            'message' => 'Tipo de servicio actualizado correctamente',
            'data' => $tipo
        ]);
    }

    public function destroy($id)
    {
        $tipo = TipoServicio::find($id);

        if (!$tipo) {
            return response()->json([
                'message' => 'Tipo de servicio no encontrado'
            ], 404);
        }

        $tipo->delete();

        return response()->json([
            'message' => 'Tipo de servicio eliminado correctamente'
        ]);
    }
}