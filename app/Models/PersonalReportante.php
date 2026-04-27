<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class PersonalReportante extends Authenticatable
{
    protected $table = 'personal_reportante';

    protected $fillable = [
        'nombre',
        'numero_empleado',
        'servicio',
        'password',
        'estado',
        'firma',
        'es_jefe_servicio',
        'area_jefe_servicio',
        'horario_inicio',
        'horario_fin',
    ];

    protected $casts = [
        'es_jefe_servicio' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function estaAprobado(): bool
    {
        return $this->estado === 'aprobado';
    }
}
