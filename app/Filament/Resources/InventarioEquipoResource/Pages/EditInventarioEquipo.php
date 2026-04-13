<?php

namespace App\Filament\Resources\InventarioEquipoResource\Pages;

use App\Filament\Resources\InventarioEquipoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventarioEquipo extends EditRecord
{
    protected static string $resource = InventarioEquipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->hasRole('Administrador') ?? false),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
