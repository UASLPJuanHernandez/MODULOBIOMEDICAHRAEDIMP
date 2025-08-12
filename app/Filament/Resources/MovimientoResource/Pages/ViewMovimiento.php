<?php

namespace App\Filament\Resources\MovimientoResource\Pages;

use App\Filament\Resources\MovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Support\Enums\FontWeight;

class ViewMovimiento extends ViewRecord
{
    protected static string $resource = MovimientoResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información General del Movimiento')
                    ->description('Detalles principales del movimiento de mobiliario')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('numero_movimiento')
                                    ->label('Número de Movimiento')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->color('primary')
                                    ->copyable()
                                    ->copyMessage('Número de movimiento copiado')
                                    ->icon('heroicon-o-hashtag'),

                                TextEntry::make('fecha_movimiento')
                                    ->label('Fecha y Hora del Movimiento')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-calendar')
                                    ->color('success'),

                                TextEntry::make('usuario.name')
                                    ->label('Usuario Responsable')
                                    ->icon('heroicon-o-user')
                                    ->color('warning'),
                            ]),
                    ]),

                Section::make('Mobiliarios en el Movimiento')
                    ->description('Lista de mobiliarios trasladados')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        TextEntry::make('cantidad_mobiliarios')
                            ->label('Total de Mobiliarios')
                            ->badge()
                            ->color(fn (string $state): string => match ((int) $state) {
                                1 => 'gray',
                                2 => 'success', 
                                3 => 'warning',
                                4 => 'danger',
                                default => 'gray',
                            }),

                        RepeatableEntry::make('mobiliarios')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('numero_control')
                                            ->label('Código de Inventario')
                                            ->weight(FontWeight::Bold)
                                            ->color('primary')
                                            ->copyable()
                                            ->icon('heroicon-o-qr-code'),

                                        TextEntry::make('descripcion')
                                            ->label('Descripción')
                                            ->limit(30)
                                            ->tooltip(fn ($state) => $state),

                                        TextEntry::make('pivot.area_anterior_id')
                                            ->label('Ubicación Anterior')
                                            ->formatStateUsing(function ($state) {
                                                if (!$state) return 'Sin ubicación anterior';
                                                $localizacion = \App\Models\Localizacion::find($state);
                                                return $localizacion ? $localizacion->ubicacion_resumida : 'Ubicación no encontrada';
                                            })
                                            ->color('danger')
                                            ->icon('heroicon-o-arrow-left'),

                                        TextEntry::make('ubicacion_actual')
                                            ->label('Ubicación Actual')
                                            ->formatStateUsing(fn ($record) => $record->ubicacionReal()->ubicacion_resumida ?? 'Sin ubicación')
                                            ->color('success')
                                            ->icon('heroicon-o-arrow-right'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Detalles del Traslado')
                    ->description('Información sobre el destino y responsables')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('areaActual.ubicacion_resumida')
                                    ->label('Área de Destino')
                                    ->weight(FontWeight::Bold)
                                    ->color('success')
                                    ->icon('heroicon-o-building-office'),

                                TextEntry::make('vale_generado')
                                    ->label('Estado del Vale')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Vale Generado' : 'Pendiente de Vale')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle'),
                            ]),
                    ]),

                Section::make('Responsables')
                    ->description('Personas encargadas de la entrega y recepción')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('se_entrega_con')
                                    ->label('Se Entrega Con')
                                    ->icon('heroicon-o-hand-raised')
                                    ->color('warning'),

                                TextEntry::make('se_retira_con')
                                    ->label('Se Retira Con')
                                    ->icon('heroicon-o-hand-thumb-up')
                                    ->color('info'),
                            ]),
                    ]),

                Section::make('Observaciones')
                    ->description('Notas adicionales del movimiento')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        TextEntry::make('observacion')
                            ->label('')
                            ->placeholder('Sin observaciones registradas')
                            ->columnSpanFull()
                            ->prose(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => empty($record->observacion)),

                Section::make('Información del Sistema')
                    ->description('Metadatos del registro')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Creado el')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-clock'),

                                TextEntry::make('updated_at')
                                    ->label('Última actualización')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-arrow-path'),

                                TextEntry::make('id')
                                    ->label('ID del Sistema')
                                    ->icon('heroicon-o-identification'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generar_vale')
                ->label('Generar Vale')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->visible(fn ($record) => !$record->vale_generado)
                ->action(function ($record) {
                    $record->update(['vale_generado' => true]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Vale generado correctamente')
                        ->body('El vale para este movimiento ha sido generado exitosamente')
                        ->success()
                        ->send();
                        
                    return redirect()->to(MovimientoResource::getUrl('view', ['record' => $record]));
                }),
                
            Actions\EditAction::make()
                ->label('Editar Movimiento')
                ->icon('heroicon-o-pencil')
                ->color('warning'),
                
            Actions\DeleteAction::make()
                ->label('Eliminar')
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }
}
