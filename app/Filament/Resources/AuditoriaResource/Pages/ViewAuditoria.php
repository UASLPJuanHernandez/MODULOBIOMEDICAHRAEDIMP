<?php

namespace App\Filament\Resources\AuditoriaResource\Pages;

use App\Filament\Resources\AuditoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditoria extends ViewRecord
{
    protected static string $resource = AuditoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verReporte')
                ->label('Ver Reporte')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->visible(fn () => $this->record->estaCompletada())
                ->url(fn (): string => AuditoriaResource::getUrl('previsualizar', ['record' => $this->record])),
            
            Actions\EditAction::make()
                ->visible(fn () => $this->record->estaEnProgreso()),
        ];
    }
}
