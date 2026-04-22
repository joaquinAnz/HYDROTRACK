<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoOrden extends Model
{
    protected $table = 'estados_orden';
    protected $primaryKey = 'id_estado';
    public $timestamps = false;
}