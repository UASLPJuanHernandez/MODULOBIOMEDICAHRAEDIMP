<?php

namespace App\Filament\Resources\EquipoBajaResource\Pages;

use App\Filament\Resources\EquipoBajaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEquipoBaja extends EditRecord
{
    protected static string $resource = EquipoBajaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
