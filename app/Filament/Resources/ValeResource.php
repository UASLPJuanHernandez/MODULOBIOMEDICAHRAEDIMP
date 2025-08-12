<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ValeResource\Pages;
use App\Filament\Resources\ValeResource\RelationManagers;
use App\Models\Vale;
use App\Models\Mobiliario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;

class ValeResource extends Resource
{
    protected static ?string $model = Vale::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Vales de Resguardo';

    protected static ?string $pluralModelLabel = 'Vales de Resguardo';

    protected static ?string $modelLabel = 'Vale de Resguardo';

    protected static ?string $navigationGroup = 'Documentación';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Vale')
                    ->description('Datos generales del vale de resguardo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('numero_vale')
                                    ->label('Número de Vale')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Se genera automáticamente'),
                                
                                Forms\Components\Select::make('tipo_vale')
                                    ->label('Tipo de Vale')
                                    ->options([
                                        'entrega' => 'Entrega',
                                        'retiro' => 'Retiro',
                                        'resguardo' => 'Resguardo',
                                    ])
                                    ->required(),
                                
                                Forms\Components\DateTimePicker::make('fecha_generacion')
                                    ->label('Fecha de Generación')
                                    ->default(now())
                                    ->required(),
                                
                                Forms\Components\Select::make('movimiento_id')
                                    ->label('Movimiento Asociado')
                                    ->relationship('movimiento', 'numero_movimiento')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->numero_movimiento}" . ($record->fecha_movimiento ? " - {$record->fecha_movimiento->format('d/m/Y H:i')}" : "") . " ({$record->cantidad_mobiliarios} items)")
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                                        if ($state) {
                                            // Obtener el movimiento seleccionado
                                            $movimiento = \App\Models\Movimiento::with('mobiliarios')->find($state);
                                            if ($movimiento && $movimiento->mobiliarios->count() > 0) {
                                                // Preparar los datos de los mobiliarios del movimiento
                                                $mobiliariosData = [];
                                                foreach ($movimiento->mobiliarios as $mobiliario) {
                                                    $mobiliariosData[] = [
                                                        'mobiliario_id' => $mobiliario->id,
                                                        'descripcion' => $mobiliario->descripcion,
                                                        'marca' => $mobiliario->marca,
                                                        'modelo' => $mobiliario->modelo,
                                                        'numero_serie' => $mobiliario->numero_serie,
                                                    ];
                                                }
                                                // Llenar automáticamente el repeater con los mobiliarios del movimiento
                                                $set('mobiliarios_data', $mobiliariosData);
                                            }
                                        } else {
                                            // Si no hay movimiento seleccionado, limpiar el repeater
                                            $set('mobiliarios_data', []);
                                        }
                                    }),
                            ]),
                    ]),
                
                Section::make('Mobiliarios del Movimiento')
                    ->description('Los mobiliarios se cargan automáticamente según el movimiento seleccionado')
                    ->schema([
                        Repeater::make('mobiliarios_data')
                            ->label('')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('mobiliario_id')
                                            ->label('Número de Control')
                                            ->options(function () {
                                                return Mobiliario::activos()->pluck('numero_control', 'id');
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->disabled() // Deshabilitado porque se llena automáticamente
                                            ->dehydrated(true) // IMPORTANTE: Asegurar que se envíe al servidor
                                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                                if ($state) {
                                                    $mobiliario = Mobiliario::find($state);
                                                    if ($mobiliario) {
                                                        $set('descripcion', $mobiliario->descripcion);
                                                        $set('marca', $mobiliario->marca);
                                                        $set('modelo', $mobiliario->modelo);
                                                        $set('numero_serie', $mobiliario->numero_serie);
                                                    }
                                                } else {
                                                    $set('descripcion', '');
                                                    $set('marca', '');
                                                    $set('modelo', '');
                                                    $set('numero_serie', '');
                                                }
                                            }),
                                        
                                        Forms\Components\TextInput::make('descripcion')
                                            ->label('Descripción')
                                            ->disabled()
                                            ->dehydrated(false),
                                        
                                        Forms\Components\TextInput::make('marca')
                                            ->label('Marca')
                                            ->disabled()
                                            ->dehydrated(false),
                                        
                                        Forms\Components\TextInput::make('modelo')
                                            ->label('Modelo')
                                            ->disabled()
                                            ->dehydrated(false),
                                        
                                        Forms\Components\TextInput::make('numero_serie')
                                            ->label('Número de Serie')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->addable(false) // No se pueden agregar items manualmente
                            ->deletable(false) // No se pueden eliminar items
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                !empty($state['mobiliario_id']) ? 
                                'Mobiliario: ' . (Mobiliario::find($state['mobiliario_id'])?->numero_control ?? 'Sin seleccionar') : 
                                'Nuevo mobiliario'
                            )
                            ->columnSpanFull()
                            ->helperText('Los mobiliarios se cargan automáticamente del movimiento seleccionado. Máximo 4 mobiliarios por vale.'),
                    ]),

                // SECCIÓN COMENTADA: Selección manual de mobiliarios
                // Descomente esta sección si desea volver al método manual de selección
                /*
                Section::make('Mobiliarios (Selección Manual)')
                    ->description('Seleccione los mobiliarios para este vale (máximo 4)')
                    ->schema([
                        Repeater::make('mobiliarios_data_manual')
                            ->label('')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('mobiliario_id')
                                            ->label('Número de Control')
                                            ->options(function () {
                                                return Mobiliario::activos()->pluck('numero_control', 'id');
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                                if ($state) {
                                                    $mobiliario = Mobiliario::find($state);
                                                    if ($mobiliario) {
                                                        $set('descripcion', $mobiliario->descripcion);
                                                        $set('marca', $mobiliario->marca);
                                                        $set('modelo', $mobiliario->modelo);
                                                        $set('numero_serie', $mobiliario->numero_serie);
                                                    }
                                                } else {
                                                    $set('descripcion', '');
                                                    $set('marca', '');
                                                    $set('modelo', '');
                                                    $set('numero_serie', '');
                                                }
                                            }),
                                        
                                        Forms\Components\TextInput::make('descripcion')
                                            ->label('Descripción')
                                            ->disabled()
                                            ->dehydrated(false),
                                        
                                        Forms\Components\TextInput::make('marca')
                                            ->label('Marca')
                                            ->disabled()
                                            ->dehydrated(false),
                                        
                                        Forms\Components\TextInput::make('modelo')
                                            ->label('Modelo')
                                            ->disabled()
                                            ->dehydrated(false),
                                        
                                        Forms\Components\TextInput::make('numero_serie')
                                            ->label('Número de Serie')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->addActionLabel('Agregar otro mobiliario')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                !empty($state['mobiliario_id']) ? 
                                'Mobiliario: ' . (Mobiliario::find($state['mobiliario_id'])?->numero_control ?? 'Sin seleccionar') : 
                                'Nuevo mobiliario'
                            )
                            ->minItems(1)
                            ->maxItems(4)
                            ->columnSpanFull()
                            ->helperText('Máximo 4 mobiliarios por vale para garantizar que todo quepa en una página'),
                    ]),
                */
                
                Section::make('Responsables')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('responsable_entrega')
                                    ->label('Responsable de Entrega')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('matricula_entrega')
                                    ->label('Matrícula de quien Entrega')
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('responsable_recibe')
                                    ->label('Responsable que Recibe')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('matricula_recibe')
                                    ->label('Matrícula de quien Recibe')
                                    ->maxLength(255),
                            ]),
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['mobiliarios', 'movimiento', 'mobiliario']))
            ->columns([
                Tables\Columns\TextColumn::make('numero_vale_formateado')
                    ->label('N° Vale')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('cantidad_mobiliarios')
                    ->label('Mobiliarios')
                    ->getStateUsing(function ($record) {
                        $cantidad = $record->mobiliarios->count();
                        if ($cantidad == 0 && $record->mobiliario_id) {
                            $cantidad = 1; // Vale individual
                        }
                        return $cantidad . ' items';
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('codigos_mobiliarios')
                    ->label('Códigos')
                    ->getStateUsing(function ($record) {
                        $codigos = collect();
                        
                        // Primero verificar mobiliarios múltiples
                        if ($record->mobiliarios->count() > 0) {
                            $codigos = $record->mobiliarios->pluck('numero_control');
                        } 
                        // Si no hay múltiples, verificar mobiliario individual
                        elseif ($record->mobiliario_id && $record->mobiliario) {
                            $codigos = collect([$record->mobiliario->numero_control]);
                        }
                        
                        if ($codigos->isEmpty()) {
                            return 'Sin mobiliarios';
                        }
                        
                        $resultado = $codigos->take(3)->implode(', ');
                        if ($codigos->count() > 3) {
                            $resultado .= '...';
                        }
                        return $resultado;
                    })
                    ->searchable()
                    ->limit(40),
                
                Tables\Columns\TextColumn::make('tipo_vale')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entrega' => 'success',
                        'retiro' => 'warning',
                        'resguardo' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('responsable_recibe')
                    ->label('Responsable')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('fecha_generacion')
                    ->label('F. Generación')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo_vale')
                    ->label('Tipo de Vale')
                    ->options([
                        'entrega' => 'Entrega',
                        'retiro' => 'Retiro',
                        'resguardo' => 'Resguardo',
                    ]),
                
                Filter::make('fecha_generacion')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_generacion', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_generacion', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('imprimir_vale')
                    ->label('Imprimir Vale')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (Vale $record): string => route('vale.imprimir', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVales::route('/'),
            'create' => Pages\CreateVale::route('/create'),
            'view' => Pages\ViewVale::route('/{record}'),
            'edit' => Pages\EditVale::route('/{record}/edit'),
        ];
    }
}
