<?php

namespace App\Filament\Resources\ConsumibleResource\Pages;

use App\Filament\Resources\ConsumibleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConsumible extends CreateRecord
{
    protected static string $resource = ConsumibleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
