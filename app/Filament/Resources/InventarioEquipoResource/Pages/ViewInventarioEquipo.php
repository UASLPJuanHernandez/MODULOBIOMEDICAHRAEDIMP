<?php

namespace App\Filament\Resources\InventarioEquipoResource\Pages;

use App\Filament\Resources\InventarioEquipoResource;
use App\Services\ValeEntregaService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotifAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;

class ViewInventarioEquipo extends ViewRecord
{
    protected static string $resource = InventarioEquipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('vale_entrega')
                ->label('Vale de Entrega')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->url(fn () => route('inventario.equipo.vale-entrega', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('vale_retiro')
                ->label('Vale de Retiro')
                ->icon('heroicon-o-document-minus')
                ->color('danger')
                ->url(fn () => route('inventario.equipo.vale-retiro', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('descargar_historial_pdf')
                ->label('Historial PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn () => route('inventario.equipo.historial.pdf', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('eliminar')
                ->label('Eliminar')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Eliminar registro del inventario')
                ->modalDescription('Se generará el vale de retiro automáticamente y quedará disponible en Documentos generados. Esta acción no se puede deshacer.')
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalIconColor('warning')
                ->modalSubmitActionLabel('Sí, eliminar')
                ->action(function (): void {
                    // 1. Registrar vale ANTES de eliminar (mientras el registro aún existe)
                    $vale = (new ValeEntregaService())->registrarRetiro($this->record);

                    // 2. Eliminar el equipo
                    $this->record->delete();

                    // 3. Notificación persistente con botón de descarga directa
                    Notification::make()
                        ->title('Equipo eliminado')
                        ->body('El vale de retiro fue guardado en Documentos generados.')
                        ->success()
                        ->persistent()
                        ->actions([
                            NotifAction::make('descargar')
                                ->label('Descargar vale de retiro')
                                ->url(route('inventario.vale.redescargar', $vale))
                                ->openUrlInNewTab()
                                ->button(),
                        ])
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información de la Unidad')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('numero_inventario')->label('No. de Inventario')->weight('bold'),
                            TextEntry::make('clues')->label('CLUES'),
                            TextEntry::make('unidad_medica')->label('Unidad Médica'),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('area')->label('Área / Especialidad'),
                            TextEntry::make('ubicacion_especifica')->label('Ubicación Específica'),
                        ]),
                    ]),

                Section::make('Datos del Equipo')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('clave_cbsg')->label('Clave Cuadro Básico CSG'),
                            TextEntry::make('equipo')->label('Equipo')->weight('bold'),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('marca')->label('Marca'),
                            TextEntry::make('modelo')->label('Modelo'),
                            TextEntry::make('numero_serie')->label('No. de Serie')->copyable(),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('propiedad')->label('Propiedad')->badge(),
                            TextEntry::make('anio_fabricacion')->label('Año de Fabricación'),
                            TextEntry::make('fecha_adquisicion')->label('Fecha de Adquisición')->date('d/m/Y'),
                        ]),
                    ]),

                Section::make('Estado Actual')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('condiciones')
                                ->label('Condiciones')
                                ->badge()
                                ->color(fn (string $state): string => match (strtoupper(trim($state))) {
                                    'BUENO', 'FUNCIONAL' => 'success',
                                    'REGULAR' => 'warning',
                                    'MALO', 'INOPERANTE', 'FUERA DE SERVICIO' => 'danger',
                                    default => 'gray',
                                }),
                            TextEntry::make('estatus_normalizado')
                                ->label('Estatus')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'Funcionamiento Completo' => 'success',
                                    'Funciona Parcialmente' => 'warning',
                                    'Fuera de Servicio' => 'danger',
                                    default => 'gray',
                                }),
                        ]),
                        TextEntry::make('causa_no_funcionamiento')->label('Causa de No Funcionamiento')->columnSpanFull(),
                        TextEntry::make('requerimientos')->label('Requerimientos Actuales')->columnSpanFull(),
                    ]),

                Section::make('Mantenimiento')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('frecuencia_mantenimiento')->label('Frecuencia'),
                            TextEntry::make('tipo_mantenimiento')->label('Tipo')->badge(),
                            TextEntry::make('contrato_mantenimiento')->label('Contrato Mant.'),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('ultimo_mp')->label('Último MP')->date('d/m/Y'),
                            TextEntry::make('siguiente_mp')->label('Siguiente MP')->date('d/m/Y'),
                        ]),
                    ]),

                Section::make('Contrato y Garantía')
                    ->schema([
                        Grid::make(3)->schema([
                            IconEntry::make('garantia')->label('Garantía')->boolean(),
                            TextEntry::make('fin_garantia')->label('Fin de Garantía')->date('d/m/Y'),
                            IconEntry::make('fin_vida_util')->label('EOL / Fin de Vida Útil')->boolean(),
                        ]),
                        Grid::make(3)->schema([
                            IconEntry::make('tiene_contrato')->label('Tiene Contrato')->boolean(),
                            TextEntry::make('numero_contrato')->label('No. de Contrato')->copyable(),
                            TextEntry::make('proveedor_mantenimiento')->label('Proveedor'),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('inicio_poliza')->label('Inicio Póliza')->date('d/m/Y'),
                            TextEntry::make('fin_poliza')->label('Fin Póliza')->date('d/m/Y'),
                            TextEntry::make('cantidad_mp_anio')->label('MP / Año'),
                        ]),
                        TextEntry::make('costo_contrato')->label('Costo de Contrato'),
                    ]),

                Section::make('Observaciones')
                    ->schema([
                        TextEntry::make('observaciones')->label('')->columnSpanFull(),
                    ])
                    ->hidden(fn ($record) => empty($record->observaciones)),
            ]);
    }
}
