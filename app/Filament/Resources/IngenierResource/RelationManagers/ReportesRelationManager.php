<?php

namespace App\Filament\Resources\IngenierResource\RelationManagers;

use App\Models\ReportePizarron;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class ReportesRelationManager extends RelationManager
{
    protected static string $relationship = 'reportes';

    protected static ?string $title = 'Historial de actividades';

    protected static ?string $icon = 'heroicon-o-clipboard-document-list';

    protected static bool $shouldSkipAuthorization = true;

    protected function getTableQuery(): Builder
    {
        return ReportePizarron::query()
            ->with(['bitacora', 'firmaSolicitud'])
            ->where('responsable', $this->getOwnerRecord()->nombre);
    }

    private function calcularEstadoActividad(ReportePizarron $record): string
    {
        if ($record->concretado) {
            return 'concretado';
        }

        if ($record->firmaSolicitud && $record->firmaSolicitud->estado === 'pendiente') {
            return 'espera_firma';
        }

        if ($record->bitacora) {
            return 'espera_firma';
        }

        return 'espera_envio';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('titulo')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->width('100px'),

                TextColumn::make('titulo')
                    ->label('Equipo / Descripción')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),

                TextColumn::make('ubicacion')
                    ->label('Área / Ubicación')
                    ->limit(28)
                    ->placeholder('—'),

                TextColumn::make('estado_actividad')
                    ->label('Estado')
                    ->state(fn (ReportePizarron $record): string => $this->calcularEstadoActividad($record))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'concretado'   => 'success',
                        'espera_firma' => 'warning',
                        'espera_envio' => 'gray',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'concretado'   => 'Concretado',
                        'espera_firma' => 'En espera de firma',
                        'espera_envio' => 'En espera de ser enviado',
                        default        => $state,
                    }),

                TextColumn::make('concretado_at')
                    ->label('Concretado el')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_actividad_filter')
                    ->label('Filtrar por estado')
                    ->options([
                        'espera_envio' => 'En espera de ser enviado',
                        'espera_firma' => 'En espera de firma',
                        'concretado'   => 'Concretado',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) return $query;
                        return match ($data['value']) {
                            'concretado'   => $query->where('concretado', true),
                            'espera_firma' => $query->where('concretado', false)
                                ->whereHas('bitacora'),
                            'espera_envio' => $query->where('concretado', false)
                                ->whereDoesntHave('bitacora'),
                            default        => $query,
                        };
                    }),
            ])
            ->headerActions([])
            ->actions([
                Action::make('ver_pdf')
                    ->label('Ver PDF')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('info')
                    ->visible(fn (ReportePizarron $record) => $record->bitacora !== null)
                    ->modalHeading(fn (ReportePizarron $record) => 'PDF — ' . $record->titulo)
                    ->modalContent(fn (ReportePizarron $record) => view(
                        'filament.ingenieros.bitacora-preview-modal',
                        ['bitacora' => $record->bitacora]
                    ))
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ])
            ->bulkActions([])
            ->striped()
            ->paginated([15, 25, 50])
            ->emptyStateHeading('Sin actividad registrada')
            ->emptyStateDescription('No hay reportes asignados a este ingeniero.');
    }
}
