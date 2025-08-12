<?php

namespace App\Filament\Resources\ValeResource\Pages;

use App\Filament\Resources\ValeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewVale extends ViewRecord
{
    protected static string $resource = ValeResource::class;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        // Cargar todas las relaciones necesarias
        $this->record->load([
            'mobiliarios.localizacion',
            'movimiento',
            'mobiliario'
        ]);
        
        $this->authorizeAccess();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('imprimir_vale')
                ->label('Imprimir Vale')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn (): string => route('vale.imprimir', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información del Vale')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('numero_vale_formateado')
                                    ->label('Número de Vale'),
                                Infolists\Components\TextEntry::make('tipo_vale')
                                    ->label('Tipo de Vale')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'entrega' => 'success',
                                        'retiro' => 'warning',
                                        'resguardo' => 'info',
                                        default => 'gray',
                                    }),
                                Infolists\Components\TextEntry::make('fecha_generacion')
                                    ->label('Fecha de Generación')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('movimiento.numero_movimiento')
                                    ->label('Movimiento Asociado')
                                    ->default('N/A'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Mobiliarios')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('mobiliarios')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('numero_control')
                                            ->label('Código'),
                                        Infolists\Components\TextEntry::make('descripcion')
                                            ->label('Descripción'),
                                        Infolists\Components\TextEntry::make('marca')
                                            ->label('Marca')
                                            ->default('N/A'),
                                        Infolists\Components\TextEntry::make('modelo')
                                            ->label('Modelo')
                                            ->default('N/A'),
                                        Infolists\Components\TextEntry::make('numero_serie')
                                            ->label('No. Serie')
                                            ->default('N/A'),
                                        Infolists\Components\TextEntry::make('localizacion.nombre')
                                            ->label('Ubicación')
                                            ->default('N/A'),
                                    ]),
                            ])
                            ->visible(fn ($record) => $record->mobiliarios->count() > 0),
                        
                        // Mostrar mobiliario individual si no hay múltiples
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('mobiliario.numero_control')
                                    ->label('Código'),
                                Infolists\Components\TextEntry::make('mobiliario.descripcion')
                                    ->label('Descripción'),
                                Infolists\Components\TextEntry::make('mobiliario.marca')
                                    ->label('Marca')
                                    ->default('N/A'),
                                Infolists\Components\TextEntry::make('mobiliario.modelo')
                                    ->label('Modelo')
                                    ->default('N/A'),
                                Infolists\Components\TextEntry::make('mobiliario.numero_serie')
                                    ->label('No. Serie')
                                    ->default('N/A'),
                                Infolists\Components\TextEntry::make('mobiliario.localizacion.nombre')
                                    ->label('Ubicación')
                                    ->default('N/A'),
                            ])
                            ->visible(fn ($record) => $record->mobiliarios->count() == 0 && $record->mobiliario_id),
                        
                        // Mensaje cuando no hay mobiliarios
                        Infolists\Components\TextEntry::make('sin_mobiliarios')
                            ->label('Estado')
                            ->formatStateUsing(fn () => 'No hay mobiliarios asociados a este vale')
                            ->visible(fn ($record) => $record->mobiliarios->count() == 0 && !$record->mobiliario_id),
                    ]),

                Infolists\Components\Section::make('Responsables')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('responsable_entrega')
                                    ->label('Responsable de Entrega'),
                                Infolists\Components\TextEntry::make('matricula_entrega')
                                    ->label('Matrícula (Entrega)')
                                    ->default('N/A'),
                                Infolists\Components\TextEntry::make('responsable_recibe')
                                    ->label('Responsable que Recibe'),
                                Infolists\Components\TextEntry::make('matricula_recibe')
                                    ->label('Matrícula (Recibe)')
                                    ->default('N/A'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Observaciones')
                    ->schema([
                        Infolists\Components\TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->default('Sin observaciones')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
