<?php

namespace App\Filament\Resources\InventarioEquipoResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class HistorialesRelationManager extends RelationManager
{
    protected static string $relationship = 'historiales';

    protected static ?string $title = 'Historial de Cambios';

    protected static ?string $icon = 'heroicon-o-clock';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descripcion')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->width('160px'),

                BadgeColumn::make('tipo_evento')
                    ->label('Evento')
                    ->colors([
                        'success' => 'creado',
                        'info'    => 'actualizado',
                        'danger'  => 'eliminado',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'creado'      => 'Creado',
                        'actualizado' => 'Actualizado',
                        'eliminado'   => 'Eliminado',
                        default       => ucfirst($state),
                    })
                    ->width('110px'),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->wrap()
                    ->limit(80),

                TextColumn::make('usuario_nombre')
                    ->label('Usuario')
                    ->sortable()
                    ->width('160px'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Action::make('ver_cambios')
                    ->label('Ver detalle')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Detalle del cambio — ' . $record->created_at->format('d/m/Y H:i:s'))
                    ->modalContent(function ($record) {
                        $cambios  = $record->cambios ?? [];
                        $tipo     = $record->tipo_evento;
                        $usuario  = $record->usuario_nombre ?? 'Desconocido';
                        $fecha    = $record->created_at->format('d/m/Y H:i:s');
                        $ip       = $record->ip_address ?? '—';

                        return view('filament.inventario.historial-detalle', compact(
                            'cambios', 'tipo', 'usuario', 'fecha', 'ip', 'record'
                        ));
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->visible(fn ($record) => !empty($record->cambios)),
            ])
            ->bulkActions([])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
