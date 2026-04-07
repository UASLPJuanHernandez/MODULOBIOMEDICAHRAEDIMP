<?php

namespace App\Filament\Pages;

use App\Models\ReportePizarron;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.pizarron';

    protected static ?string $title = 'Pizarrón';

    protected static ?string $navigationLabel = 'Inicio';
    protected static ?int    $navigationSort  = -2;

    public function getHeading(): string
    {
        return '';
    }

    public static array $responsables = [
        'Ing. María',
        'Ing. Renata',
        'Ing. María José',
        'Ing. Ana Julia',
        'Ing. Daniela',
        'Ing. Flor',
        'Ing. Pedro',
        'Ing. Sergio',
        'Ing. José',
        'Ing. Juan Pablo',
    ];

    public function getWidgets(): array
    {
        return [];
    }

    public function getColumns(): int | array
    {
        return 1;
    }

    public function getReportes()
    {
        return ReportePizarron::activos()->orderBy('created_at', 'asc')->get();
    }

    public function toggleMinimizado(int $id): void
    {
        $reporte = ReportePizarron::find($id);
        $reporte->update(['minimizado' => !$reporte->minimizado]);
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
