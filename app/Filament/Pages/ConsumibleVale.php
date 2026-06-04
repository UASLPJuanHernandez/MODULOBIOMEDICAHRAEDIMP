<?php

namespace App\Filament\Pages;

use App\Models\Consumible;
use App\Models\PersonalReportante;
use App\Models\ValeInventario;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Actions\Action as NotifAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ConsumibleVale extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon          = null;
    protected static bool    $shouldRegisterNavigation = false;
    protected static string  $view = 'filament.pages.consumible-vale';
    protected static ?string $title = 'Vale de Entrega — Material / Consumible';

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

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['cantidad' => 1]);
    }

    public function form(Form $form): Form
    {
        $areas = array_combine(self::$AREAS, self::$AREAS);

        return $form
            ->schema([
                Section::make('Consumible a entregar')
                    ->schema([
                        Select::make('consumible_id')
                            ->label('Consumible')
                            ->options(Consumible::orderBy('nombre')->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set) {
                                if (! $state) return;
                                $c = Consumible::find($state);
                                if (! $c) return;
                                $set('nombre',      $c->nombre);
                                $set('descripcion', $c->descripcion ?? '');
                                $set('marca',       $c->marca       ?? '');
                                $set('referencia',  $c->referencia  ?? '');
                                $set('stock_actual', $c->cantidad);
                            }),

                        Placeholder::make('stock_actual')
                            ->label('Stock actual')
                            ->content(fn ($state) => $state !== null
                                ? ($state > 0
                                    ? "✓ {$state} unidades disponibles"
                                    : '⚠ Sin stock')
                                : '—')
                            ->visible(fn ($get) => $get('consumible_id') !== null),
                    ]),

                Section::make('Datos del material')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(2)
                            ->columnSpanFull(),

                        TextInput::make('marca')
                            ->label('Marca')
                            ->maxLength(100),

                        TextInput::make('referencia')
                            ->label('Referencia')
                            ->maxLength(100),

                        TextInput::make('cantidad')
                            ->label('Cantidad a entregar')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ]),

                Section::make('Área / Servicio destino')
                    ->schema([
                        Select::make('area')
                            ->label('Área o Servicio')
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
                                    $set('nombre_recibe', $jefe);
                                }
                            }),
                    ]),

                Section::make('Quien entrega')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre_entrega')
                            ->label('Nombre')
                            ->maxLength(150),
                        TextInput::make('cargo_entrega')
                            ->label('Cargo')
                            ->maxLength(100),
                    ]),

                Section::make('Quien recibe')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre_recibe')
                            ->label('Nombre')
                            ->maxLength(150),
                        TextInput::make('cargo_recibe')
                            ->label('Cargo')
                            ->maxLength(100),
                    ]),

                Section::make('Observaciones')
                    ->schema([
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(3),
                    ]),
            ])
            ->statePath('data');
    }

    public function generar(): void
    {
        $this->form->validate();

        $d          = $this->data;
        $consumible = Consumible::findOrFail($d['consumible_id']);
        $cantidad   = (int) $d['cantidad'];

        if ($cantidad > $consumible->cantidad) {
            Notification::make()
                ->title('Stock insuficiente')
                ->body("Solo hay {$consumible->cantidad} unidades disponibles.")
                ->danger()
                ->send();
            return;
        }

        $consumible->decrement('cantidad', $cantidad);

        $vale = ValeInventario::create([
            'tipo'                 => 'entrega',
            'consumible_id'        => $consumible->id,
            'cantidad_entregada'   => $cantidad,
            'inventario_equipo_id' => null,
            'equipo_nombre'        => $consumible->nombre,
            'marca'                => $consumible->marca      ?? '',
            'modelo'               => $consumible->referencia ?? '',
            'numero_inventario'    => $consumible->referencia ?? '',
            'numero_serie'         => '',
            'area'                 => $d['area'] ?? '',
            'unidad_medica'        => '',
            'quien_recibe'         => $d['nombre_recibe'] ?? '',
            'cargo_recibe'         => $d['cargo_recibe']  ?? '',
            'observaciones'        => $d['observaciones'] ?? '',
            'usuario_id'           => Auth::id(),
            'usuario_nombre'       => Auth::user()?->name ?? 'Sistema',
            'nombre_entrega'       => $d['nombre_entrega'] ?? '',
            'cargo_entrega'        => $d['cargo_entrega']  ?? '',
        ]);

        Notification::make()
            ->title('Vale generado correctamente')
            ->body("Se descontaron {$cantidad} unidades de «{$consumible->nombre}».")
            ->success()
            ->persistent()
            ->actions([
                NotifAction::make('descargar')
                    ->label('Descargar PDF')
                    ->url(route('admin.consumible.vale.pdf', $vale))
                    ->openUrlInNewTab()
                    ->button(),
            ])
            ->send();

        $this->form->fill(['cantidad' => 1]);
    }
}
