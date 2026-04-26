<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleRepuestoOrden extends Model
{
    protected $table = 'detalle_repuesto_orden';
    protected $primaryKey = 'id_detalle_repuesto';
    public $timestamps = false;

    protected $fillable = [
        'id_orden',
        'id_repuesto',
        'cantidad',
        'precio_unitario',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'id_orden', 'id_orden');
    }

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class, 'id_repuesto', 'id_repuesto');
    }
}
