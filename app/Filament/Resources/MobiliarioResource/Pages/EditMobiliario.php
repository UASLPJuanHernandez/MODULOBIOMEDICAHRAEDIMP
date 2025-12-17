<?php

namespace App\Filament\Resources\MobiliarioResource\Pages;

use App\Filament\Resources\MobiliarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditMobiliario extends EditRecord
{
    protected static string $resource = MobiliarioResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        // Verificar si el mobiliario tiene mantenimientos activos
        if ($this->record->tieneMantenimientosActivos()) {
            Notification::make()
                ->warning()
                ->title('⚠️ Equipo en Mantenimiento')
                ->body('Este equipo tiene un mantenimiento activo. No se puede editar hasta que el mantenimiento sea completado o rechazado.')
                ->persistent()
                ->send();

            // Redirigir a la vista de detalles
            $this->redirect(MobiliarioResource::getUrl('view', ['record' => $this->record]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->disabled(fn () => $this->record->tieneMantenimientosActivos())
                ->tooltip(fn () => $this->record->tieneMantenimientosActivos() 
                    ? '⚠️ No se puede eliminar un equipo en mantenimiento' 
                    : null),
        ];
    }
}
