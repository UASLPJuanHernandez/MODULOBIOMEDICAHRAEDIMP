<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoCalendario extends Model
{
    protected $table = 'eventos_calendario';

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'todo_el_dia',
        'ubicacion',
        'responsable',
        'tipo',
        'estado',
        'prioridad',
        'color',
        'notas',
        'recurrencia',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
        'todo_el_dia'  => 'boolean',
    ];
}
