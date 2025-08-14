<?php

namespace App\Filament\Resources\MovimientoLoteResource\Pages;

use App\Filament\Resources\MovimientoLoteResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditMovimientoLote extends EditRecord
{
    protected static string $resource = MovimientoLoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
