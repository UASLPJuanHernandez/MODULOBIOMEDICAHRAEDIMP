<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MobiliarioResource\Pages;
use App\Filament\Imports\MobiliarioImporter;
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
use Endroid\QrCode\Builder\Builder as QrBuilder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Response;
use Filament\Notifications\Notification;
use App\Services\PDFService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class MobiliarioResource extends Resource
{
    protected static ?string $model = Mobiliario::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    
    protected static ?string $navigationLabel = 'Inventario';
    
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
    
    protected static ?string $modelLabel = 'Mobiliario';
    
    protected static ?string $pluralModelLabel = 'Mobiliario';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('tipo_mobiliario_id')
                                ->label('Tipo de Mobiliario')
                                ->relationship('tipoMobiliario', 'id')
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->descripcion_completa)
                                ->required()
                                ->reactive()
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),
                                
                            Forms\Components\TextInput::make('numero_control')
                                ->label('Número de Control')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('Se generará automáticamente')
                                ->columnSpan(1),
                        ]),
                        
                        Grid::make(1)->schema([
                            Forms\Components\TextInput::make('numero_inventario')
                                ->label('Número de Inventario (Anterior)')
                                ->maxLength(255)
                                ->placeholder('Ej: 2023-HRAE-456 (opcional, solo si ya tenía número asignado)')
                                ->helperText('Número de inventario del sistema anterior (Excel). Dejar vacío para equipos nuevos.')
                                ->columnSpan(1),
                        ]),
                        
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('clasificacion_bienes_id')
                                ->label('Clasificación de Bienes')
                                ->relationship('clasificacionBien', 'id')
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->descripcion_completa)
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),
                                
                            Forms\Components\Select::make('estado_mobiliario')
                                ->label('Estado del Mobiliario')
                                ->options([
                                    'Nuevo' => 'Nuevo',
                                    'Usado' => 'Usado',
                                    'Baja' => 'Baja',
                                    'Restaurado' => 'Restaurado',
                                ])
                                ->required()
                                ->reactive()
                                ->columnSpan(1),
                        ]),
                        
                        // Campos condicionales para cuando el estado es "Baja"
                        Grid::make(2)->schema([
                            Forms\Components\DatePicker::make('fecha_baja')
                                ->label('Fecha de Baja')
                                ->visible(fn (Get $get) => $get('estado_mobiliario') === 'Baja')
                                ->required(fn (Get $get) => $get('estado_mobiliario') === 'Baja')
                                ->default(now())
                                ->helperText('Fecha en que el equipo fue dado de baja'),
                                
                            Forms\Components\TextInput::make('motivo_baja')
                                ->label('Motivo de Baja')
                                ->maxLength(255)
                                ->visible(fn (Get $get) => $get('estado_mobiliario') === 'Baja')
                                ->required(fn (Get $get) => $get('estado_mobiliario') === 'Baja')
                                ->helperText('Especifique el motivo por el cual el equipo fue dado de baja'),
                        ])
                        ->visible(fn (Get $get) => $get('estado_mobiliario') === 'Baja'),
                        
                        // Campo oculto para establecer dado_de_baja automáticamente
                        Forms\Components\Hidden::make('dado_de_baja')
                            ->dehydrated()
                            ->dehydrateStateUsing(fn (Get $get) => $get('estado_mobiliario') === 'Baja'),
                        
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('caracteristicas')
                            ->label('Características')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Grid::make(1)->schema([
                            Forms\Components\Toggle::make('tiene_accesorios')
                                ->label('¿Este mobiliario tiene accesorios?')
                                ->reactive()
                                ->live(),
                                
                            Forms\Components\Textarea::make('descripcion_accesorios')
                                ->label('Descripción de Accesorios')
                                ->rows(2)
                                ->required(fn (Get $get) => $get('tiene_accesorios'))
                                ->hidden(fn (Get $get) => !$get('tiene_accesorios'))
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Detalles del Equipo')
                    ->schema([
                        Grid::make(3)->schema([
                            Forms\Components\TextInput::make('marca')
                                ->label('Marca')
                                ->required()
                                ->maxLength(255),
                                
                            Forms\Components\TextInput::make('modelo')
                                ->label('Modelo')
                                ->required()
                                ->maxLength(255),
                                
                            Forms\Components\TextInput::make('numero_serie')
                                ->label('Número de Serie')
                                ->maxLength(255)
                                ->placeholder('Opcional - Dejar vacío si no aplica')
                                ->helperText('Solo si el mobiliario cuenta con número de serie'),
                        ]),
                        
                        Forms\Components\FileUpload::make('foto')
                            ->label('Foto del Mobiliario')
                            ->image()
                            ->directory('mobiliarios')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120)
                            ->helperText('Suba una foto del mobiliario (opcional). Tamaño máximo: 5MB')
                            ->columnSpanFull(),
                        
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('precio')
                                ->label('Precio (MXN)')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->suffix('MXN')
                                ->step(0.01),
                                
                            Forms\Components\Select::make('metodo_adquisicion')
                                ->label('Método de Adquisición')
                                ->options([
                                    'Compra' => 'Compra',
                                    'Donación' => 'Donación',
                                    'Apoyo SS' => 'Apoyo SS',
                                    'Comodato' => 'Comodato',
                                    'Prestamo' => 'Prestamo',
                                    'Propiedad UASLP' => 'Propiedad UASLP',
                                    'IMSS-Bienestar' => 'IMSS-Bienestar',
                                    'Equipo Personal' => 'Equipo Personal',
                                    'Otros' => 'Otros',
                                ])
                                ->required()
                                ->reactive()
                                ->live(),
                        ]),
                        
                        // Campo donante (condicional para Donación)
                        Forms\Components\TextInput::make('donante')
                            ->label('Donante')
                            ->maxLength(255)
                            ->required(fn (Get $get) => $get('metodo_adquisicion') === 'Donación')
                            ->visible(fn (Get $get) => $get('metodo_adquisicion') === 'Donación')
                            ->placeholder('Ingrese el nombre del donante')
                            ->helperText('Especifique quién está donando el mobiliario'),
                        
                        // Funcionalidad condicional para número de folio
                        Grid::make(1)->schema([
                            Forms\Components\Toggle::make('tiene_folio')
                                ->label('¿Este mobiliario ya tiene asignado un número de folio?')
                                ->reactive()
                                ->live()
                                ->visible(fn (Get $get) => in_array($get('metodo_adquisicion'), ['Propiedad UASLP', 'Apoyo SS', 'Prestamo', 'IMSS-Bienestar']))
                                ->dehydrated(fn (Get $get) => in_array($get('metodo_adquisicion'), ['Propiedad UASLP', 'Apoyo SS', 'Prestamo', 'IMSS-Bienestar']))
                                ->helperText('Disponible solo para: UASLP (propiedad), Apoyo SS, Préstamo, IMSS-Bienestar'),
                                
                            Forms\Components\TextInput::make('numero_folio')
                                ->label('Número de Folio')
                                ->maxLength(100)
                                ->required(fn (Get $get) => $get('tiene_folio') && in_array($get('metodo_adquisicion'), ['Propiedad UASLP', 'Apoyo SS', 'Prestamo', 'IMSS-Bienestar']))
                                ->visible(fn (Get $get) => $get('tiene_folio') && in_array($get('metodo_adquisicion'), ['Propiedad UASLP', 'Apoyo SS', 'Prestamo', 'IMSS-Bienestar']))
                                ->dehydrated(fn (Get $get) => $get('tiene_folio') && in_array($get('metodo_adquisicion'), ['Propiedad UASLP', 'Apoyo SS', 'Prestamo', 'IMSS-Bienestar']))
                                ->placeholder('Ingrese el número de folio asignado')
                                ->helperText('Campo requerido cuando se indica que ya tiene folio asignado'),
                        ]),
                    ]),

                Section::make('Ubicación y Proveedor')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('localizacion_id')
                                ->label('Localización')
                                ->relationship('localizacion', 'id')
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->ubicacion_completa)
                                ->required()
                                ->searchable()
                                ->preload(),
                                
                            Forms\Components\Select::make('proveedor_id')
                                ->label('Proveedor')
                                ->relationship('proveedor', 'nombre_proveedor')
                                ->required(fn (Get $get) => in_array($get('metodo_adquisicion'), ['Compra', 'Comodato']))
                                ->searchable()
                                ->preload()
                                ->visible(fn (Get $get) => in_array($get('metodo_adquisicion'), ['Compra', 'Comodato']))
                                ->dehydrated(fn (Get $get) => in_array($get('metodo_adquisicion'), ['Compra', 'Comodato']))
                                ->helperText('Requerido solo para Compra y Comodato')
                                ->createOptionForm([
                                    Forms\Components\Select::make('partida_id')
                                        ->label('Tipo de Partida')
                                        ->relationship('tipoPartida', 'tipo_partida')
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\TextInput::make('nombre_proveedor')
                                        ->required(),
                                    Forms\Components\TextInput::make('monto_unitario')
                                        ->label('Monto Unitario (MXN)')
                                        ->numeric()
                                        ->prefix('$')
                                        ->suffix('MXN'),
                                    Forms\Components\TextInput::make('monto_total')
                                        ->label('Monto Total (MXN)')
                                        ->numeric()
                                        ->prefix('$')
                                        ->suffix('MXN'),
                                    Forms\Components\TextInput::make('cantidad_mobiliario')
                                        ->label('Cantidad de Mobiliario')
                                        ->numeric(),
                                ]),
                        ]),
                        
                        Forms\Components\TextInput::make('depreciacion_registrada')
                            ->label('Depreciación Registrada (MXN)')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('MXN')
                            ->step(0.01)
                            ->placeholder('Opcional'),
                    ]),

                Section::make('Asignación de Responsable')
                    ->description('Los datos del responsable se usarán para generar automáticamente el vale de resguardo')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('responsable_entrega')
                                ->label('Responsable de Entrega')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Nombre completo de quien entrega el mobiliario'),
                                
                            Forms\Components\TextInput::make('matricula_entrega')
                                ->label('Matrícula de quien Entrega')
                                ->maxLength(255)
                                ->helperText('Matrícula o identificación de quien entrega'),
                                
                            Forms\Components\TextInput::make('responsable_recibe')
                                ->label('Responsable que Recibe')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Nombre completo del responsable del resguardo'),
                                
                            Forms\Components\TextInput::make('matricula_recibe')
                                ->label('Matrícula de quien Recibe')
                                ->maxLength(255)
                                ->helperText('Matrícula o identificación del responsable'),
                        ]),
                        
                        Forms\Components\DatePicker::make('fecha_asignacion')
                            ->label('Fecha de Asignación')
                            ->default(now())
                            ->required()
                            ->helperText('Fecha en que se asigna el mobiliario al responsable'),
                        
                        Forms\Components\Textarea::make('observaciones_vale')
                            ->label('Observaciones para el Vale')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Observaciones adicionales que se incluirán en el vale de resguardo'),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['vales' => fn($q) => $q->latest()->limit(1)]))
            ->columns([
                Tables\Columns\IconColumn::make('en_mantenimiento')
                    ->label('')
                    ->icon(fn (Mobiliario $record) => 
                        $record->tieneMantenimientosActivos() ? 'heroicon-o-wrench-screwdriver' : null
                    )
                    ->color('warning')
                    ->size(Tables\Columns\IconColumn\IconColumnSize::Large)
                    ->tooltip(fn (Mobiliario $record) => 
                        $record->tieneMantenimientosActivos() 
                            ? '⚠️ EQUIPO EN MANTENIMIENTO - No se puede editar ni modificar' 
                            : null
                    ),
                    
                Tables\Columns\TextColumn::make('numero_control')
                    ->label('Número de Control')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('numero_inventario')
                    ->label('Núm. Inventario')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->placeholder('Sin número')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                    
                Tables\Columns\TextColumn::make('marca')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('modelo')
                    ->label('Modelo')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('numero_serie')
                    ->label('Número de Serie')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable()
                    ->placeholder('Sin número de serie')
                    ->color(fn ($state) => $state ? null : 'gray'),
                    
                Tables\Columns\TextColumn::make('tipoMobiliario.tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Equipo Médico' => 'success',
                        'Equipo no Médico' => 'info',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('estado_mantenimiento')
                    ->label('Mantenimiento')
                    ->formatStateUsing(function ($record) {
                        if ($record->tieneMantenimientosActivos()) {
                            $pendientes = $record->mantenimientosPendientes();
                            $aceptados = $record->mantenimientosAceptados();
                            
                            if ($aceptados > 0) {
                                return "En proceso ({$aceptados})";
                            }
                            return "Pendiente ({$pendientes})";
                        }
                        
                        $total = $record->totalMantenimientos();
                        if ($total > 0) {
                            return "Completados ({$total})";
                        }
                        
                        return 'Sin historial';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->tieneMantenimientosActivos()) {
                            return $record->mantenimientosAceptados() > 0 ? 'info' : 'warning';
                        }
                        
                        return $record->totalMantenimientos() > 0 ? 'success' : 'gray';
                    })
                    ->icon(function ($record) {
                        if ($record->tieneMantenimientosActivos()) {
                            return 'heroicon-o-wrench-screwdriver';
                        }
                        
                        return $record->totalMantenimientos() > 0 ? 'heroicon-o-check-badge' : 'heroicon-o-minus';
                    })
                    ->tooltip(function ($record) {
                        $resumen = $record->resumenMantenimientos();
                        
                        if ($resumen['total'] === 0) {
                            return 'Este equipo no tiene historial de mantenimientos';
                        }
                        
                        $tooltip = "Total: {$resumen['total']} mantenimientos\n";
                        $tooltip .= "Pendientes: {$resumen['por_estado']['pendiente']}\n";
                        $tooltip .= "En proceso: {$resumen['por_estado']['aceptado']}\n";
                        $tooltip .= "Completados: {$resumen['por_estado']['completado']}\n";
                        $tooltip .= "Con vales: {$resumen['con_vales']}";
                        
                        if ($resumen['ultimo_mantenimiento']) {
                            $tooltip .= "\nÚltimo: " . $resumen['ultimo_mantenimiento']->format('d/m/Y');
                        }
                        
                        return $tooltip;
                    })
                    ->toggleable()
                    ->sortable(false),
                    
                Tables\Columns\TextColumn::make('localizacion.direccion')
                    ->label('Dirección')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Sin dirección'),
                    
                Tables\Columns\TextColumn::make('localizacion.division')
                    ->label('División')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('Sin división'),
                    
                Tables\Columns\TextColumn::make('localizacion.sub_area')
                    ->label('Sub Área')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Sin sub área'),
                    
                Tables\Columns\TextColumn::make('localizacion.ubicacion')
                    ->label('Ubicación')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->placeholder('Sin ubicación'),
                    
                Tables\Columns\TextColumn::make('estado_ubicacion')
                    ->label('Estado Ubicación')
                    ->formatStateUsing(function ($record) {
                        $movimientosIndividuales = $record->movimientos->count();
                        $movimientosMultiples = $record->movimientosMultiples->count();
                        $totalMovimientos = $movimientosIndividuales + $movimientosMultiples;
                        
                        return $totalMovimientos > 0 ? 'Con movimientos (' . $totalMovimientos . ')' : 'Ubicación original';
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, 'movimientos') ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                    
                Tables\Columns\TextColumn::make('precio')
                    ->label('Precio (MXN)')
                    ->money('MXN')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('metodo_adquisicion')
                    ->label('Adquisición')
                    ->searchable()
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
                    }),
                    
                Tables\Columns\TextColumn::make('donante')
                    ->label('Donante')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Sin especificar')
                    ->visible(fn ($record) => $record && $record->metodo_adquisicion === 'Donación'),
                    
                Tables\Columns\TextColumn::make('proveedor.nombre_proveedor')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Sin proveedor'),
                    
                Tables\Columns\TextColumn::make('numero_folio')
                    ->label('Número de Folio')
                    ->placeholder('Sin folio')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => $record && in_array($record->metodo_adquisicion, ['Propiedad UASLP', 'Apoyo SS', 'Prestamo', 'IMSS-Bienestar']) && $record->tiene_folio),
                    
                Tables\Columns\TextColumn::make('estado_mobiliario')
                    ->label('Estado')
                    ->searchable()
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
                    
                // Columnas del Vale asociado
                Tables\Columns\TextColumn::make('vales.tipo_vale')
                    ->label('Tipo Vale')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $ultimoVale = $record->vales->first();
                        return $ultimoVale ? $ultimoVale->tipo_vale : null;
                    })
                    ->color(fn ($state): string => match ($state) {
                        'Entrega' => 'success',
                        'Devolución' => 'warning',
                        'Transferencia' => 'info',
                        default => 'gray',
                    })
                    ->placeholder('Sin vale')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('vales.responsable')
                    ->label('Responsable Vale')
                    ->getStateUsing(function ($record) {
                        $ultimoVale = $record->vales->first();
                        if (!$ultimoVale) return null;
                        
                        return $ultimoVale->responsable_recibe ?: $ultimoVale->responsable_entrega;
                    })
                    ->placeholder('Sin responsable')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('vales.fecha_generacion')
                    ->label('Fecha Vale')
                    ->date('d/m/Y')
                    ->getStateUsing(function ($record) {
                        $ultimoVale = $record->vales->first();
                        return $ultimoVale ? $ultimoVale->fecha_generacion : null;
                    })
                    ->placeholder('Sin vale')
                    ->sortable()
                    ->toggleable(),
                    
                // Información del Responsable Actual (del sistema anterior)
                Tables\Columns\TextColumn::make('responsable_actual')
                    ->label('Responsable Actual')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin responsable')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('matricula_responsable')
                    ->label('Matrícula Responsable')
                    ->searchable()
                    ->placeholder('Sin matrícula')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('puesto_responsable')
                    ->label('Puesto Responsable')
                    ->searchable()
                    ->placeholder('Sin puesto')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('usuarioCreador.name')
                    ->label('Creado por')
                    ->formatStateUsing(function ($record) {
                        return $record->usuarioCreador?->name ?? 'Sistema';
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Modificación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('usuarioEditor.name')
                    ->label('Modificado por')
                    ->formatStateUsing(function ($record) {
                        return $record->usuarioEditor?->name ?? 'N/A';
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
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
                        'Apoyo SS' => 'Apoyo SS',
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
                    
                SelectFilter::make('proveedor_id')
                    ->label('Proveedor')
                    ->relationship('proveedor', 'nombre_proveedor')
                    ->searchable()
                    ->preload(),
                    
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
                            $query->where(function ($q) {
                                $q->has('movimientos')->orHas('movimientosMultiples');
                            });
                        } elseif ($data['value'] === 'sin_movimientos') {
                            $query->where(function ($q) {
                                $q->doesntHave('movimientos')->doesntHave('movimientosMultiples');
                            });
                        }
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
                    
                Filter::make('fecha_creacion')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Creado desde'),
                        DatePicker::make('created_until')
                            ->label('Creado hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                    
                Filter::make('responsable_vale')
                    ->form([
                        Forms\Components\TextInput::make('responsable')
                            ->label('Responsable del Vale')
                            ->placeholder('Buscar por nombre del responsable...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['responsable'],
                            fn (Builder $query, $responsable): Builder => $query->whereHas('vales', function ($q) use ($responsable) {
                                $q->where('responsable_recibe', 'like', "%{$responsable}%")
                                  ->orWhere('responsable_entrega', 'like', "%{$responsable}%");
                            })
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
                Action::make('mantenimiento')
                    ->label('Mantenimiento')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->disabled(fn (Mobiliario $record) => $record->tieneMantenimientosActivos())
                    ->tooltip(fn (Mobiliario $record) => 
                        $record->tieneMantenimientosActivos() 
                            ? 'Este equipo ya tiene un mantenimiento activo' 
                            : null
                    )
                    ->form([
                        Forms\Components\DateTimePicker::make('fecha_programada')
                            ->label('Fecha y Hora de Mantenimiento')
                            ->required()
                            ->default(now()->addDay())
                            ->minDate(now())
                            ->displayFormat('d/m/Y H:i'),
                            
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo del Mantenimiento')
                            ->required()
                            ->rows(4)
                            ->placeholder('Describe el problema o motivo por el cual requiere mantenimiento...'),
                            
                        Forms\Components\Select::make('tipo_mantenimiento')
                            ->label('Tipo de Mantenimiento')
                            ->options([
                                'mantenimiento' => 'Mantenimiento Interno',
                                'proveedor' => 'Enviar con Proveedor Externo',
                            ])
                            ->required()
                            ->default('mantenimiento')
                            ->reactive(),
                            
                        Forms\Components\TextInput::make('proveedor_nombre')
                            ->label('Nombre del Proveedor')
                            ->placeholder('Ingresa el nombre del proveedor...')
                            ->visible(fn (Forms\Get $get) => $get('tipo_mantenimiento') === 'proveedor')
                            ->required(fn (Forms\Get $get) => $get('tipo_mantenimiento') === 'proveedor'),
                    ])
                    ->action(function (array $data, Mobiliario $record) {
                        // Obtener datos del mobiliario y su ubicación actual
                        $ubicacionActual = $record->ubicacionReal();
                        
                        // Crear solicitud de mantenimiento
                        $mantenimiento = \App\Models\Mantenimiento::create([
                            'mobiliario_id' => $record->id,
                            'fecha_programada' => $data['fecha_programada'],
                            'motivo' => $data['motivo'],
                            'tipo_mantenimiento' => $data['tipo_mantenimiento'],
                            'proveedor_nombre' => $data['proveedor_nombre'] ?? null,
                            'usuario_solicitante_id' => \Illuminate\Support\Facades\Auth::id(),
                        ]);
                        
                        // Notificar éxito
                        Notification::make()
                            ->title('Solicitud de Mantenimiento Creada')
                            ->body("Solicitud creada para {$record->numero_control}. Se enviará a Órdenes de Servicio.")
                            ->success()
                            ->send();
                            
                        // Notificar al administrador
                        \App\Services\AdminNotificationService::mantenimientoSolicitado(
                            \Illuminate\Support\Facades\Auth::user(),
                            $record,
                            $mantenimiento
                        );
                    })
                    ->modalHeading('Solicitar Mantenimiento')
                    ->modalDescription(fn (Mobiliario $record) => 
                        "Equipo: {$record->numero_control}\n" .
                        "Descripción: {$record->descripcion}\n" .
                        "Ubicación: " . ($record->ubicacionReal()['area'] ?? 'Sin ubicación') . "\n" .
                        "Responsable: " . ($record->ubicacionReal()['responsable'] ?? 'Sin responsable')
                    )
                    ->modalSubmitActionLabel('Crear Solicitud')
                    ->visible(fn (Mobiliario $record) => !$record->dado_de_baja),
                    
                Action::make('generateQR')
                    ->label('Generar QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->action(function (Mobiliario $record) {
                        $contenido = $record->generarQR();
                        
                        $result = QrBuilder::create()
                            ->writer(new PngWriter())
                            ->data($contenido)
                            ->encoding(new Encoding('UTF-8'))
                            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
                            ->size(300)
                            ->margin(10)
                            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                            ->build();

                        $filename = 'qr_' . $record->numero_control . '.png';
                        
                        return Response::streamDownload(function () use ($result) {
                            echo $result->getString();
                        }, $filename, [
                            'Content-Type' => 'image/png',
                        ]);
                    }),
                    
                Action::make('verHistorial')
                    ->label('Ver Historial')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->modalHeading('Historial de Movimientos')
                    ->modalContent(function (Mobiliario $record) {
                        // Obtener movimientos individuales
                        $movimientosIndividuales = $record->movimientos()
                            ->with(['areaActual', 'areaAnterior', 'usuario'])
                            ->get();
                            
                        // Obtener movimientos múltiples
                        $movimientosMultiples = $record->movimientosMultiples()
                            ->with(['areaActual', 'usuario'])
                            ->get();
                        
                        // Combinar ambos tipos de movimientos
                        $todosLosMovimientos = $movimientosIndividuales
                            ->concat($movimientosMultiples)
                            ->sortByDesc('fecha_movimiento')
                            ->values();
                            
                        if ($todosLosMovimientos->isEmpty()) {
                            return view('historial-vacio');
                        }
                        
                        return view('historial-movimientos', [
                            'movimientos' => $todosLosMovimientos, 
                            'record' => $record
                        ]);
                    })
                    ->modalWidth('5xl')
                    ->slideOver(),
                    
                Action::make('verHistorialMantenimientos')
                    ->label('Historial Mantenimientos')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->modalHeading(fn (Mobiliario $record) => 'Historial de Mantenimientos - ' . $record->numero_control)
                    ->modalContent(function (Mobiliario $record) {
                        // Obtener todos los mantenimientos con relaciones
                        $mantenimientos = $record->mantenimientos()
                            ->with(['usuarioSolicitante', 'usuarioMantenimiento'])
                            ->orderBy('created_at', 'desc')
                            ->get();
                            
                        return view('historial-mantenimientos', [
                            'mantenimientos' => $mantenimientos,
                            'record' => $record
                        ]);
                    })
                    ->modalWidth('6xl')
                    ->slideOver(),
                    
                Action::make('dar_de_baja')
                    ->label('Dar de Baja')
                    ->icon('heroicon-o-archive-box')
                    ->color('danger')
                    ->disabled(fn (Mobiliario $record) => $record->tieneMantenimientosActivos())
                    ->tooltip(fn (Mobiliario $record) => 
                        $record->tieneMantenimientosActivos() 
                            ? 'No se puede dar de baja un equipo en mantenimiento' 
                            : null
                    )
                    ->form([
                        Forms\Components\DateTimePicker::make('fecha_baja')
                            ->label('Fecha de Baja')
                            ->required()
                            ->default(now()),
                            
                        Forms\Components\Textarea::make('motivo_baja')
                            ->label('Motivo de Baja')
                            ->required()
                            ->rows(3)
                            ->placeholder('Especifica el motivo por el cual se da de baja este equipo...'),
                    ])
                    ->action(function (array $data, Mobiliario $record) {
                        $record->update([
                            'dado_de_baja' => true,
                            'fecha_baja' => $data['fecha_baja'],
                            'motivo_baja' => $data['motivo_baja'],
                        ]);
                        
                        Notification::make()
                            ->title('Equipo dado de baja')
                            ->body('El equipo ha sido dado de baja y removido del inventario general.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Dar de Baja Equipo')
                    ->modalDescription('¿Estás seguro de que quieres dar de baja este equipo? Se removerá del inventario general.')
                    ->modalSubmitActionLabel('Sí, dar de baja'),
                    
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                    
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->disabled(fn (Mobiliario $record) => $record->tieneMantenimientosActivos())
                    ->tooltip(fn (Mobiliario $record) => 
                        $record->tieneMantenimientosActivos() 
                            ? 'No se puede editar un equipo en mantenimiento activo' 
                            : null
                    ),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportarTodosLosFiltrados')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        // Notificar al administrador
                        \App\Services\AdminNotificationService::reporteGenerado(
                            \Illuminate\Support\Facades\Auth::user(), 
                            'Excel - Inventario Completo'
                        );
                        return static::exportarTodosLosDatosFiltrados();
                    })
                    ->tooltip('Exportar todos los datos mostrados en la tabla actual'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('generateQRBulk')
                        ->label('Generar QR Masivo')
                        ->icon('heroicon-o-qr-code')
                        ->color('info')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            // Aquí implementarías la generación masiva de QR codes
                            Notification::make()
                                ->title('QR Codes generados')
                                ->body('Se han generado ' . $records->count() . ' códigos QR')
                                ->success()
                                ->send();
                        }),
                        
                    Tables\Actions\BulkAction::make('exportarExcelFiltrado')
                        ->label('Exportar Seleccionados')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            // Notificar al administrador
                            \App\Services\AdminNotificationService::reporteGenerado(
                                \Illuminate\Support\Facades\Auth::user(), 
                                'Excel - Selección de ' . $records->count() . ' registros'
                            );
                            return static::exportarExcelFiltrado($records);
                        })
                        ->tooltip('Exportar solo los registros seleccionados')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->searchOnBlur()
            ->recordClasses(fn (Mobiliario $record) => 
                $record->tieneMantenimientosActivos() 
                    ? 'opacity-50 bg-yellow-50 dark:bg-yellow-900/10' 
                    : null
            )
            ->recordAction(fn (Mobiliario $record) => 
                $record->tieneMantenimientosActivos() 
                    ? null 
                    : Tables\Actions\ViewAction::class
            );
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->activos()
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
    
    /**
     * Exportar registros específicos seleccionados a Excel
     */
    protected static function exportarExcelFiltrado(\Illuminate\Database\Eloquent\Collection $records)
    {
        try {
            $filtrosInfo = static::obtenerInfoFiltros();
            $fileName = static::generarNombreArchivo($filtrosInfo);
            
            $spreadsheet = static::crearExcelConDatos($records, $filtrosInfo);
            $filePath = storage_path('app/exports/' . $fileName);
            
            // Crear directorio si no existe
            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
            
            Notification::make()
                ->title('Excel Exportado')
                ->body("Se han exportado {$records->count()} registros seleccionados.")
                ->success()
                ->send();
                
            return response()->download($filePath)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error en Exportación')
                ->body('Error al exportar a Excel: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Exportar todos los datos filtrados a Excel
     */
    protected static function exportarTodosLosDatosFiltrados()
    {
        try {
            // Obtener los datos usando el mismo query que la tabla
            $query = static::getEloquentQuery();
            $records = $query->get();
            
            $filtrosInfo = static::obtenerInfoFiltros();
            $fileName = static::generarNombreArchivo($filtrosInfo, true);
            
            $spreadsheet = static::crearExcelConDatos($records, $filtrosInfo);
            $filePath = storage_path('app/exports/' . $fileName);
            
            // Crear directorio si no existe
            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
            
            Notification::make()
                ->title('Excel Exportado')
                ->body("Se han exportado {$records->count()} registros filtrados.")
                ->success()
                ->send();
                
            return response()->download($filePath)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error en Exportación')
                ->body('Error al exportar a Excel: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Crear archivo Excel con los datos
     */
    protected static function crearExcelConDatos($records, $filtrosInfo)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventario Filtrado');
        
        // Información de filtros en la parte superior
        $row = 1;
        $sheet->setCellValue('A' . $row, 'REPORTE DE INVENTARIO HOSPITALARIO - DATOS FILTRADOS');
        $sheet->mergeCells('A' . $row . ':P' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Fecha de Generación: ' . Carbon::now()->format('d/m/Y H:i:s'));
        $sheet->setCellValue('A' . ($row + 1), 'Total de Registros: ' . $records->count());
        
        $row += 2;
        if (!empty($filtrosInfo)) {
            $sheet->setCellValue('A' . $row, 'FILTROS APLICADOS:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            
            foreach ($filtrosInfo as $filtro) {
                $sheet->setCellValue('A' . $row, '• ' . $filtro);
                $row++;
            }
        }
        
        $row += 2;
        
        // Encabezados de columnas
        $headers = [
            'A' => 'N° Control',
            'B' => 'Descripción',
            'C' => 'Marca',
            'D' => 'Modelo',
            'E' => 'N° Serie',
            'F' => 'Tipo',
            'G' => 'Categoría',
            'H' => 'Precio (MXN)',
            'I' => 'Adquisición',
            'J' => 'Estado',
            'K' => 'Dirección',
            'L' => 'División',
            'M' => 'Ubicación',
            'N' => 'Proveedor',
            'O' => 'Creado Por',
            'P' => 'Fecha Creación'
        ];
        
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
        }
        
        // Estilo para encabezados
        $headerRange = 'A' . $row . ':P' . $row;
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4472C4');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row++;
        
        // Datos
        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $record->numero_control);
            $sheet->setCellValue('B' . $row, $record->descripcion);
            $sheet->setCellValue('C' . $row, $record->marca);
            $sheet->setCellValue('D' . $row, $record->modelo);
            $sheet->setCellValue('E' . $row, $record->numero_serie ?: 'Sin número de serie');
            $sheet->setCellValue('F' . $row, $record->tipoMobiliario?->tipo ?? 'N/A');
            $sheet->setCellValue('G' . $row, $record->tipoMobiliario?->categoria ?? 'N/A');
            $sheet->setCellValue('H' . $row, '$' . number_format($record->precio, 2));
            $sheet->setCellValue('I' . $row, $record->metodo_adquisicion);
            $sheet->setCellValue('J' . $row, $record->estado_mobiliario);
            $sheet->setCellValue('K' . $row, $record->localizacion?->direccion ?? 'N/A');
            $sheet->setCellValue('L' . $row, $record->localizacion?->division ?? 'N/A');
            $sheet->setCellValue('M' . $row, $record->localizacion?->ubicacion ?? 'N/A');
            $sheet->setCellValue('N' . $row, $record->proveedor?->nombre_proveedor ?? 'N/A');
            $sheet->setCellValue('O' . $row, $record->usuarioCreador?->name ?? 'Sistema');
            $sheet->setCellValue('P' . $row, $record->created_at?->format('d/m/Y H:i') ?? 'N/A');
            
            $row++;
        }
        
        // Aplicar bordes a los datos
        $dataRange = 'A' . (count($filtrosInfo) + 7) . ':P' . ($row - 1);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Ajustar ancho de columnas
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        return $spreadsheet;
    }
    
    /**
     * Obtener información de filtros aplicados
     */
    protected static function obtenerInfoFiltros()
    {
        $filtros = [];
        
        // Filtro básico siempre aplicado
        $filtros[] = 'Solo registros activos (no dados de baja)';
        
        // Información adicional sobre el contexto
        $filtros[] = 'Exportado el: ' . Carbon::now()->format('d/m/Y H:i:s');
        
        return $filtros;
    }
    
    /**
     * Generar nombre de archivo con timestamp
     */
    protected static function generarNombreArchivo($filtrosInfo, $todosFiltrados = false)
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $tipo = $todosFiltrados ? 'todos_filtrados' : 'seleccionados';
        
        return "inventario_filtrado_{$tipo}_{$timestamp}.xlsx";
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMobiliarios::route('/'),
            'create' => Pages\CreateMobiliario::route('/create'),
            'view' => Pages\ViewMobiliario::route('/{record}'),
            'edit' => Pages\EditMobiliario::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::activos()->count();
    }
}
