<?php

namespace App\Filament\Resources\BitacoraReporteResource\Pages;

use App\Filament\Resources\BitacoraReporteResource;
use App\Models\PersonalReportante;
use App\Services\BitacoraDocxService;
use App\Services\GeminiService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBitacoraReporte extends EditRecord
{
    protected static string $resource = BitacoraReporteResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $reporte = $this->record->reporte;
        if (! $reporte) {
            return $data;
        }

        $sugerencias = (new GeminiService())->sugerirBitacora($reporte);

        $camposSimples = [
            'nombre_dispositivo',
            'marca',
            'modelo',
            'area_departamento',
            'nombre_personal',
            'fecha_reporte',
            'hora_reporte',
            'atiende_nombre',
        ];

        foreach ($camposSimples as $campo) {
            if (! empty($sugerencias[$campo]) && empty($data[$campo])) {
                $data[$campo] = $sugerencias[$campo];
            }
        }

        // Recibió: jefe de servicio del área seleccionada
        if (empty($data['recibe_nombre'])) {
            $area = $data['area_departamento'] ?? null;
            if ($area) {
                $jefe = PersonalReportante::where('es_jefe_servicio', true)
                    ->where('area_jefe_servicio', $area)
                    ->value('nombre');
                if ($jefe) {
                    $data['recibe_nombre'] = $jefe;
                }
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('descargar')
                ->label('Descargar DOCX')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $service = new BitacoraDocxService();
                    $path    = $service->generar($this->record);
                    return response()->download($path, 'Bitacora_' . $this->record->id . '.docx')
                        ->deleteFileAfterSend(true);
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Firmar')
            ->icon('heroicon-o-pencil-square');
    }

    protected function getRedirectUrl(): string
    {
        return BitacoraReporteResource::getUrl('firmar', ['record' => $this->record]);
    }
}
