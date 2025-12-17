<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MantenimientoResource\Pages;
use App\Filament\Resources\MantenimientoResource\RelationManagers;
use App\Models\Mantenimiento;
use App\Models\Mobiliario;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;

class MantenimientoResource extends Resource
{
    protected static ?string $model = Mantenimiento::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    protected static ?string $navigationLabel = 'Órdenes de Servicio';
    
    protected static ?string $navigationGroup = 'Mantenimiento';
    
    protected static ?string $modelLabel = 'Orden de Servicio';
    
    protected static ?string $pluralModelLabel = 'Órdenes de Servicio';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Equipo')
                    ->schema([
                        Forms\Components\Select::make('mobiliario_id')
                            ->label('Equipo')
                            ->relationship('mobiliario', 'numero_control')
                            ->searchable(['numero_control', 'descripcion'])
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                "{$record->numero_control} - {$record->descripcion}"
                            ),
                            
                        Forms\Components\DateTimePicker::make('fecha_programada')
                            ->label('Fecha Programada')
                            ->required(),
                            
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo del Mantenimiento')
                            ->required()
                            ->rows(3),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Detalles del Mantenimiento')
                    ->schema([
                        Forms\Components\Select::make('tipo_mantenimiento')
                            ->label('Tipo de Mantenimiento')
                            ->options([
                                'mantenimiento' => 'Mantenimiento Interno',
                                'proveedor' => 'Proveedor Externo',
                            ])
                            ->required()
                            ->reactive(),
                            
                        Forms\Components\TextInput::make('proveedor_nombre')
                            ->label('Nombre del Proveedor')
                            ->visible(fn (Forms\Get $get) => $get('tipo_mantenimiento') === 'proveedor')
                            ->required(fn (Forms\Get $get) => $get('tipo_mantenimiento') === 'proveedor'),
                            
                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'aceptado' => 'Aceptado',
                                'completado' => 'Completado',
                                'rechazado' => 'Rechazado',
                            ])
                            ->default('pendiente')
                            ->required(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Select::make('usuario_solicitante_id')
                            ->label('Solicitado por')
                            ->relationship('usuarioSolicitante', 'name')
                            ->default(Auth::id())
                            ->required(),
                            
                        Forms\Components\Select::make('usuario_mantenimiento_id')
                            ->label('Asignado a')
                            ->relationship('usuarioMantenimiento', 'name')
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(3),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('folio_vale')
                    ->label('Folio Vale')
                    ->searchable()
                    ->placeholder('Sin folio')
                    ->sortable(),
                    
                TextColumn::make('mobiliario.numero_control')
                    ->label('Número de Control')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('mobiliario.descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(30),
                    
                TextColumn::make('fecha_programada')
                    ->label('Fecha Programada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'aceptado',
                        'primary' => 'completado',
                        'danger' => 'rechazado',
                    ]),
                    
                BadgeColumn::make('tipo_mantenimiento')
                    ->label('Tipo')
                    ->colors([
                        'info' => 'mantenimiento',
                        'secondary' => 'proveedor',
                    ]),
                    
                TextColumn::make('usuarioSolicitante.name')
                    ->label('Solicitado por')
                    ->searchable(),
                    
                TextColumn::make('usuarioMantenimiento.name')
                    ->label('Asignado a')
                    ->placeholder('Sin asignar')
                    ->searchable(),
                    
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                // Por defecto, ocultar los completados y rechazados
                if (!request()->has('tableFilters')) {
                    $query->whereNotIn('estado', ['completado', 'rechazado']);
                }
            })
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aceptado' => 'Aceptado',
                        'completado' => 'Completado',
                        'rechazado' => 'Rechazado',
                    ])
                    ->default(null),
                    
                SelectFilter::make('mostrar_todos')
                    ->label('Mostrar')
                    ->options([
                        'activos' => 'Solo Activos (Pendientes y Aceptados)',
                        'completados' => 'Solo Completados',
                        'todos' => 'Todos los Mantenimientos',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            match($data['value']) {
                                'activos' => $query->whereIn('estado', ['pendiente', 'aceptado']),
                                'completados' => $query->where('estado', 'completado'),
                                'todos' => $query, // No aplicar filtro
                            };
                        }
                    })
                    ->default('activos'),
                    
                SelectFilter::make('tipo_mantenimiento')
                    ->label('Tipo de Mantenimiento')
                    ->options([
                        'mantenimiento' => 'Interno',
                        'proveedor' => 'Proveedor',
                    ]),
                    
                SelectFilter::make('usuario_mantenimiento_id')
                    ->label('Asignado a')
                    ->relationship('usuarioMantenimiento', 'name'),
            ])
            ->actions([
                Action::make('aceptar')
                    ->label('Aceptar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Mantenimiento $record) {
                        $record->aceptar(Auth::id());
                        
                        Notification::make()
                            ->title('Mantenimiento Aceptado')
                            ->body("Se generó el vale: {$record->folio_vale}")
                            ->success()
                            ->send();
                            
                        // Notificar al administrador
                        \App\Services\AdminNotificationService::mantenimientoAceptado(
                            Auth::user(),
                            $record->mobiliario,
                            $record
                        );
                    })
                    ->visible(fn (Mantenimiento $record) => $record->estado === 'pendiente')
                    ->requiresConfirmation(),
                    
                Action::make('completar')
                    ->label('Completar')
                    ->icon('heroicon-o-check-badge')
                    ->color('primary')
                    ->form([
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones Finales')
                            ->placeholder('Descripción del trabajo realizado...')
                            ->rows(4),
                    ])
                    ->action(function (array $data, Mantenimiento $record) {
                        $mobiliario = $record->mobiliario;
                        $record->completar($data['observaciones'] ?? null);
                        
                        Notification::make()
                            ->title('Mantenimiento Completado')
                            ->body("El equipo {$mobiliario->numero_control} ha vuelto a estar disponible para edición.")
                            ->success()
                            ->send();
                            
                        // Notificar al administrador
                        \App\Services\AdminNotificationService::notify(
                            'Mantenimiento Completado',
                            "El usuario " . Auth::user()->name . " ha completado el mantenimiento del equipo: {$mobiliario->numero_control}. El equipo está nuevamente disponible.",
                            'mantenimiento.completado',
                            Auth::user(),
                            [
                                'mantenimiento_id' => $record->id,
                                'mobiliario_id' => $mobiliario->id,
                                'numero_control' => $mobiliario->numero_control,
                            ]
                        );
                    })
                    ->visible(fn (Mantenimiento $record) => 
                        $record->estado === 'aceptado' && 
                        (Auth::user()->roles->contains('name', 'Personal de Mantenimiento') || 
                         Auth::user()->roles->contains('name', 'Administrador'))
                    ),
                    
                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Motivo del Rechazo')
                            ->required()
                            ->placeholder('Explica por qué se rechaza la solicitud...')
                            ->rows(3),
                    ])
                    ->action(function (array $data, Mantenimiento $record) {
                        $record->rechazar($data['observaciones']);
                        
                        Notification::make()
                            ->title('Mantenimiento Rechazado')
                            ->body('La solicitud ha sido rechazada.')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Mantenimiento $record) => $record->estado === 'pendiente')
                    ->requiresConfirmation(),
                    
                Action::make('generarVale')
                    ->label('Ver Vale')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn (Mantenimiento $record) => MantenimientoResource::getUrl('previsualizar', ['record' => $record]))
                    ->visible(fn (Mantenimiento $record) => 
                        $record->estado === 'aceptado' && !empty($record->folio_vale)
                    ),
                    
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalles'),
                    
                Tables\Actions\EditAction::make()
                    ->visible(fn (Mantenimiento $record) => 
                        $record->estado === 'pendiente' || Auth::user()->roles->contains('name', 'Administrador')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->roles->contains('name', 'Administrador')),
                ]),
            ]);
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
            'index' => Pages\ListMantenimientos::route('/'),
            'create' => Pages\CreateMantenimiento::route('/create'),
            'edit' => Pages\EditMantenimiento::route('/{record}/edit'),
            'previsualizar' => Pages\PrevisualizarVale::route('/{record}/vale-preview'),
        ];
    }
}
