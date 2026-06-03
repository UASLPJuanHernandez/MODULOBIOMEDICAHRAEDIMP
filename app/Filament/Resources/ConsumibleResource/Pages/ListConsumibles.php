<?php

namespace App\Filament\Resources\ConsumibleResource\Pages;

use App\Filament\Resources\ConsumibleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConsumibles extends ListRecords
{
    protected static string $resource = ConsumibleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('vale_entrega')
                ->label('Vale de Entrega')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->url(\App\Filament\Pages\ConsumibleVale::getUrl()),

            Actions\CreateAction::make()->label('Nuevo consumible'),
        ];
    }
}
