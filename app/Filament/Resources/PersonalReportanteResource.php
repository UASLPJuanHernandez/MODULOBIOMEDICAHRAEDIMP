<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalReportanteResource\Pages;
use App\Models\PersonalReportante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class PersonalReportanteResource extends Resource
{
    protected static ?string $model = PersonalReportante::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Personal reportante';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?string $modelLabel = 'Personal';
    protected static ?string $pluralModelLabel = 'Personal reportante';
    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        $pendientes = PersonalReportante::where('estado', 'pendiente')->count();
        return $pendientes > 0 ? (string) $pendientes : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')->label('Nombre')->disabled(),
            Forms\Components\TextInput::make('numero_empleado')->label('Número de empleado')->disabled(),
            Forms\Components\TextInput::make('servicio')->label('Servicio / Área')->disabled(),
            Forms\Components\Select::make('estado')
                ->label('Estado')
                ->options([
                    'pendiente' => 'Pendiente',
                    'aprobado'  => 'Aprobado',
                    'rechazado' => 'Rechazado',
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero_empleado')
                    ->label('No. Empleado')
                    ->searchable(),
                Tables\Columns\TextColumn::make('servicio')
                    ->label('Servicio / Área')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'aprobado',
                        'danger'  => 'rechazado',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pendiente' => 'Pendiente',
                        'aprobado'  => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Solicitó')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobado'  => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ]),
            ])
            ->actions([
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PersonalReportante $record) => $record->estado !== 'aprobado')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar registro')
                    ->modalDescription(fn (PersonalReportante $record) => "¿Aprobar el acceso de {$record->nombre} ({$record->servicio})?")
                    ->action(fn (PersonalReportante $record) => $record->update(['estado' => 'aprobado'])),

                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PersonalReportante $record) => $record->estado !== 'rechazado')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar registro')
                    ->modalDescription(fn (PersonalReportante $record) => "¿Rechazar la solicitud de {$record->nombre}?")
                    ->action(fn (PersonalReportante $record) => $record->update(['estado' => 'rechazado'])),

                Tables\Actions\DeleteAction::make()->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersonalReportante::route('/'),
        ];
    }
}
