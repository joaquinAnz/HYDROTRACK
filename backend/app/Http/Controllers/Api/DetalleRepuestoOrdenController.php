<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleRepuestoOrden;
use App\Models\DetalleServicioOrden;
use App\Models\OrdenTrabajo;
use App\Models\Repuesto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DetalleRepuestoOrdenController extends Controller
{
    public function index(): JsonResponse
    {
        $detalles = DetalleRepuestoOrden::with(['orden', 'repuesto'])
            ->orderByDesc('id_detalle_repuesto')
            ->get();

        return response()->json([
            'message' => 'Lista de detalles de repuesto por orden',
            'data' => $detalles,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_orden' => 'required|exists:ordenes_trabajo,id_orden',
            'id_repuesto' => 'required|exists:repuestos,id_repuesto',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $repuesto = Repuesto::findOrFail($request->id_repuesto);
        if ((int) $repuesto->stock_actual < (int) $request->cantidad) {
            return response()->json([
                'message' => 'Stock insuficiente para el repuesto seleccionado',
            ], 422);
        }

        $detalle = DetalleRepuestoOrden::create([
            'id_orden' => $request->id_orden,
            'id_repuesto' => $request->id_repuesto,
            'cantidad' => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
        ]);

        $repuesto->update([
            'stock_actual' => (int) $repuesto->stock_actual - (int) $request->cantidad,
        ]);

        $this->recalcularTotalOrden($request->id_orden);
        $detalle->load(['orden', 'repuesto']);

        return response()->json([
            'message' => 'Repuesto agregado a la orden correctamente',
            'data' => $detalle,
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $detalle = DetalleRepuestoOrden::find($id);

        if (!$detalle) {
            return response()->json([
                'message' => 'Detalle de repuesto no encontrado',
            ], 404);
        }

        $repuesto = Repuesto::find($detalle->id_repuesto);
        if ($repuesto) {
            $repuesto->update([
                'stock_actual' => (int) $repuesto->stock_actual + (int) $detalle->cantidad,
            ]);
        }

        $idOrden = $detalle->id_orden;
        $detalle->delete();

        $this->recalcularTotalOrden($idOrden);

        return response()->json([
            'message' => 'Detalle de repuesto eliminado correctamente',
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $detalle = DetalleRepuestoOrden::find($id);

        if (!$detalle) {
            return response()->json([
                'message' => 'Detalle de repuesto no encontrado',
            ], 404);
        }

        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $detalle->update([
            'cantidad' => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
        ]);

        $this->recalcularTotalOrden($detalle->id_orden);
        $detalle->load(['orden', 'repuesto']);

        return response()->json([
            'message' => 'Detalle de repuesto actualizado correctamente',
            'data' => $detalle,
        ]);
    }

    private function recalcularTotalOrden(int $idOrden): void
    {
        $orden = OrdenTrabajo::find($idOrden);

        if (!$orden) {
            return;
        }

        $totalServicios = DetalleServicioOrden::where('id_orden', $idOrden)
            ->get()
            ->sum(fn ($item) => $item->cantidad * $item->precio_unitario);

        $totalRepuestos = DetalleRepuestoOrden::where('id_orden', $idOrden)
            ->get()
            ->sum(fn ($item) => $item->cantidad * $item->precio_unitario);

        $nuevoTotal = ($orden->costo_mano_obra ?? 0) + $totalServicios + $totalRepuestos;
        $orden->setAttribute('total_orden', $nuevoTotal);
        $orden->setAttribute('pago_completo', (float) ($orden->total_pagado ?? 0) >= (float) $nuevoTotal && (float) $nuevoTotal > 0);
        $orden->save();
    }
}
