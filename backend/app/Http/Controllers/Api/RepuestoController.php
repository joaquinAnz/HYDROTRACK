<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Repuesto;
use Illuminate\Http\Request;

class RepuestoController extends Controller
{
    public function index(Request $request)
    {
        $soloActivos = $request->query('activos', '1') === '1';

        $query = Repuesto::query()->orderByDesc('id_repuesto');

        if ($soloActivos) {
            $query->where('estado', 1);
        }

        $repuestos = $query->get()->map(function (Repuesto $repuesto) {
            return [
                'id_repuesto' => $repuesto->id_repuesto,
                'nombre' => $repuesto->nombre,
                'marca' => $repuesto->marca,
                'descripcion' => $repuesto->descripcion,
                'stock_actual' => $repuesto->stock_actual,
                'stock_minimo' => $repuesto->stock_minimo,
                'precio_compra' => (float) $repuesto->precio_compra,
                'precio_venta' => (float) $repuesto->precio_venta,
                'estado' => (bool) $repuesto->estado,
            ];
        });

        return response()->json($repuestos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:repuestos,nombre',
            'marca' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'estado' => 'sometimes|boolean',
        ], [
            'nombre.unique' => 'Este codigo de repuesto ya existe.',
        ]);

        $repuesto = Repuesto::create([
            'nombre' => $validated['nombre'],
            'marca' => $validated['marca'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'stock_actual' => $validated['stock_actual'],
            'stock_minimo' => $validated['stock_minimo'],
            'precio_compra' => $validated['precio_compra'],
            'precio_venta' => $validated['precio_venta'],
            'estado' => $validated['estado'] ?? true,
        ]);

        return response()->json([
            'message' => 'Repuesto creado correctamente',
            'repuesto' => $repuesto,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $repuesto = Repuesto::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:100|unique:repuestos,nombre,' . $id . ',id_repuesto',
            'marca' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'stock_actual' => 'sometimes|integer|min:0',
            'stock_minimo' => 'sometimes|integer|min:0',
            'precio_compra' => 'sometimes|numeric|min:0',
            'precio_venta' => 'sometimes|numeric|min:0',
            'estado' => 'sometimes|boolean',
        ], [
            'nombre.unique' => 'Este codigo de repuesto ya existe.',
        ]);

        $repuesto->update($validated);

        return response()->json([
            'message' => 'Repuesto actualizado correctamente',
            'repuesto' => $repuesto,
        ]);
    }

    public function destroy($id)
    {
        $repuesto = Repuesto::findOrFail($id);
        $repuesto->update(['estado' => false]);

        return response()->json([
            'message' => 'Repuesto eliminado correctamente',
        ]);
    }
}
