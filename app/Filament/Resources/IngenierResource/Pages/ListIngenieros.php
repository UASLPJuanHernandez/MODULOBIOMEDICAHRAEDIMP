<?php

namespace App\Filament\Resources\IngenierResource\Pages;

use App\Filament\Resources\IngenierResource;
use App\Models\Ingeniero;
use App\Models\ReportePizarron;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class ListIngenieros extends Page
{
    protected static string $resource = IngenierResource::class;

    protected static string $view = 'filament.ingenieros.lista-cards';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('estadisticas')
                ->label('Estadísticas del área')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->url(\App\Filament\Pages\EstadisticasIngenieria::getUrl()),

            Actions\Action::make('historial')
                ->label('Historial general')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray')
                ->modalHeading('Historial de actividades')
                ->modalContent(fn () => view(
                    'filament.ingenieros.historial-modal',
                    ['historial' => $this->getHistorial()]
                ))
                ->modalWidth('7xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar'),

            Actions\CreateAction::make()
                ->label('Nuevo ingeniero'),
        ];
    }

    public function getHistorial(): Collection
    {
        return ReportePizarron::with(['bitacora', 'firmaSolicitud'])
            ->whereNotNull('responsable')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                if ($r->concretado) {
                    $r->estado_actividad = 'concretado';
                } elseif ($r->bitacora) {
                    $r->estado_actividad = 'espera_firma';
                } else {
                    $r->estado_actividad = 'espera_envio';
                }
                return $r;
            });
    }

    public function getIngenieros()
    {
        return Ingeniero::orderBy('activo', 'desc')
            ->orderBy('nombre')
            ->get()
            ->map(function ($ing) {
                $ing->total_reportes   = ReportePizarron::where('responsable', $ing->nombre)->count();
                $ing->reportes_activos = ReportePizarron::where('responsable', $ing->nombre)
                    ->whereIn('estado', ['pendiente', 'en_curso'])
                    ->count();
                return $ing;
            });
    }
}
