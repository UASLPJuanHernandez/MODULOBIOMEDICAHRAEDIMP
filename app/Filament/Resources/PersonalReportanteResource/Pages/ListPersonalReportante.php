<?php

namespace App\Filament\Resources\PersonalReportanteResource\Pages;

use App\Filament\Resources\PersonalReportanteResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListPersonalReportante extends ListRecords
{
    protected static string $resource = PersonalReportanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('registrar_usuario')
                ->label('Registrar usuario')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->url(route('portal.registro')),
        ];
    }
}
