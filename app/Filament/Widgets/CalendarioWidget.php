<?php

namespace App\Filament\Widgets;

use App\Models\EventoCalendario;
use App\Models\Ingeniero;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarioWidget extends FullCalendarWidget
{
    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.calendario-widget';

    public \Illuminate\Database\Eloquent\Model|string|null $model = EventoCalendario::class;

    // Filtros
    public string $search       = '';
    public string $filtroTipo   = '';
    public string $filtroEstado = '';
    public string $filtroPrioridad  = '';
    public string $filtroResponsable = '';

    public function updatedSearch(): void            { $this->dispatch('filament-fullcalendar--refresh'); }
    public function updatedFiltroTipo(): void        { $this->dispatch('filament-fullcalendar--refresh'); }
    public function updatedFiltroEstado(): void      { $this->dispatch('filament-fullcalendar--refresh'); }
    public function updatedFiltroPrioridad(): void   { $this->dispatch('filament-fullcalendar--refresh'); }
    public function updatedFiltroResponsable(): void { $this->dispatch('filament-fullcalendar--refresh'); }

    public function fetchEvents(array $fetchInfo): array
    {
        return EventoCalendario::query()
            ->where('fecha_inicio', '>=', $fetchInfo['start'])
            ->where('fecha_inicio', '<=', $fetchInfo['end'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('titulo', 'like', "%{$this->search}%")
                  ->orWhere('descripcion', 'like', "%{$this->search}%")
                  ->orWhere('ubicacion', 'like', "%{$this->search}%");
            }))
            ->when($this->filtroTipo,        fn ($q) => $q->where('tipo', $this->filtroTipo))
            ->when($this->filtroEstado,      fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroPrioridad,   fn ($q) => $q->where('prioridad', $this->filtroPrioridad))
            ->when($this->filtroResponsable, fn ($q) => $q->where('responsable', $this->filtroResponsable))
            ->get()
            ->map(fn (EventoCalendario $evento) => EventData::make()
                ->id($evento->id)
                ->title($evento->titulo)
                ->start($evento->fecha_inicio)
                ->end($evento->fecha_fin)
                ->allDay($evento->todo_el_dia)
                ->backgroundColor($evento->color)
                ->borderColor($evento->color)
            )
            ->toArray();
    }

    public function getFormSchema(): array
    {
        return [
            Section::make('Información general')
                ->columns(2)
                ->schema([
                    TextInput::make('titulo')
                        ->label('Título')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('descripcion')
                        ->label('Descripción')
                        ->rows(3)
                        ->columnSpanFull(),

                    DateTimePicker::make('fecha_inicio')
                        ->label('Inicio')
                        ->required()
                        ->seconds(false)
                        ->hidden(fn (Get $get) => $get('todo_el_dia')),

                    DatePicker::make('fecha_inicio_dia')
                        ->label('Inicio')
                        ->required()
                        ->visible(fn (Get $get) => $get('todo_el_dia')),

                    DateTimePicker::make('fecha_fin')
                        ->label('Fin')
                        ->seconds(false)
                        ->hidden(fn (Get $get) => $get('todo_el_dia')),

                    DatePicker::make('fecha_fin_dia')
                        ->label('Fin')
                        ->visible(fn (Get $get) => $get('todo_el_dia')),

                    Toggle::make('todo_el_dia')
                        ->label('Todo el día')
                        ->live()
                        ->columnSpanFull(),
                ]),

            Section::make('Detalles')
                ->columns(2)
                ->schema([
                    Select::make('tipo')
                        ->label('Tipo de evento')
                        ->options([
                            'reunion'       => 'Reunión',
                            'mantenimiento' => 'Mantenimiento',
                            'inspeccion'    => 'Inspección',
                            'capacitacion'  => 'Capacitación',
                            'entrega'       => 'Entrega de equipo',
                            'otro'          => 'Otro',
                        ])
                        ->required()
                        ->default('otro'),

                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'confirmado' => 'Confirmado',
                            'tentativo'  => 'Tentativo',
                            'cancelado'  => 'Cancelado',
                        ])
                        ->required()
                        ->default('confirmado'),

                    Select::make('prioridad')
                        ->label('Prioridad')
                        ->options([
                            'baja'    => 'Baja',
                            'media'   => 'Media',
                            'alta'    => 'Alta',
                            'urgente' => 'Urgente',
                        ])
                        ->required()
                        ->default('media'),

                    Select::make('responsable')
                        ->label('Responsable')
                        ->options(fn () => Ingeniero::orderBy('nombre')->pluck('nombre', 'nombre'))
                        ->searchable(),

                    TextInput::make('ubicacion')
                        ->label('Ubicación / Área')
                        ->maxLength(255),

                    ColorPicker::make('color')
                        ->label('Color en calendario')
                        ->default('#3b82f6'),
                ]),

            Section::make('Notas y recurrencia')
                ->collapsed()
                ->schema([
                    Textarea::make('notas')
                        ->label('Notas adicionales')
                        ->rows(3)
                        ->columnSpanFull(),

                    Select::make('recurrencia')
                        ->label('Recurrencia')
                        ->options([
                            ''          => 'Sin recurrencia',
                            'diario'    => 'Diario',
                            'semanal'   => 'Semanal',
                            'quincenal' => 'Quincenal',
                            'mensual'   => 'Mensual',
                            'anual'     => 'Anual',
                        ])
                        ->default(''),
                ]),
        ];
    }

    private function normalizeEventData(array $data): array
    {
        if ($data['todo_el_dia'] ?? false) {
            $data['fecha_inicio'] = $data['fecha_inicio_dia'] ?? $data['fecha_inicio'];
            $data['fecha_fin']    = $data['fecha_fin_dia']    ?? $data['fecha_fin'];
        }
        unset($data['fecha_inicio_dia'], $data['fecha_fin_dia']);
        return $data;
    }

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo evento')
                ->mountUsing(function (Form $form, array $arguments) {
                    $start = $arguments['start'] ?? now();
                    $end   = $arguments['end']   ?? now()->addHour();
                    $form->fill([
                        'fecha_inicio'     => $start,
                        'fecha_inicio_dia' => \Carbon\Carbon::parse($start)->toDateString(),
                        'fecha_fin'        => $end,
                        'fecha_fin_dia'    => \Carbon\Carbon::parse($end)->toDateString(),
                        'color'            => '#3b82f6',
                    ]);
                })
                ->using(fn (array $data) => EventoCalendario::create($this->normalizeEventData($data))),
        ];
    }

    protected function modalActions(): array
    {
        return [
            EditAction::make()
                ->mutateRecordDataUsing(function (array $data): array {
                    $data['fecha_inicio_dia'] = $data['fecha_inicio']
                        ? \Carbon\Carbon::parse($data['fecha_inicio'])->toDateString()
                        : null;
                    $data['fecha_fin_dia'] = $data['fecha_fin']
                        ? \Carbon\Carbon::parse($data['fecha_fin'])->toDateString()
                        : null;
                    return $data;
                })
                ->using(fn (EventoCalendario $record, array $data) => $record->update($this->normalizeEventData($data))),

            DeleteAction::make()
                ->using(fn (EventoCalendario $record) => $record->delete()),
        ];
    }

    public function config(): array
    {
        return [
            'firstDay'      => 1,
            'headerToolbar' => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            ],
            'buttonText' => [
                'today' => 'Hoy',
                'month' => 'Mes',
                'week'  => 'Semana',
                'day'   => 'Día',
                'list'  => 'Lista',
            ],
            'locale'       => 'es',
            'height'       => 'auto',
            'editable'     => true,
            'selectable'   => true,
            'dayMaxEvents' => true,
            'nowIndicator' => true,
            'navLinks'     => true,
        ];
    }
}
