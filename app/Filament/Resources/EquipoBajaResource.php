<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipoBajaResource\Pages;
use App\Models\Mobiliario;
use App\Models\ClasificacionBien;
use App\Models\TipoMobiliario;
use App\Models\Localizacion;
use App\Models\Proveedor;
use App\Models\Vale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class EquipoBajaResource extends Resource
{
    protected static ?string $model = Mobiliario::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    
    protected static ?string $navigationLabel = 'Equipos Dados de Baja';
    
    protected static ?string $pluralLabel = 'Equipos Dados de Baja';
    
    public static function exportarExcelFiltrado($records, $filename = null)
    {
        $filename = $filename ?: 'equipos_dados_de_baja_filtrados_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        // Obtener datos de los registros
        $data = $records->map(function ($record) {
            return static::exportarTodosLosDatosFiltrados($record);
        })->toArray();
        
        // Crear Excel
        $excel = static::crearExcelConDatos($data, 'Equipos Baja');
        
        // Crear archivo temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'equipos_dados_de_baja');
        $writer = new Xlsx($excel);
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
    
    public static function exportarTodosLosDatosFiltrados($record)
    {
        $data = [
            'ID' => $record->id,
            'Folio' => $record->folio ?? 'N/A',
            'Descripción' => $record->descripcion ?? 'N/A',
            'Número de Inventario' => $record->numero_inventario ?? 'N/A',
            'Marca' => $record->marca ?? 'N/A',
            'Modelo' => $record->modelo ?? 'N/A',
            'Número de Serie' => $record->numero_serie ?? 'N/A',
            'Precio Original' => $record->precio ? '$' . number_format($record->precio, 2) : 'N/A',
            'Tipo de Mobiliario' => $record->tipoMobiliario?->tipo ?? 'N/A',
            'División' => $record->localizacion?->division ?? 'N/A',
            'Dirección' => $record->localizacion?->direccion ?? 'N/A',
            'Subdirección' => $record->localizacion?->subdireccion ?? 'N/A',
            'Unidad' => $record->localizacion?->unidad ?? 'N/A',
            'Método de Adquisición' => $record->metodo_adquisicion ?? 'N/A',
            'Estado del Mobiliario' => $record->estado_mobiliario ?? 'N/A',
            'Fecha de Baja' => $record->fecha_baja ? Carbon::parse($record->fecha_baja)->format('d/m/Y') : 'N/A',
            'Motivo de Baja' => $record->motivo_baja ?? 'N/A',
            'Observaciones' => $record->observaciones ?? 'N/A',
            'Creado por' => $record->usuarioCreador?->name ?? 'Sistema',
            'Fecha de Creación' => $record->created_at ? Carbon::parse($record->created_at)->format('d/m/Y H:i') : 'N/A',
            'Modificado por' => $record->usuarioEditor?->name ?? 'N/A',
            'Fecha de Modificación' => $record->updated_at ? Carbon::parse($record->updated_at)->format('d/m/Y H:i') : 'N/A',
        ];
        
        return $data;
    }
    
    public static function crearExcelConDatos($data, $titulo = 'Equipos Baja')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($titulo);
        
        if (empty($data)) {
            $sheet->setCellValue('A1', 'No hay datos para mostrar');
            return $spreadsheet;
        }
        
        // Título principal
        $sheet->setCellValue('A1', $titulo);
        $sheet->mergeCells('A1:V1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE6F3FF');
        
        // Información de generación
        $fechaGeneracion = 'Generado el: ' . Carbon::now()->format('d/m/Y H:i:s');
        $totalRegistros = 'Total de equipos: ' . count($data);
        
        $sheet->setCellValue('A2', $fechaGeneracion);
        $sheet->setCellValue('A3', $totalRegistros);
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);
        
        // Encabezados de columnas (fila 5)
        $headers = array_keys($data[0]);
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', $header);
            $sheet->getStyle($col . '5')->getFont()->setBold(true);
            $sheet->getStyle($col . '5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EDF7');
            $sheet->getStyle($col . '5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }
        
        // Datos
        $row = 6;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($item as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        
        // Ajustar ancho de columnas
        $lastCol = chr(64 + count($headers));
        for ($col = 'A'; $col <= $lastCol; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bordes para toda la tabla
        $lastRow = $row - 1;
        $sheet->getStyle('A5:' . $lastCol . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
            
        // Bordes para el título
        $sheet->getStyle('A1:V1')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_MEDIUM);
        
        return $spreadsheet;
    }
    
    protected static ?string $modelLabel = 'Equipo Dado de Baja';
    
    protected static ?string $pluralModelLabel = 'Equipos Dados de Baja';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('numero_control')
                                ->label('Número de Control')
                                ->required()
                                ->readonly()
                                ->maxLength(255),
                                
                            Forms\Components\Select::make('clasificacion_bienes_id')
                                ->label('Clasificación del Bien')
                                ->relationship('clasificacionBien', 'descripcion_clase')
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->descripcion_completa)
                                ->searchable()
                                ->preload()
                                ->disabled(),
                        ]),
                        
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->required()
                            ->readonly()
                            ->rows(3),
                            
                        Forms\Components\Textarea::make('caracteristicas')
                            ->label('Características')
                            ->readonly()
                            ->rows(3),
                    ]),

                Section::make('Especificaciones Técnicas')
                    ->schema([
                        Grid::make(3)->schema([
                            Forms\Components\TextInput::make('marca')
                                ->label('Marca')
                                ->required()
                                ->readonly()
                                ->maxLength(100),
                                
                            Forms\Components\TextInput::make('modelo')
                                ->label('Modelo')
                                ->required()
                                ->readonly()
                                ->maxLength(100),
                                
                            Forms\Components\TextInput::make('numero_serie')
                                ->label('Número de Serie')
                                ->readonly()
                                ->maxLength(100),
                        ]),
                    ]),

                Section::make('Información de Baja')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\DateTimePicker::make('fecha_baja')
                                ->label('Fecha de Baja')
                                ->readonly(),
                                
                            Forms\Components\TextInput::make('precio')
                                ->label('Precio Original (MXN)')
                                ->numeric()
                                ->readonly()
                                ->prefix('$'),
                        ]),
                        
                        Forms\Components\Textarea::make('motivo_baja')
                            ->label('Motivo de Baja')
                            ->readonly()
                            ->rows(3),
                    ]),

                Section::make('Ubicación y Responsable')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('localizacion_id')
                                ->label('Ubicación')
                                ->relationship('localizacion', 'division')
                                ->searchable()
                                ->preload()
                                ->disabled(),
                                
                            Forms\Components\Select::make('tipo_mobiliario_id')
                                ->label('Tipo de Mobiliario')
                                ->relationship('tipoMobiliario', 'tipo')
                                ->searchable()
                                ->preload()
                                ->disabled(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_control')
                    ->label('Número de Control')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50),
                    
                Tables\Columns\TextColumn::make('marca')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('modelo')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('numero_serie')
                    ->label('Número de Serie')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('tipoMobiliario.tipo')
                    ->label('Tipo')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Equipo Médico' => 'success',
                        'Equipo no Médico' => 'info',
                        default => 'gray',
                    })
                    ->placeholder('Sin tipo'),
                    
                Tables\Columns\TextColumn::make('precio')
                    ->label('Precio Original')
                    ->money('MXN')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('fecha_baja')
                    ->label('Fecha de Baja')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('motivo_baja')
                    ->label('Motivo de Baja')
                    ->limit(30)
                    ->searchable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                    
                Tables\Columns\TextColumn::make('metodo_adquisicion')
                    ->label('Adquisición')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Compra' => 'success',
                        'Donación' => 'info',
                        'Comodato' => 'warning',
                        'IMSS-Bienestar' => 'success',
                        'Equipo Personal' => 'gray',
                        'Otros' => 'gray',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('estado_mobiliario')
                    ->label('Estado')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Nuevo' => 'success',
                        'Usado' => 'info',
                        'Baja' => 'danger',
                        'Restaurado' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('localizacion.direccion')
                    ->label('Dirección')
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(function ($record) {
                        return $record->localizacion?->direccion ?? 'Sin dirección';
                    })
                    ->placeholder('Sin dirección'),
                    
                Tables\Columns\TextColumn::make('localizacion.division')
                    ->label('División')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(function ($record) {
                        return $record->localizacion?->division ?? 'Sin división';
                    })
                    ->placeholder('Sin división'),
                    
                Tables\Columns\TextColumn::make('localizacion.sub_area')
                    ->label('Sub Área')
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(function ($record) {
                        return $record->localizacion?->sub_area ?? 'Sin sub área';
                    })
                    ->placeholder('Sin sub área'),
                    
                Tables\Columns\TextColumn::make('localizacion.ubicacion')
                    ->label('Ubicación')
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->formatStateUsing(function ($record) {
                        return $record->localizacion?->ubicacion ?? 'Sin ubicación';
                    })
                    ->placeholder('Sin ubicación'),
                    
                Tables\Columns\TextColumn::make('estado_ubicacion')
                    ->label('Estado Ubicación')
                    ->formatStateUsing(function ($record) {
                        $tieneMovimientos = $record->movimientos->count() > 0;
                        return $tieneMovimientos ? 'Con movimientos (' . $record->movimientos->count() . ')' : 'Ubicación original';
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, 'movimientos') ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('tipoMobiliario.tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Equipo Médico' => 'success',
                        'Equipo no Médico' => 'info',
                        default => 'gray',
                    }),
                    
                // Columnas del Vale asociado
                Tables\Columns\TextColumn::make('ultimo_vale_tipo')
                    ->label('Tipo Vale')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Entrega' => 'success',
                        'Devolución' => 'warning',
                        'Transferencia' => 'info',
                        default => 'gray',
                    })
                    ->placeholder('Sin vale')
                    ->formatStateUsing(function ($record) {
                        $ultimoVale = $record->vales->first();
                        return $ultimoVale ? $ultimoVale->tipo_vale : 'Sin vale';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('vale_responsable')
                    ->label('Responsable Vale')
                    ->formatStateUsing(function ($record) {
                        $ultimoVale = $record->vales->first();
                        if (!$ultimoVale) return 'Sin vale';
                        
                        $responsable = $ultimoVale->responsable_recibe ?: $ultimoVale->responsable_entrega;
                        return $responsable ?: 'Sin responsable';
                    })
                    ->placeholder('Sin responsable')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('usuarioCreador.name')
                    ->label('Creado por')
                    ->formatStateUsing(function ($record) {
                        return $record->usuarioCreador?->name ?? 'Sistema';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('usuarioEditor.name')
                    ->label('Modificado por')
                    ->formatStateUsing(function ($record) {
                        return $record->usuarioEditor?->name ?? 'N/A';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportarExcel')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (\Livewire\Component $livewire) {
                        // Obtener query con filtros aplicados
                        $query = $livewire->getFilteredTableQuery();
                        $records = $query->get();
                        
                        if ($records->isEmpty()) {
                            Notification::make()
                                ->title('Sin datos para exportar')
                                ->body('No hay equipos dados de baja que coincidan con los filtros aplicados.')
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        return static::exportarExcelFiltrado($records);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Exportar Equipos Dados de Baja a Excel')
                    ->modalDescription('Se exportarán todos los equipos dados de baja que coincidan con los filtros aplicados.')
                    ->modalSubmitActionLabel('Exportar'),
                    
                Tables\Actions\Action::make('generarReporteBaja')
                    ->label('Generar Reporte de Bajas')
                    ->icon('heroicon-o-document')
                    ->color('danger')
                    ->form([
                        Forms\Components\CheckboxList::make('campos_seleccionados')
                            ->label('Campos a Incluir en el Reporte')
                            ->options([
                                'descripcion' => 'Descripción',
                                'numero_inventario' => 'Número de Inventario',
                                'marca' => 'Marca',
                                'modelo' => 'Modelo',
                                'numero_serie' => 'Número de Serie',
                                'precio' => 'Precio Original',
                                'ubicacion' => 'Última Ubicación',
                                'fecha_baja' => 'Fecha de Baja',
                                'motivo_baja' => 'Motivo de Baja',
                                'estado_mobiliario' => 'Estado del Mobiliario',
                                'metodo_adquisicion' => 'Método de Adquisición',
                            ])
                            ->columns(2)
                            ->required()
                            ->default(['descripcion', 'numero_inventario', 'marca', 'modelo', 'fecha_baja', 'motivo_baja']),
                    ])
                    ->action(function (array $data) {
                        try {
                            $camposSeleccionados = $data['campos_seleccionados'];
                            $filename = 'equipos_dados_de_baja_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                            
                            return \Maatwebsite\Excel\Facades\Excel::download(
                                new \App\Exports\EquipoBajaExport($camposSeleccionados),
                                $filename
                            );
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al generar reporte')
                                ->body('Ha ocurrido un error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Generar Reporte de Equipos Dados de Baja')
                    ->modalDescription('Selecciona los campos que deseas incluir en el reporte Excel.')
                    ->modalSubmitActionLabel('Generar Reporte'),
            ])
            ->filters([
                SelectFilter::make('tipo_mobiliario_id')
                    ->label('Tipo de Mobiliario')
                    ->relationship('tipoMobiliario', 'tipo', fn($query) => $query->orderBy('tipo')->orderBy('categoria'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->descripcion_completa)
                    ->searchable()
                    ->preload()
                    ->multiple(),
                    
                SelectFilter::make('categoria_mobiliario')
                    ->label('Categoría')
                    ->options(function () {
                        return \App\Models\TipoMobiliario::distinct()
                            ->whereNotNull('categoria')
                            ->pluck('categoria', 'categoria')
                            ->sort();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('tipoMobiliario', function ($q) use ($data) {
                                $q->where('categoria', $data['value']);
                            });
                        }
                    }),
                    
                SelectFilter::make('metodo_adquisicion')
                    ->label('Método de Adquisición')
                    ->options([
                        'Compra' => 'Compra',
                        'Donación' => 'Donación',
                        'Apoyo 55' => 'Apoyo 55',
                        'Comodato' => 'Comodato',
                        'Prestamo' => 'Prestamo',
                        'Propiedad UASLP' => 'Propiedad UASLP',
                        'IMSS-Bienestar' => 'IMSS-Bienestar',
                        'Equipo Personal' => 'Equipo Personal',
                        'Otros' => 'Otros',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('estado_mobiliario')
                    ->label('Estado del Mobiliario')
                    ->options([
                        'Nuevo' => 'Nuevo',
                        'Usado' => 'Usado',
                        'Baja' => 'Baja',
                        'Restaurado' => 'Restaurado',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('localizacion_division')
                    ->label('División')
                    ->options(function () {
                        return \App\Models\Localizacion::distinct()
                            ->whereNotNull('division')
                            ->pluck('division', 'division')
                            ->sort();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('localizacion', function ($q) use ($data) {
                                $q->where('division', $data['value']);
                            });
                        }
                    }),
                    
                SelectFilter::make('localizacion_direccion')
                    ->label('Dirección')
                    ->options(function () {
                        return \App\Models\Localizacion::distinct()
                            ->whereNotNull('direccion')
                            ->pluck('direccion', 'direccion')
                            ->sort();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('localizacion', function ($q) use ($data) {
                                $q->where('direccion', $data['value']);
                            });
                        }
                    }),
                    
                SelectFilter::make('con_movimientos')
                    ->label('Estado de Movimientos')
                    ->options([
                        'con_movimientos' => 'Con movimientos',
                        'sin_movimientos' => 'Sin movimientos',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'con_movimientos') {
                            $query->has('movimientos');
                        } elseif ($data['value'] === 'sin_movimientos') {
                            $query->doesntHave('movimientos');
                        }
                    }),
                    
                Filter::make('fecha_baja')
                    ->form([
                        Grid::make(2)->schema([
                            DatePicker::make('fecha_baja_desde')
                                ->label('Fecha baja desde'),
                            DatePicker::make('fecha_baja_hasta')
                                ->label('Fecha baja hasta'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_baja_desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_baja', '>=', $date),
                            )
                            ->when(
                                $data['fecha_baja_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_baja', '<=', $date),
                            );
                    }),
                    
                Filter::make('precio_range')
                    ->form([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('precio_min')
                                ->label('Precio mínimo')
                                ->numeric()
                                ->prefix('$'),
                            Forms\Components\TextInput::make('precio_max')
                                ->label('Precio máximo')
                                ->numeric()
                                ->prefix('$'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['precio_min'],
                                fn (Builder $query, $price): Builder => $query->where('precio', '>=', $price),
                            )
                            ->when(
                                $data['precio_max'],
                                fn (Builder $query, $price): Builder => $query->where('precio', '<=', $price),
                            );
                    }),
                    
                SelectFilter::make('created_by')
                    ->label('Creado por')
                    ->relationship('usuarioCreador', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('updated_by')
                    ->label('Modificado por')
                    ->relationship('usuarioEditor', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Action::make('verHistorial')
                    ->label('Ver Historial')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->modalHeading('Historial de Movimientos')
                    ->modalContent(function (Mobiliario $record) {
                        $movimientos = $record->movimientos()
                            ->with(['areaActual', 'areaAnterior', 'usuario'])
                            ->orderBy('fecha_movimiento', 'desc')
                            ->get();
                            
                        if ($movimientos->isEmpty()) {
                            return view('historial-vacio');
                        }
                        
                        return view('historial-movimientos', compact('movimientos', 'record'));
                    })
                    ->modalWidth('5xl')
                    ->slideOver(),
                
                Action::make('reactivar')
                    ->label('Reactivar Equipo')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Reactivar Equipo')
                    ->modalDescription('¿Estás seguro de que quieres reactivar este equipo? Volverá a aparecer en el inventario general.')
                    ->modalSubmitActionLabel('Sí, reactivar')
                    ->action(function (Mobiliario $record) {
                        $record->update([
                            'dado_de_baja' => false,
                            'fecha_baja' => null,
                            'motivo_baja' => null,
                        ]);
                        
                        Notification::make()
                            ->title('Equipo reactivado')
                            ->body('El equipo ha sido reactivado y aparecerá en el inventario general.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('exportarSeleccionados')
                        ->label('Exportar Seleccionados')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->title('Sin equipos seleccionados')
                                    ->body('Selecciona al menos un equipo para exportar.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            
                            return static::exportarExcelFiltrado($records, 'equipos_dados_de_baja_seleccionados_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Exportar Equipos Seleccionados')
                        ->modalDescription('Se exportarán los equipos dados de baja seleccionados.')
                        ->modalSubmitActionLabel('Exportar'),
                        
                    Tables\Actions\BulkAction::make('reactivar_multiple')
                        ->label('Reactivar Seleccionados')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Reactivar Equipos')
                        ->modalDescription('¿Estás seguro de que quieres reactivar los equipos seleccionados?')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function (Mobiliario $record) {
                                $record->update([
                                    'dado_de_baja' => false,
                                    'fecha_baja' => null,
                                    'motivo_baja' => null,
                                ]);
                            });
                            
                            Notification::make()
                                ->title('Equipos reactivados')
                                ->body('Se han reactivado ' . $records->count() . ' equipos.')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('fecha_baja', 'desc')
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->searchOnBlur();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->dadosDeBaja()
            ->with([
                'localizacion',
                'tipoMobiliario',
                'usuarioCreador',
                'usuarioEditor',
                'proveedor',
                'clasificacionBien',
                'ultimoMovimiento.areaActual',
                'movimientos' => function($query) {
                    $query->orderBy('fecha_movimiento', 'desc');
                },
                'vales' => function($query) {
                    $query->latest()->limit(1);
                }
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
            'index' => Pages\ListEquipoBajas::route('/'),
            'view' => Pages\ViewEquipoBaja::route('/{record}'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::dadosDeBaja()->count();
    }
    
    public static function canCreate(): bool
    {
        return false; // No se pueden crear equipos dados de baja directamente
    }
}
