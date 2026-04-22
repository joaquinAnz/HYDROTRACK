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
        return [
            'id_vehiculo' => 'required|integer|exists:vehiculos,id_vehiculo',
            'id_tecnico' => 'nullable|integer|exists:usuarios,id_usuario',
            'id_usuario_registro' => 'required|integer|exists:usuarios,id_usuario',
            'descripcion_falla' => 'required|string|max:2000',
            'diagnostico' => 'nullable|string|max:2000',
            'observaciones' => 'nullable|string|max:2000',
            'costo_mano_obra' => 'nullable|numeric|min:0',
            'id_estado' => 'required|integer|exists:estados_orden,id_estado',
            'fecha_salida' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
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