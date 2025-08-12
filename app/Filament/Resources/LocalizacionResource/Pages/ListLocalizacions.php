<?php

namespace App\Filament\Resources\LocalizacionResource\Pages;

use App\Filament\Resources\LocalizacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLocalizacions extends ListRecords
{
    protected static string $resource = LocalizacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
