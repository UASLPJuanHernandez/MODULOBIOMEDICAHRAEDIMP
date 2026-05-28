<?php

namespace App\Observers;

use App\Models\InventarioEquipo;
use App\Models\InventarioEquipoHistorial;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class InventarioEquipoObserver
{
    /** Mapa de campo => etiqueta legible en español */
    protected static array $etiquetas = [
        'numero_inventario'       => 'No. de Inventario',
        'clues'                   => 'CLUES',
        'unidad_medica'           => 'Unidad Médica',
        'area'                    => 'Área / Especialidad',
        'ubicacion_especifica'    => 'Ubicación Específica',
        'clave_cbsg'              => 'Clave CSG',
        'equipo'                  => 'Equipo',
        'equipo_alternativo'      => 'Equipo Alternativo',
        'marca'                   => 'Marca',
        'modelo'                  => 'Modelo',
        'numero_serie'            => 'Número de Serie',
        'propiedad'               => 'Propiedad',
        'condiciones'             => 'Condiciones',
        'estatus'                 => 'Estatus',
        'causa_no_funcionamiento' => 'Causa de No Funcionamiento',
        'fecha_adquisicion'       => 'Fecha de Adquisición',
        'anio_fabricacion'        => 'Año de Fabricación',
        'requerimientos'          => 'Requerimientos',
        'frecuencia_mantenimiento'=> 'Frecuencia de Mantenimiento',
        'tipo_mantenimiento'      => 'Tipo de Mantenimiento',
        'contrato_mantenimiento'  => 'Contrato de Mantenimiento',
        'fin_vida_util'           => 'Fin de Vida Útil (EOL)',
        'garantia'                => 'Garantía',
        'fin_garantia'            => 'Fin de Garantía',
        'tiene_contrato'          => 'Tiene Contrato',
        'numero_contrato'         => 'No. de Contrato',
        'proveedor_mantenimiento' => 'Proveedor de Mantenimiento',
        'inicio_poliza'           => 'Inicio de Póliza',
        'fin_poliza'              => 'Fin de Póliza',
        'costo_contrato'          => 'Costo de Contrato',
        'cantidad_mp_anio'        => 'Cantidad de MP al Año',
        'ultimo_mp'               => 'Último MP',
        'siguiente_mp'            => 'Siguiente MP',
        'observaciones'           => 'Observaciones',
    ];

    /** Campos a ignorar en el historial (técnicos / timestamps) */
    protected static array $ignorados = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    public function created(InventarioEquipo $equipo): void
    {
        $this->registrar($equipo, 'creado', [], 'Registro creado en el inventario');
    }

    public function updated(InventarioEquipo $equipo): void
    {
        $cambios = [];

        foreach ($equipo->getChanges() as $campo => $nuevoValor) {
            if (in_array($campo, self::$ignorados)) {
                continue;
            }

            $anteriorValor = $equipo->getOriginal($campo);

            $cambios[] = [
                'campo'    => $campo,
                'etiqueta' => self::$etiquetas[$campo] ?? $campo,
                'anterior' => $this->formatearValor($campo, $anteriorValor),
                'nuevo'    => $this->formatearValor($campo, $nuevoValor),
            ];
        }

        if (empty($cambios)) {
            return;
        }

        $totalCambios = count($cambios);
        $descripcion = $totalCambios === 1
            ? 'Modificado: ' . $cambios[0]['etiqueta']
            : "Modificados {$totalCambios} campos: " . implode(', ', array_column($cambios, 'etiqueta'));

        $this->registrar($equipo, 'actualizado', $cambios, $descripcion);
    }

    public function deleting(InventarioEquipo $equipo): void
    {
        // Se registra ANTES de borrar (evento "deleting") para que la FK todavía
        // sea válida. La migración 2026_04_14_000003 cambió la FK a nullOnDelete,
        // por lo que el registro persiste con inventario_equipo_id = null.
        $nombre = trim(($equipo->numero_inventario ? '[' . $equipo->numero_inventario . '] ' : '') . ($equipo->equipo ?? ''));
        $this->registrar($equipo, 'eliminado', [], 'Registro eliminado: ' . $nombre);
    }

    protected function registrar(
        InventarioEquipo $equipo,
        string $tipoEvento,
        array $cambios,
        string $descripcion
    ): void {
        $usuario = Auth::user();

        InventarioEquipoHistorial::create([
            'inventario_equipo_id' => $equipo->id,
            'tipo_evento'          => $tipoEvento,
            'cambios'              => $cambios ?: null,
            'descripcion'          => $descripcion,
            'usuario_id'           => $usuario?->id,
            'usuario_nombre'       => $usuario?->name ?? 'Sistema',
            'ip_address'           => Request::ip(),
        ]);

        $equipoLabel = trim(($equipo->numero_inventario ? "[{$equipo->numero_inventario}] " : '') . ($equipo->equipo ?? ''));
        AuditService::log('equipo', "{$descripcion}: {$equipoLabel}", [
            'actor_tipo'     => 'admin',
            'actor_id'       => $usuario?->id ?? 0,
            'actor_nombre'   => $usuario?->name ?? 'Sistema',
            'documento_tipo' => 'equipo',
            'documento_id'   => $equipo->id,
            'metadata'       => ['tipo_evento' => $tipoEvento, 'area' => $equipo->area],
        ]);
    }

    protected function formatearValor(string $campo, mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '(vacío)';
        }

        // Booleanos
        $camposBooleanos = ['garantia', 'tiene_contrato', 'fin_vida_util'];
        if (in_array($campo, $camposBooleanos)) {
            return $valor ? 'Sí' : 'No';
        }

        return (string) $valor;
    }
}
