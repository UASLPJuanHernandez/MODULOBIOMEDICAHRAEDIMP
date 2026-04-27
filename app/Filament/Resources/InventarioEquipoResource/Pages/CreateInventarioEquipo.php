<?php

namespace App\Filament\Resources\InventarioEquipoResource\Pages;

use App\Filament\Resources\InventarioEquipoResource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
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
        if (!empty($this->data['generar_vale'])) {
            $url = route('inventario.equipo.vale-entrega', $this->record);
            // Abrir la descarga en nueva pestaña; la navegación normal continúa
            $this->js("window.open('" . addslashes($url) . "', '_blank')");
        }
    }
}
