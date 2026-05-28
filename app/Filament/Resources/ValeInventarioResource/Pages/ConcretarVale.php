<?php

namespace App\Filament\Resources\ValeInventarioResource\Pages;

use App\Filament\Resources\ValeInventarioResource;
use App\Models\PersonalReportante;
use App\Services\GeminiService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class ConcretarVale extends EditRecord
{
    protected static string $resource = ValeInventarioResource::class;

    public function getTitle(): string
    {
        return 'Concretar vale';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $vale = $this->record;

        $sugerencias = (new GeminiService())->sugerirVale($vale);

        $campos = [
            'tipo', 'equipo_nombre', 'numero_inventario', 'area',
            'marca', 'modelo', 'numero_serie', 'unidad_medica',
            'usuario_nombre', 'quien_recibe', 'cargo_recibe', 'observaciones',
        ];

        foreach ($campos as $campo) {
            if (! empty($sugerencias[$campo]) && empty($data[$campo])) {
                $data[$campo] = $sugerencias[$campo];
            }
            // Siempre aplicar área mejorada por IA
            if ($campo === 'area' && ! empty($sugerencias['area'])) {
                $data['area'] = $sugerencias['area'];
            }
        }

        // Auto-rellenar jefe de servicio del área (para referencia en el PDF)
        if (empty($data['quien_recibe']) && ! empty($data['area'])) {
            $jefe = PersonalReportante::where('es_jefe_servicio', true)
                ->where('area_jefe_servicio', $data['area'])
                ->value('nombre');
            if ($jefe) {
                $data['quien_recibe'] = $jefe;
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ver_pdf')
                ->label('Vista previa')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => route('inventario.vale.preview', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar y firmar')
            ->icon('heroicon-o-pencil-square');
    }

    protected function getRedirectUrl(): string
    {
        return ValeInventarioResource::getUrl('firmar', ['record' => $this->record]);
    }
}
