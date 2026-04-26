<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenActualizacion extends Model
{
    protected $table = 'orden_actualizaciones';
    protected $primaryKey = 'id_actualizacion';
    public $timestamps = false;

    protected $fillable = [
        'id_orden',
        'id_usuario',
        'tipo',
        'descripcion',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'id_orden', 'id_orden');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
