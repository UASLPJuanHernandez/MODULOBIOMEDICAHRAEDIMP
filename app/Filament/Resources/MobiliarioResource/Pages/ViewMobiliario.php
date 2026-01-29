<?php

namespace App\Filament\Resources\MobiliarioResource\Pages;

use App\Filament\Resources\MobiliarioResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Support\Enums\FontWeight;

class ViewMobiliario extends ViewRecord
{
    protected static string $resource = MobiliarioResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Cargar las relaciones necesarias
        $this->record->load(['vales', 'valesMultiples', 'mantenimientos']);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información General')
                    ->description('Datos principales del mobiliario')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('numero_control')
                                    ->label('Número de Control')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->color('primary')
                                    ->copyable()
                                    ->copyMessage('Número de control copiado')
                                    ->icon('heroicon-o-qr-code'),

                                TextEntry::make('descripcion')
                                    ->label('Descripción')
                                    ->weight(FontWeight::Medium)
                                    ->columnSpan(2)
                                    ->icon('heroicon-o-document-text'),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('marca')
                                    ->label('Marca')
                                    ->placeholder('No especificado')
                                    ->icon('heroicon-o-tag'),

                                TextEntry::make('modelo')
                                    ->label('Modelo')
                                    ->placeholder('No especificado')
                                    ->icon('heroicon-o-cpu-chip'),

                                TextEntry::make('numero_serie')
                                    ->label('Número de Serie')
                                    ->placeholder('No especificado')
                                    ->copyable()
                                    ->icon('heroicon-o-hashtag'),

                                TextEntry::make('estado_mobiliario')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Nuevo' => 'success',
                                        'Usado' => 'info',
                                        'Baja' => 'danger',
                                        'Restaurado' => 'warning',
                                        default => 'gray',
                                    })
                                    ->icon('heroicon-o-shield-check'),
                            ]),
                    ]),

                Section::make('Foto y Código QR')
                    ->description('Imagen del mobiliario y código QR para identificación')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('foto')
                                    ->label('Foto del Mobiliario')
                                    ->circular(false)
                                    ->height(200)
                                    ->width(200)
                                    ->visibility('public')
                                    ->defaultImageUrl('https://via.placeholder.com/200x200/e5e7eb/6b7280?text=Sin+Foto')
                                    ->columnSpan(1),
                                    
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('qr_code')
                                            ->label('Código QR')
                                            ->formatStateUsing(function ($record) {
                                                return '<img src="' . $record->getQrDataUri() . '" alt="QR Code" class="mx-auto" style="width: 200px; height: 200px;" />';
                                            })
                                            ->html(),
                                            
                                        Infolists\Components\Actions::make([
                                            Infolists\Components\Actions\Action::make('descargarQR')
                                                ->label('Descargar QR')
                                                ->icon('heroicon-o-arrow-down-tray')
                                                ->color('info')
                                                ->action(function ($record) {
                                                    $qrCode = $record->generarQR();
                                                    
                                                    return response()->streamDownload(function () use ($qrCode) {
                                                        echo $qrCode;
                                                    }, 'QR_' . $record->numero_control . '.png', [
                                                        'Content-Type' => 'image/png',
                                                    ]);
                                                })
                                                ->tooltip('Descargar código QR en formato PNG'),
                                        ]),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Clasificación y Tipo')
                    ->description('Categorización del mobiliario')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tipoMobiliario.tipo')
                                    ->label('Tipo de Mobiliario')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-folder'),

                                TextEntry::make('tipoMobiliario.categoria')
                                    ->label('Categoría')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-o-bookmark'),

                                TextEntry::make('clasificacionBien.descripcion')
                                    ->label('Clasificación')
                                    ->placeholder('Sin clasificación')
                                    ->icon('heroicon-o-clipboard-document-list'),
                            ]),
                    ]),

                Section::make('Ubicación y Localización')
                    ->description('Dónde se encuentra actualmente el mobiliario')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('localizacion.ubicacion_resumida')
                                    ->label('Ubicación Actual')
                                    ->weight(FontWeight::Bold)
                                    ->color('success')
                                    ->icon('heroicon-o-building-office'),

                                TextEntry::make('historial_ubicacion')
                                    ->label('Historial de Ubicación')
                                    ->formatStateUsing(fn ($record) => $record->resumenUbicacion())
                                    ->color('gray')
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ]),

                Section::make('Información Económica')
                    ->description('Datos sobre adquisición y valor')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('precio')
                                    ->label('Precio de Adquisición')
                                    ->money('MXN')
                                    ->icon('heroicon-o-banknotes'),

                                TextEntry::make('metodo_adquisicion')
                                    ->label('Método de Adquisición')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Compra' => 'success',
                                        'Donación' => 'info',
                                        'Comodato' => 'warning',
                                        default => 'gray',
                                    })
                                    ->icon('heroicon-o-shopping-cart'),

                                TextEntry::make('donante')
                                    ->label('Donante')
                                    ->placeholder('Sin especificar')
                                    ->icon('heroicon-o-user-circle')
                                    ->visible(fn ($record) => $record->metodo_adquisicion === 'Donación'),

                                TextEntry::make('proveedor.nombre_proveedor')
                                    ->label('Proveedor')
                                    ->placeholder('Sin proveedor registrado')
                                    ->icon('heroicon-o-building-storefront'),
                            ]),
                    ]),

                Section::make('Estado y Folio')
                    ->description('Información sobre el estado del mobiliario')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('dado_de_baja')
                                    ->label('Estado del Mobiliario')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Dado de Baja' : 'Activo')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'),

                                TextEntry::make('numero_folio')
                                    ->label('Número de Folio')
                                    ->placeholder('Sin folio asignado')
                                    ->visible(fn ($record) => $record->tiene_folio)
                                    ->copyable()
                                    ->icon('heroicon-o-document-duplicate'),
                            ]),
                    ]),

                Section::make('Historial de Mantenimientos')
                    ->description('Mantenimientos realizados a este equipo')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        TextEntry::make('mantenimientos_stats')
                            ->label('Estadísticas')
                            ->formatStateUsing(function ($record) {
                                $total = $record->totalMantenimientos();
                                $pendientes = $record->mantenimientosPendientes();
                                $aceptados = $record->mantenimientosAceptados();
                                $completados = $record->mantenimientos()->where('estado', 'completados')->count();
                                
                                if ($total === 0) {
                                    return 'No hay mantenimientos registrados';
                                }
                                
                                return "Total: {$total} | Pendientes: {$pendientes} | Aceptados: {$aceptados} | Completados: {$completados}";
                            })
                            ->icon('heroicon-o-chart-bar'),
                            
                        TextEntry::make('ultimo_mantenimiento_info')
                            ->label('Último Mantenimiento')
                            ->formatStateUsing(function ($record) {
                                $ultimo = $record->ultimoMantenimiento;
                                
                                if (!$ultimo) {
                                    return 'Nunca se ha realizado mantenimiento';
                                }
                                
                                $dias = $record->diasSinMantenimiento();
                                return "Completado hace {$dias} días ({$ultimo->fecha_completado->format('d/m/Y')})";
                            })
                            ->icon('heroicon-o-clock'),
                            
                        TextEntry::make('mantenimientos_activos')
                            ->label('Estado Actual')
                            ->formatStateUsing(function ($record) {
                                if ($record->tieneMantenimientosActivos()) {
                                    return 'Tiene mantenimientos en proceso';
                                }
                                return 'Sin mantenimientos pendientes';
                            })
                            ->badge()
                            ->color(function ($record) {
                                return $record->tieneMantenimientosActivos() ? 'warning' : 'success';
                            })
                            ->icon(function ($record) {
                                return $record->tieneMantenimientosActivos() ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle';
                            }),

                        RepeatableEntry::make('mantenimientosOrdenados')
                            ->label('Historial Detallado')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('folio_vale')
                                            ->label('Folio Vale')
                                            ->placeholder('Sin folio')
                                            ->copyable()
                                            ->url(fn ($state, $record) => 
                                                $record->folio_vale ? route('mantenimiento.vale.pdf', $record) : null
                                            )
                                            ->openUrlInNewTab()
                                            ->icon('heroicon-o-document-text'),
                                            
                                        TextEntry::make('estado')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'pendiente' => 'warning',
                                                'aceptado' => 'success', 
                                                'completado' => 'info',
                                                'rechazado' => 'danger',
                                                default => 'gray',
                                            }),
                                            
                                        TextEntry::make('tipo_mantenimiento')
                                            ->label('Tipo')
                                            ->formatStateUsing(fn (string $state): string => 
                                                $state === 'mantenimiento' ? 'Interno' : 'Proveedor'
                                            )
                                            ->badge()
                                            ->color(fn (string $state): string => 
                                                $state === 'mantenimiento' ? 'info' : 'secondary'
                                            ),
                                    ]),
                                    
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('motivo')
                                            ->label('Motivo')
                                            ->limit(100),
                                            
                                        TextEntry::make('observaciones')
                                            ->label('Observaciones')
                                            ->placeholder('Sin observaciones')
                                            ->limit(100),
                                    ]),
                                    
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('fecha_programada')
                                            ->label('Programado')
                                            ->dateTime('d/m/Y H:i'),
                                            
                                        TextEntry::make('fecha_aceptacion')
                                            ->label('Aceptado')
                                            ->placeholder('No aceptado')
                                            ->dateTime('d/m/Y H:i'),
                                            
                                        TextEntry::make('fecha_completado')
                                            ->label('Completado')
                                            ->placeholder('No completado')
                                            ->dateTime('d/m/Y H:i'),
                                    ]),
                                    
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('usuarioSolicitante.name')
                                            ->label('Solicitado por'),
                                            
                                        TextEntry::make('usuarioMantenimiento.name')
                                            ->label('Asignado a')
                                            ->placeholder('Sin asignar'),
                                    ]),
                            ])
                            ->contained(false)
                            ->columns(1)
                            ->visible(fn ($record) => $record->totalMantenimientos() > 0),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->totalMantenimientos() === 0),

                Section::make('Vales de Resguardo')
                    ->description('Vales de resguardo asociados a este mobiliario')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('vales_stats')
                            ->label('Resumen')
                            ->formatStateUsing(function ($record) {
                                $record->load(['vales', 'valesMultiples']);
                                $totalVales = $record->vales->count();
                                $totalValesMultiples = $record->valesMultiples->count();
                                $total = $totalVales + $totalValesMultiples;
                                
                                if ($total === 0) {
                                    return 'No hay vales de resguardo registrados';
                                }
                                
                                return "Total de vales: {$total} (Individuales: {$totalVales} | Múltiples: {$totalValesMultiples})";
                            })
                            ->icon('heroicon-o-document-duplicate')
                            ->badge()
                            ->color(function ($record) {
                                $record->load(['vales', 'valesMultiples']);
                                return ($record->vales->count() + $record->valesMultiples->count()) > 0 ? 'success' : 'gray';
                            }),

                        RepeatableEntry::make('vales')
                            ->label('Vales Individuales')
                            ->schema([
                                TextEntry::make('numero_vale')
                                    ->label('Folio Vale')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntry\TextEntrySize::Medium)
                                    ->copyable()
                                    ->url(fn ($record) => route('vale.imprimir', $record))
                                    ->openUrlInNewTab()
                                    ->color('primary')
                                    ->icon('heroicon-o-document-arrow-down')
                                    ->tooltip('Clic para ver PDF del vale')
                                    ->columnSpanFull(),
                            ])
                            ->contained(false)
                            ->columns(1)
                            ->visible(function ($record) {
                                $record->load('vales');
                                return $record->vales->count() > 0;
                            }),
                            
                        RepeatableEntry::make('valesMultiples')
                            ->label('Vales Múltiples (parte de un vale con varios mobiliarios)')
                            ->schema([
                                TextEntry::make('numero_vale')
                                    ->label('Folio Vale')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntry\TextEntrySize::Medium)
                                    ->copyable()
                                    ->url(fn ($record) => route('vale.imprimir', $record))
                                    ->openUrlInNewTab()
                                    ->color('primary')
                                    ->icon('heroicon-o-document-arrow-down')
                                    ->tooltip('Clic para ver PDF del vale')
                                    ->columnSpanFull(),
                            ])
                            ->contained(false)
                            ->columns(1)
                            ->visible(function ($record) {
                                $record->load('valesMultiples');
                                return $record->valesMultiples->count() > 0;
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(function ($record) {
                        $record->load(['vales', 'valesMultiples']);
                        return ($record->vales->count() + $record->valesMultiples->count()) === 0;
                    }),

                Section::make('Vales de Mantenimiento')
                    ->description('Vales generados para mantenimientos')
                    ->icon('heroicon-o-wrench')
                    ->schema([
                        TextEntry::make('vales_mantenimiento_stats')
                            ->label('Resumen')
                            ->formatStateUsing(function ($record) {
                                $record->load('mantenimientos');
                                $totalValesMantenimiento = $record->mantenimientos
                                    ->whereNotNull('folio_vale')
                                    ->count();
                                
                                if ($totalValesMantenimiento === 0) {
                                    return 'No hay vales de mantenimiento generados';
                                }
                                
                                return "Total de vales de mantenimiento: {$totalValesMantenimiento}";
                            })
                            ->icon('heroicon-o-document')
                            ->badge()
                            ->color(function ($record) {
                                $record->load('mantenimientos');
                                return $record->mantenimientos->whereNotNull('folio_vale')->count() > 0 ? 'info' : 'gray';
                            }),

                        RepeatableEntry::make('mantenimientos')
                            ->label('Vales de Mantenimiento Generados')
                            ->schema([
                                TextEntry::make('folio_vale')
                                    ->label('Folio Vale')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntry\TextEntrySize::Medium)
                                    ->copyable()
                                    ->url(fn ($state, $record) => 
                                        $record->folio_vale ? route('mantenimiento.vale.pdf', $record) : null
                                    )
                                    ->openUrlInNewTab()
                                    ->color('primary')
                                    ->icon('heroicon-o-document-arrow-down')
                                    ->tooltip('Clic para ver PDF del vale')
                                    ->columnSpanFull(),
                            ])
                            ->contained(false)
                            ->columns(1)
                            ->visible(function ($record) {
                                $record->load('mantenimientos');
                                return $record->mantenimientos->whereNotNull('folio_vale')->count() > 0;
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(function ($record) {
                        $record->load('mantenimientos');
                        return $record->mantenimientos->whereNotNull('folio_vale')->count() === 0;
                    }),

                Section::make('Información de Baja')
                    ->description('Detalles del proceso de baja si aplica')
                    ->icon('heroicon-o-x-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('dado_de_baja')
                                    ->label('Estado de Baja')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'DADO DE BAJA' : 'ACTIVO')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'),
                                    
                                TextEntry::make('fecha_baja')
                                    ->label('Fecha de Baja')
                                    ->placeholder('No aplica')
                                    ->date('d/m/Y')
                                    ->visible(fn ($record) => $record->dado_de_baja)
                                    ->icon('heroicon-o-calendar'),
                            ]),
                            
                        TextEntry::make('motivo_baja')
                            ->label('Motivo de Baja')
                            ->placeholder('No especificado')
                            ->visible(fn ($record) => $record->dado_de_baja)
                            ->columnSpanFull()
                            ->icon('heroicon-o-document-text'),
                    ])
                    ->visible(fn ($record) => $record->dado_de_baja)
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Información del Sistema')
                    ->description('Metadatos y control de versiones')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Creado el')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-clock'),

                                TextEntry::make('updated_at')
                                    ->label('Última actualización')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-arrow-path'),

                                TextEntry::make('usuarioCreador.name')
                                    ->label('Creado por')
                                    ->placeholder('Usuario no disponible')
                                    ->icon('heroicon-o-user-plus'),

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
            Actions\EditAction::make()
                ->label('Editar')
                ->icon('heroicon-o-pencil')
                ->color('warning'),
                
            Actions\DeleteAction::make()
                ->label('Eliminar')
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }
}
