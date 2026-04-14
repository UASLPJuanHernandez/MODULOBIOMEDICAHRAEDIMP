<?php

namespace App\Filament\Resources;

use App\Exports\InventarioEquipoExport;
use App\Filament\Resources\InventarioEquipoResource\Pages;
use App\Models\InventarioEquipo;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class InventarioEquipoResource extends Resource
{
    protected static ?string $model = InventarioEquipo::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Inventario';

    protected static ?string $modelLabel = 'Equipo';

    protected static ?string $pluralModelLabel = 'Inventario de Equipos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Unidad')
                    ->schema([
                        Grid::make(3)->schema([
                            Forms\Components\TextInput::make('numero_inventario')
                                ->label('No. de Inventario')
                                ->maxLength(100),
                            Forms\Components\TextInput::make('clues')
                                ->label('CLUES')
                                ->maxLength(100),
                            Forms\Components\TextInput::make('unidad_medica')
                                ->label('Unidad Médica')
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('area')
                                ->label('Especialidad / Área')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('ubicacion_especifica')
                                ->label('Ubicación Específica')
                                ->maxLength(255),
                        ]),
                    ]),

                Section::make('Datos del Equipo')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('clave_cbsg')
                                ->label('Clave Cuadro Básico CSG')
                                ->maxLength(100),
                            Forms\Components\TextInput::make('equipo')
                                ->label('Equipo')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Grid::make(3)->schema([
                            Forms\Components\TextInput::make('marca')
                                ->label('Marca')
                                ->maxLength(100),
                            Forms\Components\TextInput::make('modelo')
                                ->label('Modelo')
                                ->maxLength(100),
                            Forms\Components\TextInput::make('numero_serie')
                                ->label('Número de Serie')
                                ->maxLength(150),
                        ]),
                        Grid::make(3)->schema([
                            Forms\Components\Select::make('propiedad')
                                ->label('Propiedad del Equipo')
                                ->options([
                                    'PROPIO' => 'Propio',
                                    'COMODATO' => 'Comodato',
                                    'ARRENDAMIENTO' => 'Arrendamiento',
                                    'SERVICIO INTEGRAL' => 'Servicio Integral',
                                    'PRÉSTAMO' => 'Préstamo',
                                    'OTRO' => 'Otro',
                                ])
                                ->native(false),
                            Forms\Components\TextInput::make('anio_fabricacion')
                                ->label('Año de Fabricación')
                                ->maxLength(10),
                            Forms\Components\DatePicker::make('fecha_adquisicion')
                                ->label('Fecha de Adquisición')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                        ]),
                    ]),

                Section::make('Estado Actual')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('condiciones')
                                ->label('Condiciones del Equipo')
                                ->options([
                                    'BUENO' => 'Bueno',
                                    'REGULAR' => 'Regular',
                                    'MALO' => 'Malo',
                                    'FUNCIONAL' => 'Funcional',
                                    'INOPERANTE' => 'Inoperante',
                                    'FUERA DE SERVICIO' => 'Fuera de Servicio',
                                    'N/A' => 'N/A',
                                ])
                                ->native(false),
                            Forms\Components\Select::make('estatus')
                                ->label('Estatus del Equipo')
                                ->options([
                                    'FUNCIONAMIENTO COMPLETO' => 'Funcionamiento Completo',
                                    'FUNCIONA PARCIALMENTE' => 'Funciona Parcialmente',
                                    'FUERA DE SERVICIO' => 'Fuera de Servicio',
                                    'DISFUNCIONAL' => 'Disfuncional',
                                ])
                                ->native(false),
                        ]),
                        Forms\Components\Textarea::make('causa_no_funcionamiento')
                            ->label('Causa de No Funcionamiento')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('requerimientos')
                            ->label('Requerimientos y Necesidades Actuales')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Mantenimiento')
                    ->schema([
                        Grid::make(3)->schema([
                            Forms\Components\TextInput::make('frecuencia_mantenimiento')
                                ->label('Frecuencia de Mantenimiento')
                                ->maxLength(100),
                            Forms\Components\Select::make('tipo_mantenimiento')
                                ->label('Mantenimiento Preventivo')
                                ->options([
                                    'INTERNO' => 'Interno',
                                    'EXTERNO' => 'Externo',
                                    'INTERNO/EXTERNO' => 'Interno / Externo',
                                ])
                                ->native(false),
                            Forms\Components\TextInput::make('contrato_mantenimiento')
                                ->label('Contrato de Mantenimiento')
                                ->maxLength(50),
                        ]),
                        Grid::make(2)->schema([
                            Forms\Components\DatePicker::make('ultimo_mp')
                                ->label('Último MP')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                            Forms\Components\DatePicker::make('siguiente_mp')
                                ->label('Siguiente MP')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                        ]),
                    ]),

                Section::make('Contrato y Garantía')
                    ->schema([
                        Grid::make(3)->schema([
                            Forms\Components\Toggle::make('garantia')
                                ->label('Garantía'),
                            Forms\Components\DatePicker::make('fin_garantia')
                                ->label('Fin de Garantía')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                            Forms\Components\Toggle::make('fin_vida_util')
                                ->label('Fin de Vida Útil (EOL)'),
                        ]),
                        Grid::make(3)->schema([
                            Forms\Components\Toggle::make('tiene_contrato')
                                ->label('Tiene Contrato'),
                            Forms\Components\TextInput::make('numero_contrato')
                                ->label('No. de Contrato')
                                ->maxLength(150),
                            Forms\Components\TextInput::make('proveedor_mantenimiento')
                                ->label('Proveedor de Mantenimiento')
                                ->maxLength(255),
                        ]),
                        Grid::make(3)->schema([
                            Forms\Components\DatePicker::make('inicio_poliza')
                                ->label('Inicio de Póliza')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                            Forms\Components\DatePicker::make('fin_poliza')
                                ->label('Fin de Póliza')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                            Forms\Components\TextInput::make('cantidad_mp_anio')
                                ->label('Cantidad de MP al Año')
                                ->maxLength(20),
                        ]),
                        Forms\Components\TextInput::make('costo_contrato')
                            ->label('Costo de Contrato')
                            ->maxLength(100),
                    ]),

                Section::make('Observaciones')
                    ->schema([
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_inventario')
                    ->label('No. Inventario')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('equipo')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(40),
                Tables\Columns\TextColumn::make('marca')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('modelo')
                    ->label('Modelo')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('area')
                    ->label('Área')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(35)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ubicacion_especifica')
                    ->label('Ubicación')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('estatus_normalizado')
                    ->label('Estatus')
                    ->colors([
                        'success' => 'Funcionamiento Completo',
                        'warning' => 'Funciona Parcialmente',
                        'danger' => 'Fuera de Servicio',
                        'gray' => fn ($state) => !in_array($state, ['Funcionamiento Completo', 'Funciona Parcialmente', 'Fuera de Servicio']),
                    ])
                    ->sortable(false),
                Tables\Columns\TextColumn::make('condiciones')
                    ->label('Condiciones')
                    ->badge()
                    ->color(fn (string $state): string => match (strtoupper(trim($state))) {
                        'BUENO', 'FUNCIONAL' => 'success',
                        'REGULAR' => 'warning',
                        'MALO', 'INOPERANTE', 'FUERA DE SERVICIO', 'DISFUNCIONAL', 'DISFUNCION/AL' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('propiedad')
                    ->label('Propiedad')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('siguiente_mp')
                    ->label('Sig. MP')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('tiene_contrato')
                    ->label('Contrato')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('garantia')
                    ->label('Garantía')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('numero_inventario')
            ->filters([
                SelectFilter::make('estatus')
                    ->label('Estatus')
                    ->options([
                        'FUNCIONAMIENTO COMPLETO' => 'Funcionamiento Completo',
                        'FUNCION/AMIENTO COMPLETO' => 'Funcionamiento Completo (alt)',
                        'FUNCIONA PARCIALMENTE' => 'Funciona Parcialmente',
                        'FUNCION/A PARCIALMENTE' => 'Funciona Parcialmente (alt)',
                        'FUERA DE SERVICIO' => 'Fuera de Servicio',
                        'DISFUNCIONAL' => 'Disfuncional',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->where('estatus', 'like', '%' . $data['value'] . '%');
                    }),
                SelectFilter::make('condiciones')
                    ->label('Condiciones')
                    ->options([
                        'BUENO' => 'Bueno',
                        'REGULAR' => 'Regular',
                        'MALO' => 'Malo',
                        'FUNCIONAL' => 'Funcional',
                        'INOPERANTE' => 'Inoperante',
                        'FUERA DE SERVICIO' => 'Fuera de Servicio',
                    ]),
                SelectFilter::make('propiedad')
                    ->label('Propiedad')
                    ->options([
                        'PROPIO' => 'Propio',
                        'COMODATO' => 'Comodato',
                        'ARRENDAMIENTO' => 'Arrendamiento',
                        'PRÉSTAMO' => 'Préstamo',
                        'OTRO' => 'Otro',
                    ]),
                SelectFilter::make('tipo_mantenimiento')
                    ->label('Tipo Mantenimiento')
                    ->options([
                        'INTERNO' => 'Interno',
                        'EXTERNO' => 'Externo',
                    ]),
                Filter::make('tiene_contrato')
                    ->label('Con Contrato')
                    ->query(fn (Builder $query) => $query->where('tiene_contrato', true))
                    ->toggle(),
                Filter::make('garantia')
                    ->label('Con Garantía')
                    ->query(fn (Builder $query) => $query->where('garantia', true))
                    ->toggle(),
                Filter::make('siguiente_mp_proximo')
                    ->label('MP Próximo (30 días)')
                    ->query(fn (Builder $query) => $query->whereBetween('siguiente_mp', [now(), now()->addDays(30)]))
                    ->toggle(),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('deseleccionar')
                        ->label('Quitar selección')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                        ->action(fn () => null)
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\ExportBulkAction::make()
                        ->hidden(),
                    Tables\Actions\BulkAction::make('exportar_seleccion')
                        ->label('Exportar selección a Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $fecha = Carbon::now()->format('Y-m-d_H-i');
                            $nombre = "Inventario_Equipos_Seleccion_{$fecha}.xlsx";
                            return Excel::download(new InventarioEquipoExport($records), $nombre);
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasRole('Administrador') ?? false),
                ]),
            ])
            ->striped()
            ->paginated([25, 50, 100, 'all']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarioEquipos::route('/'),
            'create' => Pages\CreateInventarioEquipo::route('/create'),
            'view' => Pages\ViewInventarioEquipo::route('/{record}'),
            'edit' => Pages\EditInventarioEquipo::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
