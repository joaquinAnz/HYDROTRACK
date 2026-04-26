<?php

namespace App\Http\Controllers\Api;

use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::query()
            ->orderByDesc('id_cliente')
            ->get()
            ->map(function (Cliente $cliente) {
                return [
                    'id_cliente' => $cliente->id_cliente,
                    'nombres' => $cliente->nombres,
                    'apellidos' => $cliente->apellidos,
                    'carnet_identidad' => $cliente->carnet_identidad,
                    'telefono' => $cliente->telefono,
                    'fecha_registro' => $cliente->fecha_registro,
                    'ultima_modificacion' => $cliente->ultima_modificacion,
                    'estado' => (bool) $cliente->estado,
                ];
            });

        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'carnet_identidad' => 'required|string|max:30|unique:clientes,carnet_identidad',
            'telefono' => 'required|string|max:20',
        ]);

        $cliente = Cliente::create([
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'carnet_identidad' => $validated['carnet_identidad'],
            'telefono' => $validated['telefono'],
            'estado' => true,
        ]);

        return response()->json([
            'message' => 'Cliente creado correctamente',
            'cliente' => [
                'id_cliente' => $cliente->id_cliente,
                'nombres' => $cliente->nombres,
                'apellidos' => $cliente->apellidos,
                'carnet_identidad' => $cliente->carnet_identidad,
                'telefono' => $cliente->telefono,
                'fecha_registro' => $cliente->fecha_registro,
                'ultima_modificacion' => $cliente->ultima_modificacion,
                'estado' => (bool) $cliente->estado,
            ],
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'nombres' => 'sometimes|string|max:100',
            'apellidos' => 'sometimes|string|max:100',
            'carnet_identidad' => 'sometimes|string|max:30|unique:clientes,carnet_identidad,' . $id . ',id_cliente',
            'telefono' => 'sometimes|string|max:20',
            'estado' => 'sometimes|boolean',
        ]);

        $cliente->update($validated);

        return response()->json([
            'message' => 'Cliente actualizado correctamente',
            'cliente' => [
                'id_cliente' => $cliente->id_cliente,
                'nombres' => $cliente->nombres,
                'apellidos' => $cliente->apellidos,
                'carnet_identidad' => $cliente->carnet_identidad,
                'telefono' => $cliente->telefono,
                'fecha_registro' => $cliente->fecha_registro,
                'ultima_modificacion' => $cliente->ultima_modificacion,
                'estado' => (bool) $cliente->estado,
            ],
        ]);
    }
}
