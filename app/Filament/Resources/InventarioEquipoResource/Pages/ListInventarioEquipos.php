<?php

namespace App\Filament\Resources\InventarioEquipoResource\Pages;

use App\Filament\Resources\InventarioEquipoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListInventarioEquipos extends ListRecords
{
    protected static string $resource = InventarioEquipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
