<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    public $timestamps = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'carnet_identidad',
        'fecha_registro',
        'ultima_modificacion',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    protected static function booted(): void
    {
        static::creating(function (Cliente $cliente) {
            if (!$cliente->fecha_registro) {
                $cliente->fecha_registro = Carbon::now();
            }

            $cliente->ultima_modificacion = Carbon::now();
        });

        static::updating(function (Cliente $cliente) {
            $cliente->ultima_modificacion = Carbon::now();
        });
    }
}
