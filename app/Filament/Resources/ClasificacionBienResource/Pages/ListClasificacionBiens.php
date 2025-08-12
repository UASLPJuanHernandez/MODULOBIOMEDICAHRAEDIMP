<?php

namespace App\Filament\Resources\ClasificacionBienResource\Pages;

use App\Filament\Resources\ClasificacionBienResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClasificacionBiens extends ListRecords
{
    protected static string $resource = ClasificacionBienResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
