<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrdenTrabajoRequest;
use App\Http\Requests\UpdateOrdenTrabajoRequest;
use App\Models\OrdenTrabajo;
use Illuminate\Http\JsonResponse;

class OrdenTrabajoController extends Controller
{
    public function index(): JsonResponse
    {
        $ordenes = OrdenTrabajo::with([
            'vehiculo',
            'tipoServicio', // 👈 NUEVO
            'tecnico',
            'usuarioRegistro',
            'estado',
            'detallesServicio.servicio'
        ])
        ->orderByDesc('id_orden')
        ->get();

        return response()->json([
            'message' => 'Lista de órdenes de trabajo',
            'data' => $ordenes
        ]);
    }

    public function store(StoreOrdenTrabajoRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['codigo_seguimiento'] = $this->generarCodigoSeguimiento();
        $data['fecha_ingreso'] = now();
        $data['costo_mano_obra'] = $data['costo_mano_obra'] ?? 0;
        $data['total_orden'] = $data['costo_mano_obra'];

        $orden = OrdenTrabajo::create($data);

        $orden->load([
            'vehiculo',
            'tipoServicio', // 👈 NUEVO
            'tecnico',
            'usuarioRegistro',
            'estado',
            'detallesServicio.servicio'
        ]);

        return response()->json([
            'message' => 'Orden de trabajo creada correctamente',
            'data' => $orden
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $orden = OrdenTrabajo::with([
            'vehiculo',
            'tipoServicio', // 👈 NUEVO
            'tecnico',
            'usuarioRegistro',
            'estado',
            'detallesServicio.servicio'
        ])->find($id);

        if (!$orden) {
            return response()->json([
                'message' => 'Orden de trabajo no encontrada'
            ], 404);
        }

        return response()->json([
            'message' => 'Detalle de la orden de trabajo',
            'data' => $orden
        ]);
    }

    public function update(UpdateOrdenTrabajoRequest $request, int $id): JsonResponse
    {
        $orden = OrdenTrabajo::find($id);

        if (!$orden) {
            return response()->json([
                'message' => 'Orden de trabajo no encontrada'
            ], 404);
        }

        $orden->update($request->validated());

        $orden->load([
            'vehiculo',
            'tipoServicio', // 👈 NUEVO
            'tecnico',
            'usuarioRegistro',
            'estado',
            'detallesServicio.servicio'
        ]);

        return response()->json([
            'message' => 'Orden de trabajo actualizada correctamente',
            'data' => $orden
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $orden = OrdenTrabajo::find($id);

        if (!$orden) {
            return response()->json([
                'message' => 'Orden de trabajo no encontrada'
            ], 404);
        }

        $orden->delete();

        return response()->json([
            'message' => 'Orden de trabajo eliminada correctamente'
        ]);
    }

    private function generarCodigoSeguimiento(): string
    {
        do {
            $codigo = 'OT-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (OrdenTrabajo::where('codigo_seguimiento', $codigo)->exists());

        return $codigo;
    }
}