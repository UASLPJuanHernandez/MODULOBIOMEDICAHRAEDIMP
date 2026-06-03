<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumible extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'marca',
        'referencia',
        'cantidad',
    ];
}
