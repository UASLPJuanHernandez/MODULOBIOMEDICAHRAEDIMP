<?php

namespace App\Filament\Resources\EquipoBajaResource\Pages;

use App\Filament\Resources\EquipoBajaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipoBajas extends ListRecords
{
    protected static string $resource = EquipoBajaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
