<?php

namespace App\Filament\Resources\ValeInventarioResource\Pages;

use App\Filament\Pages\DocumentosGenerados;
use App\Filament\Resources\ValeInventarioResource;
use App\Models\InventarioEquipo;
use App\Models\PersonalReportante;
use Filament\Resources\Pages\CreateRecord;

class CreateValeInventario extends CreateRecord
{
    protected static string $resource = ValeInventarioResource::class;

    protected function getRedirectUrl(): string
    {
        return DocumentosGenerados::getUrl();
    }

    public function getBreadcrumbs(): array
    {
        return [
            DocumentosGenerados::getUrl() => 'Documentos Generados',
            'Nuevo Vale',
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $equipoId = request()->query('equipo_id');
        if (!$equipoId) return;

        $equipo = InventarioEquipo::find($equipoId);
        if (!$equipo) return;

        // Buscar jefe del servicio del área para pre-rellenar quien_recibe
        $jefeNombre = null;
        if ($equipo->area) {
            $jefeNombre = PersonalReportante::where('es_jefe_servicio', true)
                ->where('area_jefe_servicio', $equipo->area)
                ->value('nombre');
        }

        $this->form->fill([
            'equipos' => [[
                'equipo_nombre'     => $equipo->equipo,
                'numero_inventario' => $equipo->numero_inventario,
                'numero_serie'      => $equipo->numero_serie,
                'marca'             => $equipo->marca,
                'modelo'            => $equipo->modelo,
                'unidad_medica'     => $equipo->unidad_medica,
            ]],
            'area'        => $equipo->area,
            'quien_recibe'=> $jefeNombre,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_nombre'] = auth()->user()?->name ?? 'Administrador';
        $data['estado']         = 'pendiente';

        $equipoId = request()->query('equipo_id');
        if ($equipoId) {
            $data['inventario_equipo_id'] = $equipoId;
        }

        // Sincronizar campos individuales desde el primer equipo del repeater
        $primer = $data['equipos'][0] ?? [];
        $data['equipo_nombre']     = $primer['equipo_nombre']     ?? null;
        $data['numero_inventario'] = $primer['numero_inventario'] ?? null;
        $data['numero_serie']      = $primer['numero_serie']      ?? null;
        $data['marca']             = $primer['marca']             ?? null;
        $data['modelo']            = $primer['modelo']            ?? null;
        $data['unidad_medica']     = $primer['unidad_medica']     ?? null;

        return $data;
    }
}
