<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use App\Exports\MobiliarioExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class GenerarReportes extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document';
    
    protected static ?string $navigationLabel = 'Generar Reportes';
    
    protected static ?string $navigationGroup = 'Reportes';
    
    protected static ?int $navigationSort = 50;
    
    protected static ?string $title = 'Generador de Reportes de Mobiliario';
    
    public static function shouldRegisterNavigation(): bool
    {
        return !auth()->user()?->hasRole('Personal de Mantenimiento') ?? true;
    }
    
    protected static string $view = 'filament.pages.generar-reportes';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Seleccionar Campos para el Reporte')
                    ->description('Selecciona los campos que deseas incluir en el reporte de Excel. Los campos se exportarán en el orden que aparecen aquí.')
                    ->schema([
                        CheckboxList::make('campos_seleccionados')
                            ->label('Campos a Incluir')
                            ->options([
                                'descripcion' => ' * Descripción',
                                'numero_inventario' => '* Número de Inventario',
                                'marca' => '* Marca',
                                'modelo' => '* Modelo',
                                'numero_serie' => '* Número de Serie',
                                'precio' => '* Precio (MXN)',
                                'ubicacion' => '* Ubicación',
                                'responsable' => '* Responsable Asignado',
                                'estado_mobiliario' => 'Estado del Mobiliario (Opcional)',
                                'metodo_adquisicion' => 'Método de Adquisición (Opcional)',
                                'numero_folio' => 'Número de Folio (Opcional)',
                            ])
                            ->descriptions([
                                'descripcion' => 'Descripción detallada del mobiliario',
                                'numero_inventario' => 'Código único del mobiliario',
                                'marca' => 'Marca del mobiliario',
                                'modelo' => 'Modelo del mobiliario',
                                'numero_serie' => 'Número de serie del equipo',
                                'precio' => 'Precio en pesos mexicanos',
                                'ubicacion' => 'Ubicación actual del mobiliario',
                                'responsable' => 'Persona responsable del mobiliario',
                                'estado_mobiliario' => 'Estado actual del mobiliario',
                                'metodo_adquisicion' => 'Forma de adquisición del mobiliario',
                                'numero_folio' => 'Número de folio asignado (cuando aplique)',
                            ])
                            ->columns(2)
                            ->gridDirection('row')
                            ->required()
                            ->validationMessages([
                                'required' => 'Debes seleccionar al menos un campo para generar el reporte.',
                            ])
                            ->helperText('Selecciona los campos que quieres incluir en tu reporte de Excel.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Generar Reporte')
                    ->schema([
                        Actions::make([
                            Action::make('generar_excel')
                                ->label('Generar Reporte Excel')
                                ->icon('heroicon-o-document')
                                ->color('success')
                                ->size('lg')
                                ->action('generarReporteExcel')
                                ->requiresConfirmation()
                                ->modalHeading('Generar Reporte de Mobiliario')
                                ->modalDescription('¿Estás seguro de que quieres generar el reporte con los campos seleccionados?')
                                ->modalSubmitActionLabel('Sí, generar reporte'),
                        ])->fullWidth(),
                    ]),
            ])
            ->statePath('data');
    }

    public function generarReporteExcel()
    {
        $data = $this->form->getState();
        
        if (empty($data['campos_seleccionados'])) {
            Notification::make()
                ->title('Error')
                ->body('Debes seleccionar al menos un campo para generar el reporte.')
                ->danger()
                ->send();
            
            return;
        }

        try {
            $camposSeleccionados = $data['campos_seleccionados'];
            $export = new MobiliarioExport($camposSeleccionados);
            
            $filename = 'reporte_mobiliario_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            Notification::make()
                ->title('Reporte generado exitosamente')
                ->body('El reporte de mobiliario se ha generado correctamente.')
                ->success()
                ->send();

            return Excel::download($export, $filename);
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al generar el reporte')
                ->body('Ocurrió un error al generar el reporte: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
