<?php

namespace App\Filament\Resources\IngenierResource\Pages;

use App\Filament\Resources\IngenierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIngeniero extends EditRecord
{
    protected static string $resource = IngenierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
