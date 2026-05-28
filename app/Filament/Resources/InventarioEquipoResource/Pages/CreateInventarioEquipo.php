<?php

namespace App\Filament\Resources\InventarioEquipoResource\Pages;

use App\Filament\Resources\InventarioEquipoResource;
use App\Services\ValeEntregaService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateInventarioEquipo extends CreateRecord
{
    protected static string $resource = InventarioEquipoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    /**
     * Añade al formulario de creación una sección extra al final
     * con el toggle de vale de entrega. No se persiste en BD.
     */
    protected function getFormSchema(): array
    {
        return [
            ...InventarioEquipoResource::form($this->form)->getComponents(),

            Section::make('Vale de Entrega')
                ->description('¿Desea generar un vale de entrega para este equipo?')
                ->icon('heroicon-o-document-arrow-down')
                ->schema([
                    Toggle::make('generar_vale')
                        ->label('Generar vale de entrega al guardar')
                        ->helperText('Se descargará automáticamente el vale en formato Word (.docx) con los datos del equipo.')
                        ->default(false)
                        ->dehydrated(false),  // no se guarda en la base de datos
                ])
                ->collapsible()
                ->collapsed(false),
        ];
    }

    protected function afterCreate(): void
    {
        $vale = (new ValeEntregaService())->registrarEntrega($this->record);

        $notif = Notification::make()
            ->title('Equipo registrado')
            ->body('El vale de entrega está disponible en Documentos generados.')
            ->success()
            ->persistent()
            ->actions([
                NotificationAction::make('descargar')
                    ->label('Descargar vale')
                    ->url(route('inventario.vale.redescargar', $vale))
                    ->openUrlInNewTab()
                    ->button(),
            ]);

        if (!empty($this->data['generar_vale'])) {
            $this->js("window.open('" . addslashes(route('inventario.vale.redescargar', $vale)) . "', '_blank')");
        }

        $notif->send();
    }
}
