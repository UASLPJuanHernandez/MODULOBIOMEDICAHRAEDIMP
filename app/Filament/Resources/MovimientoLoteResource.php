<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimientoLoteResource\Pages;
use App\Filament\Resources\MovimientoLoteResource\RelationManagers;
use App\Models\MovimientoLote;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Get;
use Filament\Forms\Set;

class MovimientoLoteResource extends Resource
{
    protected static ?string $model = MovimientoLote::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationLabel = 'Movimientos por Lote';

    protected static ?string $pluralModelLabel = 'Movimientos por Lote';

    protected static ?string $modelLabel = 'Movimiento por Lote';

    protected static ?string $navigationGroup = 'Gestión de Inventario';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Selección de Mobiliario')
                    ->description('Seleccione hasta 4 mobiliarios para el movimiento (máximo para vale)')
                    ->schema([
                        CheckboxList::make('mobiliarios_data')
                            ->label('Mobiliarios Disponibles')
                            ->options(function () {
                                return Mobiliario::with('localizacion')
                                    ->get()
                                    ->mapWithKeys(function ($mobiliario) {
                                        $ubicacion = $mobiliario->ubicacionReal();
                                        $ubicacionTexto = $ubicacion ? $ubicacion->ubicacion_resumida : 'Sin ubicación';
                                        
                                        return [
                                            $mobiliario->id => "{$mobiliario->numero_control} - {$mobiliario->descripcion} (Actual: {$ubicacionTexto})"
                                        ];
                                    });
                            })
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(1)
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                // Limitar a 4 mobiliarios máximo
                                if (is_array($state) && count($state) > 4) {
                                    $set('mobiliarios_data', array_slice($state, 0, 4));
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Límite excedido')
                                        ->body('Máximo 4 mobiliarios permitidos por movimiento')
                                        ->warning()
                                        ->send();
                                }
                            })
                            ->helperText('Seleccione hasta 4 mobiliarios. El contador se muestra abajo.')
                            ->columnSpanFull(),
                            
                        Forms\Components\Placeholder::make('contador_mobiliarios')
                            ->label('')
                            ->content(function (Get $get): string {
                                $seleccionados = $get('mobiliarios_data') ?? [];
                                $cantidad = is_array($seleccionados) ? count($seleccionados) : 0;
                                
                                return "<div style='color: " . ($cantidad <= 4 ? '#059669' : '#dc2626') . "; font-weight: bold; font-size: 14px;'>
                                    📦 Mobiliarios seleccionados: {$cantidad}/4
                                </div>";
                            })
                            ->live(),
                    ]),
                
                Section::make('Información del Movimiento')
                    ->description('Configure el traslado del lote de mobiliarios')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('area_actual_id')
                                    ->label('Área de Destino')
                                    ->options(function () {
                                        return Localizacion::all()->pluck('ubicacion_resumida', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Seleccione la nueva ubicación'),
                                
                                Forms\Components\DateTimePicker::make('fecha_movimiento')
                                    ->label('Fecha y Hora del Movimiento')
                                    ->default(now())
                                    ->required(),
                                
                                Forms\Components\Hidden::make('usuario_id')
                                    ->default(fn () => Auth::id())
                                    ->required(),
                                
                                Forms\Components\TextInput::make('usuario_responsable')
                                    ->label('Usuario que realiza el movimiento')
                                    ->default(fn () => Auth::user()?->name)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(2),
                            ]),
                    ]),
                
                Section::make('Responsables de Entrega y Recepción')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('se_entrega_con')
                                    ->label('Se Entrega Con')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('se_retira_con')
                                    ->label('Se Retira Con')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),
                
                Section::make('Observaciones')
                    ->schema([
                        Forms\Components\Textarea::make('observacion')
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
                Tables\Columns\TextColumn::make('numero_lote')
                    ->label('N° Lote')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('cantidad_mobiliarios')
                    ->label('Mobiliarios')
                    ->getStateUsing(fn ($record) => $record->mobiliarios()->count())
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('areaActual.ubicacion_resumida')
                    ->label('Área Destino')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('fecha_movimiento')
                    ->label('Fecha Movimiento')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('vale_generado')
                    ->label('Vale')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('se_entrega_con')
                    ->label('Entrega')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('area_actual_id')
                    ->label('Área Destino')
                    ->relationship('areaActual', 'division')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->ubicacion_resumida)
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('vale_generado')
                    ->label('Estado del Vale')
                    ->options([
                        0 => 'Sin vale',
                        1 => 'Con vale',
                    ]),
                
                SelectFilter::make('usuario_id')
                    ->label('Usuario')
                    ->relationship('usuario', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('generar_vale')
                    ->label('Generar Vale')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn (MovimientoLote $record): bool => !$record->vale_generado)
                    ->url(fn (MovimientoLote $record): string => 
                        route('admin.resources.vales.create', ['movimiento_lote_id' => $record->id])
                    )
                    ->openUrlInNewTab(),
                
                Tables\Actions\Action::make('ver_vale')
                    ->label('Ver Vale')
                    ->icon('heroicon-o-document')
                    ->color('info')
                    ->visible(fn (MovimientoLote $record): bool => $record->vale_generado && $record->vale)
                    ->url(fn (MovimientoLote $record): string => 
                        route('vale.imprimir', $record->vale)
                    )
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
            'index' => Pages\ListMovimientoLotes::route('/'),
            'create' => Pages\CreateMovimientoLote::route('/create'),
            'edit' => Pages\EditMovimientoLote::route('/{record}/edit'),
        ];
    }
}
