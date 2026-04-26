<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoOrden extends Model
{
    protected $table = 'pagos_orden';
    protected $primaryKey = 'id_pago_orden';
    public $timestamps = false;

    protected $fillable = [
        'id_orden',
        'monto_total',
        'observacion',
        'fecha_pago',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'id_orden', 'id_orden');
    }

    public function metodos()
    {
        return $this->hasMany(PagoMetodoOrden::class, 'id_pago_orden', 'id_pago_orden');
    }
}
