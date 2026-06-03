<?php

namespace App\Filament\Pages;

use App\Models\Ingeniero;
use App\Models\ReportePizarron;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.pizarron';

    protected static ?string $title = 'Pizarrón';

    protected static ?string $navigationLabel = 'Inicio';
    protected static ?int    $navigationSort  = -3;

    // Campos del formulario nuevo reporte
    public string $nr_titulo              = '';
    public string $nr_descripcion         = '';
    public string $nr_equipo              = '';
    public string $nr_ubicacion           = '';
    public string $nr_prioridad           = 'media';
    public string $nr_responsable         = '';
    public string $nr_reportante_nombre   = '';
    public string $nr_reportante_servicio = '';

    public function getHeading(): string
    {
        return '';
    }

    public function getWidgets(): array
    {
        return [];
    }

    public function getColumns(): int | array
    {
        return 1;
    }

    public function crearReporte(): void
    {
        $this->validate([
            'nr_titulo'      => 'required|max:255',
            'nr_descripcion' => 'required',
            'nr_prioridad'   => 'required',
        ], [
            'nr_titulo.required'      => 'El título es obligatorio.',
            'nr_descripcion.required' => 'La descripción es obligatoria.',
        ]);

        ReportePizarron::create([
            'titulo'               => $this->nr_titulo,
            'descripcion'          => $this->nr_descripcion,
            'descripcion_original' => $this->nr_descripcion,
            'equipo'               => $this->nr_equipo              ?: null,
            'ubicacion'            => $this->nr_ubicacion           ?: null,
            'prioridad'            => $this->nr_prioridad,
            'responsable'          => $this->nr_responsable         ?: null,
            'reportante_nombre'    => $this->nr_reportante_nombre   ?: null,
            'reportante_servicio'  => $this->nr_reportante_servicio ?: null,
            'estado'               => 'pendiente',
        ]);

        $this->reset([
            'nr_titulo', 'nr_descripcion', 'nr_equipo', 'nr_ubicacion',
            'nr_responsable', 'nr_reportante_nombre', 'nr_reportante_servicio',
        ]);
        $this->nr_prioridad = 'media';

        $this->dispatch('reporte-creado');
    }

    public static function getResponsables(): array
    {
        return Ingeniero::activos()
            ->orderBy('nombre')
            ->pluck('nombre')
            ->toArray();
    }

    public function getReportes()
    {
        return ReportePizarron::activos()
            ->orderByRaw("CASE prioridad WHEN 'urgencia' THEN 1 WHEN 'moderada' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function toggleMinimizado(int $id): void
    {
        ReportePizarron::find($id)->update(['minimizado' => !ReportePizarron::find($id)->minimizado]);
    }

    public function cambiarEstado(int $id, string $estado): void
    {
        ReportePizarron::find($id)->update(['estado' => $estado]);
    }

    public function asignarResponsable(int $id, string $responsable): void
    {
        ReportePizarron::find($id)->update(['responsable' => $responsable]);
    }

    public function cambiarPrioridad(int $id, string $prioridad): void
    {
        ReportePizarron::find($id)->update(['prioridad' => $prioridad]);
    }

    public function eliminar(int $id): void
    {
        ReportePizarron::find($id)->delete();
    }
}
