<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'correo',
        'nombres',
        'apellidos',
        'telefono',
        'usuario',
        'password_hash',
        'id_rol',
        'estado',
        'fecha_creacion',
        'ultima_modificacion'
    ];

    protected $hidden = [
        'password_hash'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    protected static function booted(): void
    {
        static::creating(function (Usuario $usuario) {
            if (!$usuario->fecha_creacion) {
                $usuario->fecha_creacion = Carbon::now();
            }
        });

        static::updating(function (Usuario $usuario) {
            $usuario->ultima_modificacion = Carbon::now();
        });
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
}