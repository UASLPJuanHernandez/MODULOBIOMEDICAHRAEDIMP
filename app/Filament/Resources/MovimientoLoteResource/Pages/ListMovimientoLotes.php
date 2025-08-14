<?php

namespace App\Filament\Resources\MovimientoLoteResource\Pages;

use App\Filament\Resources\MovimientoLoteResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListMovimientoLotes extends ListRecords
{
    protected static string $resource = MovimientoLoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
