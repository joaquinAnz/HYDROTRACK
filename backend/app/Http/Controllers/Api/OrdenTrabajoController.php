<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrdenTrabajoRequest;
use App\Http\Requests\UpdateOrdenTrabajoRequest;
use App\Models\Cliente;
use App\Models\EstadoOrden;
use App\Models\OrdenTrabajo;
use App\Models\Vehiculo;
use Illuminate\Http\JsonResponse;

class OrdenTrabajoController extends Controller
{
    public function index(): JsonResponse
    {
        $ordenes = OrdenTrabajo::with([
            'vehiculo.cliente',
            'tipoServicio', // 👈 NUEVO
            'tecnico',
            'usuarioRegistro',
            'estado',
            'detallesServicio.servicio',
            'detallesRepuestos.repuesto',
            'pagos.metodos',
            'actualizaciones.usuario'
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
        $modoAtencion = $request->input('modo_atencion', 'servicio_tecnico');
        $esSoloVenta = $modoAtencion === 'solo_venta';

        if ($esSoloVenta) {
            $idClienteVenta = (int) ($data['id_cliente'] ?? $this->obtenerClienteGeneralId());
            $data['id_vehiculo'] = $this->obtenerVehiculoMostradorId($idClienteVenta);
            $data['descripcion_falla'] = $data['descripcion_falla'] ?? 'Venta de repuestos en mostrador';
            $data['diagnostico'] = $data['diagnostico'] ?? null;
            $data['costo_mano_obra'] = 0;
            $data['id_tecnico'] = null;

            if (empty($data['id_estado'])) {
                $data['id_estado'] = $this->obtenerEstadoPorDefectoParaVenta();
            }
        }

        $data['codigo_seguimiento'] = $this->generarCodigoSeguimiento();
        $data['fecha_ingreso'] = now();
        $data['costo_mano_obra'] = $data['costo_mano_obra'] ?? 0;
        $data['total_orden'] = $data['total_orden'] ?? $data['costo_mano_obra'];

        $orden = OrdenTrabajo::create($data);

        $vehiculo = Vehiculo::find($data['id_vehiculo'] ?? null);
        if (!$esSoloVenta && $vehiculo && $vehiculo->estado === 1) {
            // El vehículo permanece activo en la tabla de vehículos.
        }

        $orden->load([
            'vehiculo.cliente',
            'tipoServicio', // 👈 NUEVO
            'tecnico',
            'usuarioRegistro',
            'estado',
            'detallesServicio.servicio',
            'detallesRepuestos.repuesto',
            'pagos.metodos',
            'actualizaciones.usuario'
        ]);

        return response()->json([
            'message' => 'Orden de trabajo creada correctamente',
            'data' => $orden
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $orden = OrdenTrabajo::with([
            'vehiculo.cliente',
            'tipoServicio', // 👈 NUEVO
            'tecnico',
            'usuarioRegistro',
            'estado',
            'detallesServicio.servicio',
            'detallesRepuestos.repuesto',
            'pagos.metodos',
            'actualizaciones.usuario'
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
            'vehiculo.cliente',
            'tipoServicio', // 👈 NUEVO
            'tecnico',
            'usuarioRegistro',
            'estado',
            'detallesServicio.servicio',
            'detallesRepuestos.repuesto',
            'pagos.metodos',
            'actualizaciones.usuario'
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

    private function obtenerVehiculoMostradorId(int $idCliente): int
    {
        $placaVirtual = 'MST-' . $idCliente;

        $vehiculoMostrador = Vehiculo::firstOrCreate(
            ['placa' => $placaVirtual],
            [
                'id_cliente' => $idCliente,
                'descripcion' => 'Vehiculo virtual para ventas en mostrador',
                'estado' => 1,
                'marca' => 'N/A',
                'modelo' => 'N/A',
                'anio' => null,
            ]
        );

        return (int) $vehiculoMostrador->id_vehiculo;
    }

    private function obtenerClienteGeneralId(): int
    {
        $clienteGeneral = Cliente::firstOrCreate(
            ['carnet_identidad' => '0000000'],
            [
                'nombres' => 'CLIENTE',
                'apellidos' => 'GENERAL',
                'telefono' => '0000000',
                'estado' => true,
            ]
        );

        return (int) $clienteGeneral->id_cliente;
    }

    private function obtenerEstadoPorDefectoParaVenta(): int
    {
        $preferidos = ['FINALIZADO', 'EN PROCESO', 'PENDIENTE'];

        $estado = EstadoOrden::all()->first(function (EstadoOrden $item) use ($preferidos) {
            return in_array((string) $item->nombre, $preferidos, true);
        });

        if ($estado) {
            return (int) $estado->id_estado;
        }

        $estadoExistente = EstadoOrden::query()->first();
        if ($estadoExistente) {
            return (int) $estadoExistente->id_estado;
        }

        $nuevoEstado = new EstadoOrden();
        $nuevoEstado->nombre = 'PENDIENTE';
        $nuevoEstado->save();

        return (int) $nuevoEstado->id_estado;
    }
}