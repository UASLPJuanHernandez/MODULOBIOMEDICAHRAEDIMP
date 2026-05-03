<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BitacoraReporteResource\Pages;
use App\Models\BitacoraReporte;
use App\Services\BitacoraDocxService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
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
                        ->required(),
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

            Section::make('Descripción del reporte')
                ->schema([
                    Textarea::make('mensaje_original')
                        ->label('Mensaje original del usuario')
                        ->rows(4)
                        ->required(),
                ]),

            Section::make('Acciones realizadas')
                ->description('Agrega las acciones que se realizaron. Puedes incluir imagen y pie de imagen en cada una.')
                ->schema([
                    Repeater::make('acciones')
                        ->label('')
                        ->schema([
                            Textarea::make('texto')
                                ->label('Acción realizada')
                                ->rows(2)
                                ->required(),

                            FileUpload::make('imagen_path')
                                ->label('Imagen (opcional)')
                                ->image()
                                ->directory('bitacoras/imagenes')
                                ->nullable(),

                            TextInput::make('pie_imagen')
                                ->label('Pie de imagen')
                                ->placeholder('Ej: Figura 1. Equipo después de la intervención'),
                        ])
                        ->columns(1)
                        ->addActionLabel('+ Agregar acción')
                        ->defaultItems(1)
                        ->maxItems(10),
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
