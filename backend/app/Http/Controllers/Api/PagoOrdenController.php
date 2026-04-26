<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdenTrabajo;
use App\Models\PagoMetodoOrden;
use App\Models\PagoOrden;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoOrdenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_orden' => 'required|integer|exists:ordenes_trabajo,id_orden',
            'metodos' => 'required|array|min:1',
            'metodos.*.metodo' => 'required|string|in:efectivo,qr,tarjeta',
            'metodos.*.monto' => 'required|numeric|gt:0',
            'observacion' => 'nullable|string|max:2000',
        ]);

        $orden = OrdenTrabajo::find($request->id_orden);
        if (!$orden) {
            return response()->json([
                'message' => 'La orden no existe.',
            ], 404);
        }

        $montoTotalPago = collect($request->metodos)->sum(function (array $item) {
            return (float) ($item['monto'] ?? 0);
        });

        if ($montoTotalPago <= 0) {
            return response()->json([
                'message' => 'El monto total del pago debe ser mayor a 0.',
            ], 422);
        }

        $totalOrden = (float) ($orden->total_orden ?? 0);
        $totalPagadoActual = (float) ($orden->total_pagado ?? 0);
        $saldoPendiente = max(0, $totalOrden - $totalPagadoActual);

        if ($saldoPendiente <= 0) {
            return response()->json([
                'message' => 'La orden ya se encuentra pagada completamente.',
            ], 422);
        }

        if ($montoTotalPago > $saldoPendiente) {
            return response()->json([
                'message' => 'El pago excede el saldo pendiente de la orden.',
                'saldo_pendiente' => round($saldoPendiente, 2),
            ], 422);
        }

        $resultado = DB::transaction(function () use ($request, $orden, $montoTotalPago, $saldoPendiente, $totalPagadoActual) {
            $pago = PagoOrden::create([
                'id_orden' => $orden->id_orden,
                'monto_total' => $montoTotalPago,
                'observacion' => $request->observacion,
            ]);

            foreach ($request->metodos as $metodo) {
                PagoMetodoOrden::create([
                    'id_pago_orden' => $pago->id_pago_orden,
                    'metodo' => strtolower((string) $metodo['metodo']),
                    'monto' => $metodo['monto'],
                ]);
            }

            $nuevoTotalPagado = $totalPagadoActual + $montoTotalPago;
            $esPagoCompleto = $nuevoTotalPagado >= ((float) $orden->total_orden - 0.00001);

            $orden->update([
                'total_pagado' => $nuevoTotalPagado,
                'pago_completo' => $esPagoCompleto,
            ]);

            $pago->load('metodos');

            return [
                'pago' => $pago,
                'es_pago_completo' => $esPagoCompleto,
                'saldo_restante' => max(0, round($saldoPendiente - $montoTotalPago, 2)),
            ];
        });

        return response()->json([
            'message' => $resultado['es_pago_completo']
                ? 'Pago de orden completado y registrado correctamente.'
                : 'Adelanto de orden registrado correctamente.',
            'data' => $resultado['pago'],
            'es_pago_completo' => $resultado['es_pago_completo'],
            'saldo_restante' => $resultado['saldo_restante'],
        ], 201);
    }

    public function indexByOrden(int $idOrden): JsonResponse
    {
        $orden = OrdenTrabajo::find($idOrden);

        if (!$orden) {
            return response()->json([
                'message' => 'La orden no existe.',
            ], 404);
        }

        $pagos = PagoOrden::with('metodos')
            ->where('id_orden', $idOrden)
            ->orderByDesc('id_pago_orden')
            ->get();

        return response()->json([
            'message' => 'Pagos de la orden obtenidos correctamente.',
            'data' => $pagos,
        ]);
    }

    public function update(Request $request, int $idPago): JsonResponse
    {
        $pago = PagoOrden::with('orden')->find($idPago);

        if (!$pago) {
            return response()->json([
                'message' => 'Pago no encontrado.',
            ], 404);
        }

        $request->validate([
            'metodos' => 'required|array|min:1',
            'metodos.*.metodo' => 'required|string|in:efectivo,qr,tarjeta',
            'metodos.*.monto' => 'required|numeric|gt:0',
            'observacion' => 'nullable|string|max:2000',
        ]);

        $montoActualizado = collect($request->metodos)->sum(fn (array $item) => (float) ($item['monto'] ?? 0));

        if ($montoActualizado <= 0) {
            return response()->json([
                'message' => 'El monto total del pago debe ser mayor a 0.',
            ], 422);
        }

        $orden = $pago->orden;
        if (!$orden) {
            return response()->json([
                'message' => 'La orden asociada no existe.',
            ], 404);
        }

        DB::transaction(function () use ($request, $pago, $montoActualizado, $orden) {
            $pago->update([
                'monto_total' => $montoActualizado,
                'observacion' => $request->observacion,
            ]);

            PagoMetodoOrden::where('id_pago_orden', $pago->id_pago_orden)->delete();

            foreach ($request->metodos as $metodo) {
                PagoMetodoOrden::create([
                    'id_pago_orden' => $pago->id_pago_orden,
                    'metodo' => strtolower((string) $metodo['metodo']),
                    'monto' => $metodo['monto'],
                ]);
            }

            $this->recalcularPagoOrden($orden);
        });

        $pago->load('metodos');
        $orden->refresh();

        return response()->json([
            'message' => 'Pago actualizado correctamente.',
            'data' => $pago,
            'orden' => [
                'id_orden' => $orden->id_orden,
                'total_orden' => $orden->total_orden,
                'total_pagado' => $orden->total_pagado,
                'pago_completo' => $orden->pago_completo,
            ],
        ]);
    }

    private function recalcularPagoOrden(OrdenTrabajo $orden): void
    {
        $totalPagado = (float) PagoOrden::where('id_orden', $orden->id_orden)->sum('monto_total');
        $totalOrden = (float) ($orden->total_orden ?? 0);

        $orden->update([
            'total_pagado' => $totalPagado,
            'pago_completo' => $totalOrden > 0 && $totalPagado >= $totalOrden,
        ]);
    }
}
