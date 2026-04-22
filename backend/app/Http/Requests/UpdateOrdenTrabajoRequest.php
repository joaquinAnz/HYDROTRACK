<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrdenTrabajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_vehiculo' => 'sometimes|required|integer|exists:vehiculos,id_vehiculo',
            'id_tecnico' => 'nullable|integer|exists:usuarios,id_usuario',
            'descripcion_falla' => 'sometimes|required|string|max:2000',
            'diagnostico' => 'nullable|string|max:2000',
            'observaciones' => 'nullable|string|max:2000',
            'costo_mano_obra' => 'nullable|numeric|min:0',
            'id_estado' => 'sometimes|required|integer|exists:estados_orden,id_estado',
            'fecha_salida' => 'nullable|date',
            'total_orden' => 'nullable|numeric|min:0',
        ];
    }
}