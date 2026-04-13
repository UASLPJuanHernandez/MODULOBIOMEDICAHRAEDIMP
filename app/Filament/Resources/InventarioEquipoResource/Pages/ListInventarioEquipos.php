<?php

namespace App\Filament\Resources\InventarioEquipoResource\Pages;

use App\Exports\InventarioEquipoExport;
use App\Filament\Resources\InventarioEquipoResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListInventarioEquipos extends ListRecords
{
    protected static string $resource = InventarioEquipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_excel')
                ->label('Descargar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (): BinaryFileResponse {
                    $fecha = Carbon::now()->format('Y-m-d_H-i');
                    $nombre = "Inventario_Equipos_HRAEDIMP_{$fecha}.xlsx";
                    return Excel::download(new InventarioEquipoExport(), $nombre);
                }),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos'),
            'funcionamiento_completo' => Tab::make('En Funcionamiento')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function ($q) {
                    $q->where('estatus', 'like', '%COMPLETO%')
                      ->orWhere('estatus', 'like', '%FUNCIONANDO%');
                }))
                ->badge(fn () => \App\Models\InventarioEquipo::where(function ($q) {
                    $q->where('estatus', 'like', '%COMPLETO%')
                      ->orWhere('estatus', 'like', '%FUNCIONANDO%');
                })->count())
                ->badgeColor('success'),
            'parcialmente' => Tab::make('Funciona Parcialmente')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estatus', 'like', '%PARCIAL%'))
                ->badge(fn () => \App\Models\InventarioEquipo::where('estatus', 'like', '%PARCIAL%')->count())
                ->badgeColor('warning'),
            'fuera_servicio' => Tab::make('Fuera de Servicio')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function ($q) {
                    $q->where('estatus', 'like', '%FUERA%')
                      ->orWhere('estatus', 'like', '%DISFUNCIONAL%')
                      ->orWhere('estatus', 'like', '%NO FUNCIONA%');
                }))
                ->badge(fn () => \App\Models\InventarioEquipo::where(function ($q) {
                    $q->where('estatus', 'like', '%FUERA%')
                      ->orWhere('estatus', 'like', '%DISFUNCIONAL%')
                      ->orWhere('estatus', 'like', '%NO FUNCIONA%');
                })->count())
                ->badgeColor('danger'),
        ];
    }
}
