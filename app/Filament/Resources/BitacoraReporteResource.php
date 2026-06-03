<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BitacoraReporteResource\Pages;
use App\Models\BitacoraReporte;
use App\Models\PersonalReportante;
use App\Services\BitacoraDocxService;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BitacoraReporteResource extends Resource
{
    protected static ?string $model = BitacoraReporte::class;

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Bitácoras';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Se accede desde Documentos generados
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Datos del solicitante')
                ->columns(3)
                ->schema([
                    TextInput::make('nombre_personal')
                        ->label('Nombre del personal')
                        ->required(),

                    TextInput::make('numero_identificacion')
                        ->label('N° de identificación'),

                    Select::make('area_departamento')
                        ->label('Área / Departamento')
                        ->options([
                            'Audiología'               => 'Audiología',
                            'Anestesiología'           => 'Anestesiología',
                            'Banco de Sangre'          => 'Banco de Sangre',
                            'Banco de Leches'          => 'Banco de Leches',
                            'Cardiología'              => 'Cardiología',
                            'CEYE'                     => 'CEYE',
                            'Cirugía Ambulatoria'      => 'Cirugía Ambulatoria',
                            'Cirugías'                 => 'Cirugías',
                            'Clínica de catéter'       => 'Clínica de catéter',
                            'Clínica displacías'       => 'Clínica displacías',
                            'Consultorio pediatría'    => 'Consultorio pediatría',
                            'Consultorio ginecología'  => 'Consultorio ginecología',
                            'Crecimiento y desarrollo' => 'Crecimiento y desarrollo',
                            'Cuidados intermedios'     => 'Cuidados intermedios',
                            'Dermatología'             => 'Dermatología',
                            'Dietología'               => 'Dietología',
                            'Endoscopia'               => 'Endoscopia',
                            'Farmacia'                 => 'Farmacia',
                            'Ginecología y obstetricia'=> 'Ginecología y obstetricia',
                            'Hemodiálisis'             => 'Hemodiálisis',
                            'Hemodinamia'              => 'Hemodinamia',
                            'Imagenología'             => 'Imagenología',
                            'Inhaloterapia'            => 'Inhaloterapia',
                            'Laboratorio'              => 'Laboratorio',
                            'Lactantes'                => 'Lactantes',
                            'Maxilofacial'             => 'Maxilofacial',
                            'Neonatología'             => 'Neonatología',
                            'Medicina interna'         => 'Medicina interna',
                            'Neurología'               => 'Neurología',
                            'Oncología adultos'        => 'Oncología adultos',
                            'Oncología pediátrica'     => 'Oncología pediátrica',
                            'Oftalmología'             => 'Oftalmología',
                            'Ortopedia'                => 'Ortopedia',
                            'Otorrinolaringología'     => 'Otorrinolaringología',
                            'Patología'                => 'Patología',
                            'Pediatría'                => 'Pediatría',
                            'Quemados'                 => 'Quemados',
                            'Quirófano'                => 'Quirófano',
                            'Radioterapia'             => 'Radioterapia',
                            'Rehabilitación'           => 'Rehabilitación',
                            'Reumatología'             => 'Reumatología',
                            'Somatometría'             => 'Somatometría',
                            'Tococirugía'              => 'Tococirugía',
                            'Trasplantes'              => 'Trasplantes',
                            'UCIA'                     => 'UCIA',
                            'UCIN'                     => 'UCIN',
                            'UCIN aislados'            => 'UCIN aislados',
                            'UCIP'                     => 'UCIP',
                            'Urgencias'                => 'Urgencias',
                        ])
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if (! $state) return;
                            $jefe = PersonalReportante::where('es_jefe_servicio', true)
                                ->where('area_jefe_servicio', $state)
                                ->value('nombre');
                            if ($jefe) {
                                $set('recibe_nombre', $jefe);
                            }
                        }),
                ]),

            Section::make('Fecha y hora')
                ->columns(2)
                ->schema([
                    DatePicker::make('fecha_reporte')
                        ->label('Fecha del reporte')
                        ->required(),

                    TimePicker::make('hora_reporte')
                        ->label('Hora del reporte')
                        ->seconds(false)
                        ->required(),
                ]),

            Section::make('Mensaje recibido')
                ->schema([
                    Placeholder::make('mensaje_preview')
                        ->label('Mensaje original del usuario')
                        ->content(fn ($record) => $record?->reporte?->descripcion_original
                            ?? $record?->mensaje_original
                            ?? '—'),
                ]),

            Section::make('Justificación / Observaciones')
                ->schema([
                    Actions::make([
                        FormAction::make('pegar_mensaje')
                            ->label('Pegar mensaje recibido')
                            ->icon('heroicon-o-clipboard-document')
                            ->color('gray')
                            ->action(fn ($record, Set $set) => $set(
                                'justificacion',
                                $record?->mensaje_original ?? ''
                            )),
                    ]),
                    Textarea::make('justificacion')
                        ->label('')
                        ->rows(5)
                        ->columnSpanFull(),
                ]),

            Section::make('SE SOLICITA')
                ->description('Selecciona el tipo de servicio o la razón de baja. Solo se marca una opción.')
                ->columns(2)
                ->schema([
                    Radio::make('tipo_servicio')
                        ->label('Servicio')
                        ->options([
                            'preventivo' => 'Preventivo',
                            'correctivo' => 'Correctivo',
                        ])
                        ->live()
                        ->afterStateUpdated(fn ($state, \Filament\Forms\Set $set) => $state ? $set('tipo_baja', null) : null),

                    Radio::make('tipo_baja')
                        ->label('Baja por')
                        ->options([
                            'no_funcional' => 'Por no ser funcional para el área',
                            'inservible'   => 'Inservible',
                            'obsoleto'     => 'Obsoleto',
                            'disposicion'  => 'A disposición',
                            'traspaso'     => 'Traspaso',
                            'otro'         => 'Otro',
                        ])
                        ->live()
                        ->afterStateUpdated(fn ($state, \Filament\Forms\Set $set) => $state ? $set('tipo_servicio', null) : null),
                ]),

            Section::make('Resultado')
                ->columns(1)
                ->schema([
                    Select::make('resultado')
                        ->label('La solicitud fue resuelta de forma...')
                        ->options([
                            'satisfactoria'    => 'Satisfactoria',
                            'parcial'          => 'Parcial',
                            'no_satisfactoria' => 'No satisfactoria',
                        ])
                        ->required(),
                ]),

            Section::make('Datos del equipo')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre_dispositivo')
                        ->label('Descripción del bien / equipo')
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('marca')
                        ->label('Marca'),

                    TextInput::make('modelo')
                        ->label('Modelo'),

                    TextInput::make('numero_serie')
                        ->label('Número de serie')
                        ->required()
                        ->hint('Si es un consumible, escribir "Consumible"'),

                    TextInput::make('numero_control')
                        ->label('N° de control'),
                ]),

            Section::make('Firmas')
                ->columns(2)
                ->schema([
                    TextInput::make('atiende_nombre')
                        ->label('Atendió (nombre)'),

                    TextInput::make('recibe_nombre')
                        ->label('Recibió (nombre)'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('nombre_personal')
                    ->label('Personal')
                    ->searchable(),

                TextColumn::make('area_departamento')
                    ->label('Área')
                    ->searchable(),

                TextColumn::make('fecha_reporte')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('nombre_dispositivo')
                    ->label('Equipo')
                    ->placeholder('—'),

                TextColumn::make('resultado')
                    ->label('Resultado')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'satisfactoria'    => 'success',
                        'parcial'          => 'warning',
                        'no_satisfactoria' => 'danger',
                        default            => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match($state) {
                        'satisfactoria'    => 'Satisfactoria',
                        'parcial'          => 'Parcial',
                        'no_satisfactoria' => 'No satisfactoria',
                        default            => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Generada')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('descargar')
                    ->label('Descargar DOCX')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (BitacoraReporte $record) {
                        $service = new BitacoraDocxService();
                        $path    = $service->generar($record);
                        return response()->download($path, 'Bitacora_' . $record->id . '.docx')
                            ->deleteFileAfterSend(true);
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index'  => Pages\ListBitacorasReporte::route('/'),
            'create' => Pages\CreateBitacoraReporte::route('/create'),
            'edit'   => Pages\EditBitacoraReporte::route('/{record}/edit'),
            'firmar' => Pages\FirmarBitacora::route('/{record}/firmar'),
        ];
    }
}
