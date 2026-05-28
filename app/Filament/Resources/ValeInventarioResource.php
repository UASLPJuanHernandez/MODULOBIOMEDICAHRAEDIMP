<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ValeInventarioResource\Pages;
use App\Models\PersonalReportante;
use App\Models\ValeInventario;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ValeInventarioResource extends Resource
{
    protected static ?string $model = ValeInventario::class;

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Vales';
    protected static ?string $modelLabel      = 'Vale';
    protected static ?string $pluralModelLabel = 'Vales';
    protected static ?int    $navigationSort  = 3;
    protected static bool    $shouldRegisterNavigation = false;

    private static array $AREAS = [
        'Audiología','Anestesiología','Banco de Sangre','Banco de Leches',
        'Cardiología','CEYE','Cirugía Ambulatoria','Cirugías','Clínica de catéter',
        'Clínica displacías','Consultorio pediatría','Consultorio ginecología',
        'Crecimiento y desarrollo','Cuidados intermedios','Dermatología','Dietología',
        'Endoscopia','Farmacia','Ginecología y obstetricia','Hemodiálisis','Hemodinamia',
        'Imagenología','Inhaloterapia','Laboratorio','Lactantes','Maxilofacial',
        'Neonatología','Medicina interna','Neurología','Oncología adultos',
        'Oncología pediátrica','Oftalmología','Ortopedia','Otorrinolaringología',
        'Patología','Pediatría','Quemados','Quirófano','Radioterapia','Rehabilitación',
        'Reumatología','Somatometría','Tococirugía','Trasplantes',
        'UCIA','UCIN','UCIN aislados','UCIP','Urgencias',
    ];

    public static function form(Form $form): Form
    {
        $areas = array_combine(self::$AREAS, self::$AREAS);

        return $form->schema([

            Section::make('Tipo de vale')
                ->columns(2)
                ->schema([
                    Select::make('tipo')
                        ->label('Tipo de movimiento')
                        ->options([
                            'entrega' => 'Entrega (alta / préstamo)',
                            'retiro'  => 'Retiro (baja / devolución)',
                        ])
                        ->required(),

                    Select::make('area')
                        ->label('Área / Servicio')
                        ->options($areas)
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if (! $state) return;
                            $jefe = PersonalReportante::where('es_jefe_servicio', true)
                                ->where('area_jefe_servicio', $state)
                                ->value('nombre');
                            if ($jefe) {
                                $set('quien_recibe', $jefe);
                            }
                        }),
                ]),

            Section::make('Datos del equipo')
                ->columns(2)
                ->schema([
                    TextInput::make('equipo_nombre')
                        ->label('Descripción del equipo')
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('numero_inventario')
                        ->label('No. Inventario'),

                    TextInput::make('numero_serie')
                        ->label('No. Serie'),

                    TextInput::make('marca')
                        ->label('Marca'),

                    TextInput::make('modelo')
                        ->label('Modelo'),

                    TextInput::make('unidad_medica')
                        ->label('Unidad médica')
                        ->columnSpanFull(),
                ]),

            Section::make('Receptor')
                ->columns(2)
                ->schema([
                    TextInput::make('quien_recibe')
                        ->label('Nombre de quien recibe'),

                    TextInput::make('cargo_recibe')
                        ->label('Cargo / puesto'),
                ]),

            Section::make('Observaciones')
                ->schema([
                    Textarea::make('observaciones')
                        ->label('Observaciones')
                        ->rows(3),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->width('140px'),

                BadgeColumn::make('tipo')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'entrega',
                        'danger'  => 'retiro',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'entrega' => 'Entrega',
                        'retiro'  => 'Retiro',
                        default   => ucfirst($state),
                    })
                    ->width('100px'),

                TextColumn::make('numero_inventario')
                    ->label('No. Inventario')
                    ->searchable()
                    ->weight('bold')
                    ->width('130px'),

                TextColumn::make('equipo_nombre')
                    ->label('Equipo')
                    ->searchable()
                    ->limit(45)
                    ->wrap(),

                TextColumn::make('area')
                    ->label('Área')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('marca')
                    ->label('Marca')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('modelo')
                    ->label('Modelo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('usuario_nombre')
                    ->label('Generado por')
                    ->sortable()
                    ->width('150px'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo de vale')
                    ->options([
                        'entrega' => 'Vale de Entrega',
                        'retiro'  => 'Vale de Retiro',
                    ]),
                Filter::make('hoy')
                    ->label('Solo hoy')
                    ->query(fn (Builder $q) => $q->whereDate('created_at', today()))
                    ->toggle(),
                Filter::make('esta_semana')
                    ->label('Esta semana')
                    ->query(fn (Builder $q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->toggle(),
            ])
            ->actions([
                Action::make('redescargar')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn (ValeInventario $record) => route('inventario.vale.redescargar', $record))
                    ->openUrlInNewTab(),

                Action::make('ver_equipo')
                    ->label('Ver equipo')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (ValeInventario $record) => $record->inventarioEquipo
                        ? route('filament.admin.resources.inventario-equipos.view', $record->inventarioEquipo)
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn (ValeInventario $record) => $record->inventario_equipo_id !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasRole('Administrador') ?? false),
                ]),
            ])
            ->striped()
            ->paginated([25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'     => Pages\ListValeInventarios::route('/'),
            'concretar' => Pages\ConcretarVale::route('/{record}/concretar'),
            'firmar'    => Pages\FirmarVale::route('/{record}/firmar'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $hoy = static::getModel()::whereDate('created_at', today())->count();
        return $hoy > 0 ? (string) $hoy : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
