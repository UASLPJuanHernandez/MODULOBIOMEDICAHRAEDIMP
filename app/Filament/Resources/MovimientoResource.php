<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimientoResource\Pages;
use App\Filament\Resources\MovimientoResource\RelationManagers;
use App\Models\Movimiento;
use App\Models\Mobiliario;
use App\Models\Localizacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Auth;

class MovimientoResource extends Resource
{
    protected static ?string $model = Movimiento::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    public static function shouldRegisterNavigation(): bool
    {
        return !auth()->user()?->hasRole('Personal de Mantenimiento') ?? true;
    }
    
    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('Administrador') ?? false;
    }
    
    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('Administrador') ?? false;
    }

    protected static ?string $navigationLabel = 'Movimientos';

    protected static ?string $pluralModelLabel = 'Movimientos';

    protected static ?string $modelLabel = 'Movimiento';

    protected static ?string $navigationGroup = 'Gestión de Inventario';

    protected static ?int $navigationSort = 2;

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
                                    ->activos() // Filtrar solo mobiliarios activos (no dados de baja)
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
                Tables\Columns\TextColumn::make('numero_movimiento')
                    ->label('Número de Movimiento')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('cantidad_mobiliarios')
                    ->label('Mobiliarios')
                    ->badge()
                    ->color(fn (string $state): string => match ((int) $state) {
                        1 => 'gray',
                        2 => 'success',
                        3 => 'warning',
                        4 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => "{$state} item" . ((int) $state !== 1 ? 's' : '')),
                
                Tables\Columns\TextColumn::make('mobiliarios_resumen')
                    ->label('Códigos de Inventario')
                    ->getStateUsing(function ($record) {
                        $codigos = $record->mobiliarios->pluck('numero_control')->toArray();
                        if (count($codigos) <= 2) {
                            return implode(', ', $codigos);
                        }
                        return implode(', ', array_slice($codigos, 0, 2)) . ' y ' . (count($codigos) - 2) . ' más';
                    })
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('areaActual.ubicacion_resumida')
                    ->label('Destino')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fecha_movimiento')
                    ->label('Fecha del Movimiento')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('se_entrega_con')
                    ->label('Se Entrega Con')
                    ->limit(20)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('se_retira_con')
                    ->label('Se Retira Con')
                    ->limit(20)
                    ->searchable(),
                
                Tables\Columns\IconColumn::make('vale_generado')
                    ->label('Vale')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('area_actual_id')
                    ->label('Área de Destino')
                    ->options(fn () => Localizacion::all()->pluck('ubicacion_resumida', 'id')),
                
                SelectFilter::make('cantidad_mobiliarios')
                    ->label('Cantidad de Mobiliarios')
                    ->options([
                        '1' => '1 mobiliario',
                        '2' => '2 mobiliarios',
                        '3' => '3 mobiliarios',
                        '4' => '4 mobiliarios',
                    ]),
                
                Filter::make('con_vale')
                    ->label('Con Vale Generado')
                    ->query(fn (Builder $query): Builder => $query->where('vale_generado', true)),
                
                Filter::make('sin_vale')
                    ->label('Sin Vale Generado')
                    ->query(fn (Builder $query): Builder => $query->where('vale_generado', false)),
                
                Filter::make('fecha_movimiento')
                    ->label('Fecha del Movimiento')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_movimiento', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_movimiento', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver Detalles')
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                        
                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->icon('heroicon-o-pencil')
                        ->color('warning'),
                    Tables\Actions\Action::make('generar_vale')
                        ->label('Generar Vale')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->visible(fn ($record) => !$record->vale_generado)
                        ->action(function ($record) {
                            // Lógica para generar vale aquí
                            $record->update(['vale_generado' => true]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Vale generado correctamente')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha_movimiento', 'desc');
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
            'index' => Pages\ListMovimientos::route('/'),
            'create' => Pages\CreateMovimiento::route('/create'),
            'view' => Pages\ViewMovimiento::route('/{record}'),
            'edit' => Pages\EditMovimiento::route('/{record}/edit'),
        ];
    }
    

}
