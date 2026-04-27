<?php

namespace App\Filament\Resources\MobiliarioResource\Pages;

use App\Filament\Resources\MobiliarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMobiliario extends CreateRecord
{
    protected static string $resource = MobiliarioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Mobiliario creado exitosamente';
    }
}
