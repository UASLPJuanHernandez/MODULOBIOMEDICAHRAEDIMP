<?php

namespace App\Filament\Resources\InventarioEquipoResource\Pages;

use App\Filament\Resources\InventarioEquipoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventarioEquipo extends CreateRecord
{
    protected static string $resource = InventarioEquipoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
