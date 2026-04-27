<?php

namespace App\Filament\Resources\BitacoraReporteResource\Pages;

use App\Filament\Resources\BitacoraReporteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBitacorasReporte extends ListRecords
{
    protected static string $resource = BitacoraReporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
