<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoMetodoOrden extends Model
{
    protected $table = 'pago_metodos_orden';
    protected $primaryKey = 'id_pago_metodo';
    public $timestamps = false;

    protected $fillable = [
        'id_pago_orden',
        'metodo',
        'monto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function pago()
    {
        return $this->belongsTo(PagoOrden::class, 'id_pago_orden', 'id_pago_orden');
    }
}
