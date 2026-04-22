<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleServicioOrden;
use App\Models\OrdenTrabajo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DetalleServicioOrdenController extends Controller
{
    public function index(): JsonResponse
    {
        $detalles = DetalleServicioOrden::with(['orden', 'servicio'])
            ->orderByDesc('id_detalle_servicio')
            ->get();

        return response()->json([
            'message' => 'Lista de detalles de servicio por orden',
            'data' => $detalles
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_orden' => 'required|exists:ordenes_trabajo,id_orden',
            'id_servicio' => 'required|exists:servicios_adicionales,id_servicio',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $detalle = DetalleServicioOrden::create([
            'id_orden' => $request->id_orden,
            'id_servicio' => $request->id_servicio,
            'cantidad' => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
        ]);

        $this->recalcularTotalOrden($request->id_orden);

        $detalle->load(['orden', 'servicio']);

        return response()->json([
            'message' => 'Servicio agregado a la orden correctamente',
            'data' => $detalle
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $detalle = DetalleServicioOrden::with(['orden', 'servicio'])->find($id);

        if (!$detalle) {
            return response()->json([
                'message' => 'Detalle de servicio no encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Detalle del servicio de la orden',
            'data' => $detalle
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $detalle = DetalleServicioOrden::find($id);

        if (!$detalle) {
            return response()->json([
                'message' => 'Detalle de servicio no encontrado'
            ], 404);
        }

        $request->validate([
            'id_servicio' => 'required|exists:servicios_adicionales,id_servicio',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $detalle->update([
            'id_servicio' => $request->id_servicio,
            'cantidad' => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
        ]);

        $this->recalcularTotalOrden($detalle->id_orden);

        $detalle->load(['orden', 'servicio']);

        return response()->json([
            'message' => 'Detalle de servicio actualizado correctamente',
            'data' => $detalle
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $detalle = DetalleServicioOrden::find($id);

        if (!$detalle) {
            return response()->json([
                'message' => 'Detalle de servicio no encontrado'
            ], 404);
        }

        $idOrden = $detalle->id_orden;
        $detalle->delete();

        $this->recalcularTotalOrden($idOrden);

        return response()->json([
            'message' => 'Detalle de servicio eliminado correctamente'
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
            ->sum(function ($item) {
                return $item->cantidad * $item->precio_unitario;
            });

        $orden->total_orden = ($orden->costo_mano_obra ?? 0) + $totalServicios;
        $orden->save();
    }
}