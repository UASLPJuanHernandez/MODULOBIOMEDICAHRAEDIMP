<?php

namespace App\Filament\Resources\AuditoriaResource\Pages;

use App\Filament\Resources\AuditoriaResource;
use App\Models\Auditoria;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Actions;

class PrevisualizarReporte extends Page
{
    use InteractsWithRecord;
    
    protected static string $resource = AuditoriaResource::class;

    protected static string $view = 'filament.pages.previsualizar-reporte';
    
    protected static ?string $title = 'Previsualización del Reporte';
    
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('descargar')
                ->label('Descargar Reporte PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): string => route('auditoria.reporte.pdf', ['auditoria' => $this->record->id]))
                ->openUrlInNewTab(),
            
            Actions\Action::make('volver')
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => AuditoriaResource::getUrl('view', ['record' => $this->record])),
        ];
    }
    
    public function getAuditoria()
    {
        return $this->record->load([
            'ubicacion',
            'usuario',
            'items.mobiliario.localizacion'
        ]);
    }
}
