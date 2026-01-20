<?php

namespace App\Filament\Resources\MobiliarioResource\Pages;

use App\Filament\Resources\MobiliarioResource;
use App\Filament\Imports\MobiliarioImporter;
use App\Filament\Imports\MobiliarioLegacyImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMobiliarios extends ListRecords
{
    protected static string $resource = MobiliarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->importer(MobiliarioLegacyImporter::class)
                ->label('Importar desde Sistema Anterior')
                ->color('warning')
                ->icon('heroicon-o-arrow-up-tray')
                ->csvDelimiter(',')
                ->maxRows(5000),
            Actions\CreateAction::make(),
        ];
    }
}
