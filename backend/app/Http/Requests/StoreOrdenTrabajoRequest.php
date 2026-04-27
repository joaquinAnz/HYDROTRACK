<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdenTrabajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $modoAtencion = $this->input('modo_atencion', 'servicio_tecnico');
        $esSoloVenta = $modoAtencion === 'solo_venta';

        return [
            'modo_atencion' => 'nullable|in:servicio_tecnico,solo_venta',
            'id_cliente' => $esSoloVenta
                ? 'nullable|integer|exists:clientes,id_cliente'
                : 'required|integer|exists:clientes,id_cliente',
            'id_vehiculo' => $esSoloVenta
                ? 'nullable|integer|exists:vehiculos,id_vehiculo'
                : 'required|integer|exists:vehiculos,id_vehiculo',
            'id_tecnico' => 'nullable|integer|exists:usuarios,id_usuario',
            'id_usuario_registro' => 'required|integer|exists:usuarios,id_usuario',
            'descripcion_falla' => $esSoloVenta
                ? 'nullable|string|max:2000'
                : 'required|string|max:2000',
            'diagnostico' => 'nullable|string|max:2000',
            'observaciones' => 'nullable|string|max:2000',
            'costo_mano_obra' => 'nullable|numeric|min:0',
            'total_orden' => 'nullable|numeric|min:0',
            'id_estado' => $esSoloVenta
                ? 'nullable|integer|exists:estados_orden,id_estado'
                : 'required|integer|exists:estados_orden,id_estado',
            'fecha_salida' => 'nullable|date',
            'id_tipo_servicio' => 'required|integer|exists:tipos_servicio,id_tipo_servicio',
        ];
    }

    public function messages(): array
    {
        return [
            'id_cliente.required' => 'El cliente es obligatorio para servicio tecnico.',
            'id_cliente.exists' => 'El cliente seleccionado no existe.',
            'id_vehiculo.required' => 'El vehículo es obligatorio.',
            'id_vehiculo.exists' => 'El vehículo no existe.',
            'id_tecnico.exists' => 'El técnico no existe.',
            'id_usuario_registro.required' => 'El usuario que registra es obligatorio.',
            'id_usuario_registro.exists' => 'El usuario que registra no existe.',
            'descripcion_falla.required' => 'La descripción de la falla es obligatoria.',
            'id_estado.required' => 'El estado es obligatorio.',
            'id_estado.exists' => 'El estado no existe.',
        ];
    }
}