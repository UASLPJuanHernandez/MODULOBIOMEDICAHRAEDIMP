<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenServicioResource\Pages;
use App\Filament\Resources\OrdenServicioResource\RelationManagers;
use App\Models\OrdenServicio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class OrdenServicioResource extends Resource
{
    protected static ?string $model = OrdenServicio::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Órdenes de Servicio';

    protected static ?string $pluralModelLabel = 'Órdenes de Servicio';

    protected static ?string $modelLabel = 'Orden de Servicio';

    protected static ?string $navigationGroup = 'Mantenimiento';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Orden')
                    ->description('Detalles de la orden de servicio de mantenimiento')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('mobiliario_id')
                                    ->label('Mobiliario')
                                    ->relationship('mobiliario', 'numero_control')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('numero_orden')
                                    ->label('Número de Orden')
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->maxLength(50),
                                
                                Forms\Components\Select::make('tipo_servicio')
                                    ->label('Tipo de Servicio')
                                    ->options([
                                        'preventivo' => 'Mantenimiento Preventivo',
                                        'correctivo' => 'Mantenimiento Correctivo',
                                        'calibracion' => 'Calibración',
                                        'reparacion' => 'Reparación',
                                        'instalacion' => 'Instalación',
                                        'reubicacion' => 'Reubicación',
                                    ])
                                    ->required(),
                                
                                Forms\Components\DatePicker::make('fecha_solicitud')
                                    ->label('Fecha de Solicitud')
                                    ->default(today())
                                    ->required(),
                                
                                Forms\Components\DatePicker::make('fecha_programada')
                                    ->label('Fecha Programada')
                                    ->required(),
                                
                                Forms\Components\Select::make('usuario_id')
                                    ->label('Usuario Solicitante')
                                    ->relationship('usuario', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                
                                Forms\Components\Select::make('prioridad')
                                    ->label('Prioridad')
                                    ->options([
                                        'baja' => 'Baja',
                                        'media' => 'Media',
                                        'alta' => 'Alta',
                                        'critica' => 'Crítica',
                                    ])
                                    ->default('media')
                                    ->required(),
                            ]),
                    ]),
                
                Section::make('Descripción del Problema')
                    ->schema([
                        Forms\Components\Textarea::make('problema_detectado')
                            ->label('Problema Detectado')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Detalles del Servicio')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('tecnico_asignado')
                                    ->label('Técnico Asignado')
                                    ->maxLength(255),
                                
                                Forms\Components\Select::make('estado')
                                    ->label('Estado')
                                    ->options([
                                        'pendiente' => 'Pendiente',
                                        'en_proceso' => 'En Proceso',
                                        'completado' => 'Completado',
                                        'cancelado' => 'Cancelado',
                                        'requiere_repuestos' => 'Requiere Repuestos',
                                    ])
                                    ->default('pendiente')
                                    ->required(),
                                
                                Forms\Components\DatePicker::make('fecha_inicio')
                                    ->label('Fecha de Inicio'),
                                
                                Forms\Components\DatePicker::make('fecha_finalizacion')
                                    ->label('Fecha de Finalización'),
                                
                                Forms\Components\TextInput::make('costo_estimado')
                                    ->label('Costo Estimado')
                                    ->numeric()
                                    ->prefix('$'),
                                
                                Forms\Components\TextInput::make('costo_real')
                                    ->label('Costo Real')
                                    ->numeric()
                                    ->prefix('$'),
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
            ->columns([
                Tables\Columns\TextColumn::make('numero_orden')
                    ->label('N° Orden')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('mobiliario.numero_control')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('mobiliario.descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('tipo_servicio')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'preventivo' => 'info',
                        'correctivo' => 'warning',
                        'calibracion' => 'success',
                        'reparacion' => 'danger',
                        'instalacion' => 'gray',
                        'reubicacion' => 'purple',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'en_proceso' => 'info',
                        'completado' => 'success',
                        'cancelado' => 'danger',
                        'requiere_repuestos' => 'gray',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baja' => 'info',
                        'media' => 'warning',
                        'alta' => 'danger',
                        'critica' => 'red',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('fecha_solicitud')
                    ->label('F. Solicitud')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('F. Programada')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('tecnico_asignado')
                    ->label('Técnico')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Solicitante')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('costo_real')
                    ->label('Costo')
                    ->money('MXN')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo_servicio')
                    ->label('Tipo de Servicio')
                    ->options([
                        'preventivo' => 'Mantenimiento Preventivo',
                        'correctivo' => 'Mantenimiento Correctivo',
                        'calibracion' => 'Calibración',
                        'reparacion' => 'Reparación',
                        'instalacion' => 'Instalación',
                        'reubicacion' => 'Reubicación',
                    ]),
                
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'completado' => 'Completado',
                        'cancelado' => 'Cancelado',
                        'requiere_repuestos' => 'Requiere Repuestos',
                    ]),
                
                SelectFilter::make('prioridad')
                    ->label('Prioridad')
                    ->options([
                        'baja' => 'Baja',
                        'media' => 'Media',
                        'alta' => 'Alta',
                        'critica' => 'Crítica',
                    ]),
                
                Filter::make('fecha_solicitud')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_solicitud', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_solicitud', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListOrdenServicios::route('/'),
            'create' => Pages\CreateOrdenServicio::route('/create'),
            'view' => Pages\ViewOrdenServicio::route('/{record}'),
            'edit' => Pages\EditOrdenServicio::route('/{record}/edit'),
        ];
    }
}
