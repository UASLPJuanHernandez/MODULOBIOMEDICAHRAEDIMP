<?php

namespace App\Filament\Resources\BitacoraReporteResource\Pages;

use App\Filament\Resources\BitacoraReporteResource;
use App\Services\BitacoraDocxService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBitacoraReporte extends EditRecord
{
    protected static string $resource = BitacoraReporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('descargar')
                ->label('Descargar DOCX')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $service = new BitacoraDocxService();
                    $path    = $service->generar($this->record);
                    return response()->download($path, 'Bitacora_' . $this->record->id . '.docx')
                        ->deleteFileAfterSend(true);
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Firmar')
            ->icon('heroicon-o-pencil-square');
    }

    protected function getRedirectUrl(): string
    {
        return BitacoraReporteResource::getUrl('firmar', ['record' => $this->record]);
    }
}
