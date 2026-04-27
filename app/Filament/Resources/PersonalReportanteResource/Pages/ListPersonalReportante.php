<?php

namespace App\Filament\Resources\PersonalReportanteResource\Pages;

use App\Filament\Resources\PersonalReportanteResource;
use Filament\Resources\Pages\ListRecords;

class ListPersonalReportante extends ListRecords
{
    protected static string $resource = PersonalReportanteResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
