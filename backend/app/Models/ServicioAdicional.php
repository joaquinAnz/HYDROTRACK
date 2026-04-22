<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioAdicional extends Model
{
    protected $table = 'servicios_adicionales';
    protected $primaryKey = 'id_servicio';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'costo_base',
        'estado',
    ];

    protected $casts = [
        'costo_base' => 'decimal:2',
        'estado' => 'boolean',
    ];
}