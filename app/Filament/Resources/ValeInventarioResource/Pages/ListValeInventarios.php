<?php

namespace App\Filament\Resources\ValeInventarioResource\Pages;

use App\Filament\Resources\ValeInventarioResource;
use App\Models\ValeInventario;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListValeInventarios extends ListRecords
{
    protected static string $resource = ValeInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->badge(ValeInventario::count()),

            'entrega' => Tab::make('Vales de Entrega')
                ->modifyQueryUsing(fn ($query) => $query->where('tipo', 'entrega'))
                ->badge(ValeInventario::where('tipo', 'entrega')->count())
                ->badgeColor('success'),

            'retiro' => Tab::make('Vales de Retiro')
                ->modifyQueryUsing(fn ($query) => $query->where('tipo', 'retiro'))
                ->badge(ValeInventario::where('tipo', 'retiro')->count())
                ->badgeColor('danger'),
        ];
    }
}
