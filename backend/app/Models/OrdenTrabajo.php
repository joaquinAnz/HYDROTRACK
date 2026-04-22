<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenTrabajo extends Model
{
    protected $table = 'ordenes_trabajo';
    protected $primaryKey = 'id_orden';

    public $timestamps = false;

    protected $fillable = [
        'codigo_seguimiento',
        'id_vehiculo',
        'id_tecnico',
        'id_usuario_registro',
        'id_tipo_servicio',
        'fecha_ingreso',
        'fecha_salida',
        'descripcion_falla',
        'diagnostico',
        'observaciones',
        'costo_mano_obra',
        'id_estado',
        'total_orden',
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_salida' => 'datetime',
        'costo_mano_obra' => 'decimal:2',
        'total_orden' => 'decimal:2',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo', 'id_vehiculo');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'id_tecnico', 'id_usuario');
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_registro', 'id_usuario');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoOrden::class, 'id_estado', 'id_estado');
    }
    public function tipoServicio()
    {
        return $this->belongsTo(TipoServicio::class, 'id_tipo_servicio', 'id_tipo_servicio');
    }

    public function detallesServicio()
    {
        return $this->hasMany(DetalleServicioOrden::class, 'id_orden', 'id_orden');
    }
}