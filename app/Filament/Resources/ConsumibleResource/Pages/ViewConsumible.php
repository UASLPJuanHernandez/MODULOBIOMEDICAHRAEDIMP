<?php

namespace App\Filament\Resources\ConsumibleResource\Pages;

use App\Filament\Resources\ConsumibleResource;
use App\Models\Consumible;
use App\Services\ValeEntregaService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotifAction;
use Filament\Resources\Pages\ViewRecord;

class ViewConsumible extends ViewRecord
{
    protected static string $resource = ConsumibleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('vale_entrega')
                ->label('Vale de Entrega')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->form([
                    Forms\Components\TextInput::make('quien_recibe')
                        ->label('Quién recibe')
                        ->maxLength(150),
                    Forms\Components\TextInput::make('cargo_recibe')
                        ->label('Cargo')
                        ->maxLength(100),
                    Forms\Components\TextInput::make('area')
                        ->label('Área / Servicio')
                        ->maxLength(150),
                ])
                ->modalHeading('Generar Vale de Entrega')
                ->modalDescription(fn () => "Consumible: {$this->record->nombre}")
                ->modalSubmitActionLabel('Generar vale')
                ->action(function (array $data): void {
                    $vale = (new ValeEntregaService())->registrarEntregaConsumible($this->record, $data);

                    Notification::make()
                        ->title('Vale de Entrega generado')
                        ->body('El vale está disponible en Documentos generados.')
                        ->success()
                        ->persistent()
                        ->actions([
                            NotifAction::make('descargar')
                                ->label('Descargar vale')
                                ->url(route('inventario.vale.redescargar', $vale))
                                ->openUrlInNewTab()
                                ->button(),
                        ])
                        ->send();
                }),
        ];
    }
}
