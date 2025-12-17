<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditoriaResource\Pages;
use App\Models\Auditoria;
use App\Models\Localizacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AuditoriaResource extends Resource
{
    protected static ?string $model = Auditoria::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    
    protected static ?string $navigationLabel = 'Auditorías';
    
    protected static ?string $modelLabel = 'Auditoría';
    
    protected static ?string $pluralModelLabel = 'Auditorías';
    
    protected static ?string $navigationGroup = 'Gestión de Inventario';
    
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Auditoría')
                    ->schema([
                        Forms\Components\Select::make('ubicacion_id')
                            ->label('Ubicación a Auditar')
                            ->relationship('ubicacion', 'division')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->ubicacion_completa)
                            ->searchable(['division', 'sub_area', 'ubicacion'])
                            ->preload()
                            ->required()
                            ->helperText('Seleccione la ubicación donde se realizará la auditoría'),
                            
                        Forms\Components\TextInput::make('responsable_nombre')
                            ->label('Nombre del Responsable del Área')
                            ->required()
                            ->helperText('Nombre de la persona responsable que firmará el reporte'),
                            
                        Forms\Components\DateTimePicker::make('fecha_inicio')
                            ->label('Fecha de Inicio')
                            ->default(now())
                            ->required(),
                            
                        Forms\Components\Textarea::make('observaciones_generales')
                            ->label('Observaciones Generales')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('ubicacion.ubicacion_completa')
                    ->label('Ubicación')
                    ->searchable(['ubicacion.division', 'ubicacion.sub_area'])
                    ->sortable()
                    ->wrap(),
                    
                TextColumn::make('usuario.name')
                    ->label('Auditor')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('responsable_nombre')
                    ->label('Responsable del Área')
                    ->searchable()
                    ->wrap(),
                    
                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'en_progreso',
                        'success' => 'completada',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_progreso' => 'En Progreso',
                        'completada' => 'Completada',
                        default => $state,
                    }),
                    
                TextColumn::make('total_mobiliarios')
                    ->label('Total')
                    ->alignCenter()
                    ->sortable(),
                    
                TextColumn::make('mobiliarios_presentes')
                    ->label('Presentes')
                    ->alignCenter()
                    ->color('success')
                    ->sortable(),
                    
                TextColumn::make('mobiliarios_ausentes')
                    ->label('Ausentes')
                    ->alignCenter()
                    ->color('danger')
                    ->sortable(),
                    
                TextColumn::make('vales_generados')
                    ->label('Vales')
                    ->alignCenter()
                    ->color('warning')
                    ->sortable(),
                    
                TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                TextColumn::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'en_progreso' => 'En Progreso',
                        'completada' => 'Completada',
                    ]),
                    
                SelectFilter::make('ubicacion')
                    ->relationship('ubicacion', 'division')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->ubicacion_completa)
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('ejecutar')
                    ->label('Ejecutar')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->visible(fn (Auditoria $record) => $record->estaEnProgreso())
                    ->url(fn (Auditoria $record): string => AuditoriaResource::getUrl('ejecutar', ['record' => $record])),
                    
                Action::make('reporte')
                    ->label('Ver Reporte')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn (Auditoria $record) => $record->estaCompletada())
                    ->url(fn (Auditoria $record): string => AuditoriaResource::getUrl('previsualizar', ['record' => $record])),
                    
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Auditoria $record) => $record->estaEnProgreso()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Auditoria $record) => $record->estaEnProgreso()),
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
            'index' => Pages\ListAuditorias::route('/'),
            'create' => Pages\CreateAuditoria::route('/create'),
            'view' => Pages\ViewAuditoria::route('/{record}'),
            'edit' => Pages\EditAuditoria::route('/{record}/edit'),
            'ejecutar' => Pages\EjecutarAuditoria::route('/{record}/ejecutar'),
            'previsualizar' => Pages\PrevisualizarReporte::route('/{record}/reporte'),
        ];
    }
}
