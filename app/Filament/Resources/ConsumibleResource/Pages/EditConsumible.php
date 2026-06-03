<?php

namespace App\Filament\Resources\ConsumibleResource\Pages;

use App\Filament\Resources\ConsumibleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConsumible extends EditRecord
{
    protected static string $resource = ConsumibleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
