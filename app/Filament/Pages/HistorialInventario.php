<?php

namespace App\Filament\Pages;

use App\Models\InventarioEquipoHistorial;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class HistorialInventario extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Historial General';
    protected static ?string $title           = 'Historial General del Inventario';
    protected static string  $view            = 'filament.pages.historial-inventario';
    protected static bool    $shouldRegisterNavigation = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InventarioEquipoHistorial::query()
                    ->with('inventarioEquipo')
                    ->latest()
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->width('155px'),

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

                TextColumn::make('inventarioEquipo.numero_inventario')
                    ->label('No. Inventario')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->width('120px')
                    ->placeholder('(eliminado)')
                    ->url(fn ($record) => $record->inventarioEquipo
                        ? route('filament.admin.resources.inventario-equipos.view', $record->inventarioEquipo)
                        : null)
                    ->openUrlInNewTab(),

                TextColumn::make('inventarioEquipo.equipo')
                    ->label('Equipo')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->placeholder('(eliminado)'),

                TextColumn::make('inventarioEquipo.area')
                    ->label('Área')
                    ->searchable()
                    ->limit(30)
                    ->toggleable()
                    ->placeholder('—'),

                TextColumn::make('descripcion')
                    ->label('Descripción del cambio')
                    ->wrap()
                    ->limit(70),

                TextColumn::make('usuario_nombre')
                    ->label('Usuario')
                    ->sortable()
                    ->width('140px'),
            ])
            ->filters([
                SelectFilter::make('tipo_evento')
                    ->label('Tipo de evento')
                    ->options([
                        'creado'      => 'Creado',
                        'actualizado' => 'Actualizado',
                        'eliminado'   => 'Eliminado',
                    ]),

                Filter::make('rango_fechas')
                    ->label('Rango de fechas')
                    ->form([
                        Grid::make(2)->schema([
                            DatePicker::make('desde')
                                ->label('Desde')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                            DatePicker::make('hasta')
                                ->label('Hasta')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['hasta'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['desde'] ?? null) {
                            $indicators[] = 'Desde: ' . Carbon::parse($data['desde'])->format('d/m/Y');
                        }
                        if ($data['hasta'] ?? null) {
                            $indicators[] = 'Hasta: ' . Carbon::parse($data['hasta'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),

                Filter::make('solo_hoy')
                    ->label('Solo hoy')
                    ->query(fn (Builder $q) => $q->whereDate('created_at', today()))
                    ->toggle(),

                Filter::make('esta_semana')
                    ->label('Esta semana')
                    ->query(fn (Builder $q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->toggle(),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Action::make('ver_detalle')
                    ->label('Detalle')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Detalle — ' . $record->created_at->format('d/m/Y H:i:s'))
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

                Action::make('ir_equipo')
                    ->label('Ver equipo')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn ($record) => $record->inventarioEquipo
                        ? route('filament.admin.resources.inventario-equipos.view', $record->inventarioEquipo)
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->inventarioEquipo !== null),
            ])
            ->headerActions([
                Action::make('exportar_pdf')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('danger')
                    ->url(fn () => $this->buildPdfUrl())
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100]);
    }

    protected function buildPdfUrl(): string
    {
        $params = [];

        // Capturar los filtros activos de la tabla
        $tableFilters = $this->tableFilters ?? [];

        if (!empty($tableFilters['rango_fechas']['desde'])) {
            $params['desde'] = $tableFilters['rango_fechas']['desde'];
        }
        if (!empty($tableFilters['rango_fechas']['hasta'])) {
            $params['hasta'] = $tableFilters['rango_fechas']['hasta'];
        }
        if (!empty($tableFilters['tipo_evento']['value'])) {
            $params['tipo'] = $tableFilters['tipo_evento']['value'];
        }
        if (!empty($tableFilters['solo_hoy']['isActive'])) {
            $params['hoy'] = '1';
        }

        return route('inventario.historial-general.pdf', $params);
    }
}
