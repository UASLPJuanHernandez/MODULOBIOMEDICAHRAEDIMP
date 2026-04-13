<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioEquipo extends Model
{
    use HasFactory;

    protected $table = 'inventario_equipos';

    protected $fillable = [
        'numero_inventario',
        'clues',
        'unidad_medica',
        'area',
        'ubicacion_especifica',
        'clave_cbsg',
        'equipo',
        'equipo_alternativo',
        'marca',
        'modelo',
        'numero_serie',
        'propiedad',
        'condiciones',
        'estatus',
        'causa_no_funcionamiento',
        'fecha_adquisicion',
        'anio_fabricacion',
        'requerimientos',
        'frecuencia_mantenimiento',
        'tipo_mantenimiento',
        'contrato_mantenimiento',
        'fin_vida_util',
        'garantia',
        'fin_garantia',
        'tiene_contrato',
        'numero_contrato',
        'proveedor_mantenimiento',
        'inicio_poliza',
        'fin_poliza',
        'costo_contrato',
        'cantidad_mp_anio',
        'ultimo_mp',
        'siguiente_mp',
        'observaciones',
    ];

    protected $casts = [
        'fecha_adquisicion' => 'date',
        'fin_garantia' => 'date',
        'inicio_poliza' => 'date',
        'fin_poliza' => 'date',
        'ultimo_mp' => 'date',
        'siguiente_mp' => 'date',
        'garantia' => 'boolean',
        'tiene_contrato' => 'boolean',
        'fin_vida_util' => 'boolean',
    ];

    // Scopes
    public function scopeEnFuncionamiento($query)
    {
        return $query->where('estatus', 'like', '%FUNCION%COMPLETO%')
                     ->orWhere('estatus', 'like', '%FUNCIONAMIENTO COMPLETO%');
    }

    public function scopeFueraDeServicio($query)
    {
        return $query->where('estatus', 'like', '%FUERA%');
    }

    public function scopePorArea($query, string $area)
    {
        return $query->where('area', $area);
    }

    public function scopePorEstatus($query, string $estatus)
    {
        return $query->where('estatus', 'like', "%{$estatus}%");
    }

    // Accessors
    public function getEstatusNormalizadoAttribute(): string
    {
        $estatus = strtoupper(trim($this->estatus ?? ''));
        if (str_contains($estatus, 'COMPLETO') || str_contains($estatus, 'FUNCIONANDO')) {
            return 'Funcionamiento Completo';
        }
        if (str_contains($estatus, 'PARCIAL')) {
            return 'Funciona Parcialmente';
        }
        if (str_contains($estatus, 'FUERA') || str_contains($estatus, 'DISFUNCIONAL') || str_contains($estatus, 'NO FUNCIONA')) {
            return 'Fuera de Servicio';
        }
        return $estatus ?: 'Sin Estatus';
    }

    public function getColorEstatusAttribute(): string
    {
        return match ($this->estatus_normalizado) {
            'Funcionamiento Completo' => 'success',
            'Funciona Parcialmente' => 'warning',
            'Fuera de Servicio' => 'danger',
            default => 'gray',
        };
    }
}
