<?php

namespace App\Filament\Resources\AuditoriaResource\Pages;

use App\Filament\Resources\AuditoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAuditorias extends ListRecords
{
    protected static string $resource = AuditoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Auditoría')
                ->icon('heroicon-o-plus'),
        ];
    }
}
