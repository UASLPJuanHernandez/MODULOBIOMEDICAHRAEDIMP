<?php

namespace App\Filament\Resources\MantenimientoResource\Pages;

use App\Filament\Resources\MantenimientoResource;
use App\Models\Mantenimiento;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Actions;

class PrevisualizarVale extends Page
{
    use InteractsWithRecord;
    
    protected static string $resource = MantenimientoResource::class;

    protected static string $view = 'filament.pages.previsualizar-vale-mantenimiento';
    
    protected static ?string $title = 'Previsualización del Vale de Mantenimiento';
    
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('descargar')
                ->label('Descargar Vale PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): string => route('mantenimiento.vale.pdf', ['mantenimiento' => $this->record]))
                ->openUrlInNewTab(),
            
            Actions\Action::make('volver')
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => MantenimientoResource::getUrl('index')),
        ];
    }
    
    public function getMantenimiento()
    {
        return $this->record->load([
            'mobiliario',
            'usuarioSolicitante',
            'usuarioMantenimiento'
        ]);
    }
    
    public function getUbicacion()
    {
        return $this->record->mobiliario->ubicacionReal();
    }
}
