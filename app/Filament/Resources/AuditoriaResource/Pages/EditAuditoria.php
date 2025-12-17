<?php

namespace App\Filament\Resources\AuditoriaResource\Pages;

use App\Filament\Resources\AuditoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAuditoria extends EditRecord
{
    protected static string $resource = AuditoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
