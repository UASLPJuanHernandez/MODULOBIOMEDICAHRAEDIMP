<?php

namespace App\Filament\Resources\BitacoraReporteResource\Pages;

use App\Filament\Pages\DocumentosGenerados;
use App\Filament\Resources\BitacoraReporteResource;
use App\Models\FirmaSolicitud;
use App\Models\Ingeniero;
use App\Models\PersonalReportante;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

class FirmarBitacora extends Page
{
    use InteractsWithRecord;

    protected static string $resource = BitacoraReporteResource::class;
    protected static string $view     = 'filament.pages.firmar-bitacora';

    public array $ingenieros = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        abort_unless(static::getResource()::canEdit($this->record), 403);

        $this->ingenieros = Ingeniero::whereNotNull('firma_svg')
            ->where('firma_svg', '!=', '')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'cargo', 'firma_svg'])
            ->map(fn ($i) => [
                'id'     => $i->id,
                'nombre' => $i->nombre,
                'cargo'  => $i->cargo,
                'firma'  => $i->firma_svg,
            ])
            ->values()
            ->toArray();
    }

    public function guardarFirmaYEnviar(string $firmaData, ?string $firmaPosicion = null): void
    {
        // Guardar posición + imagen juntos para que BitacoraOverlayService
        // coloque la firma exactamente donde el ingeniero la posicionó
        $savedData = ($firmaPosicion)
            ? json_encode(['posicion' => json_decode($firmaPosicion, true), 'imagen' => $firmaData])
            : $firmaData;

        $this->record->update(['firma_ingeniero' => $savedData]);

        $area     = $this->record->area_departamento;
        $areaNorm = strtolower(trim(Str::ascii($area)));

        $jefe = PersonalReportante::where('es_jefe_servicio', true)
            ->where('estado', 'aprobado')
            ->get()
            ->first(fn ($j) => strtolower(trim(Str::ascii($j->area_jefe_servicio ?? ''))) === $areaNorm);

        if ($jefe && $this->record->reporte_pizarron_id) {
            FirmaSolicitud::firstOrCreate(
                [
                    'reporte_pizarron_id'    => $this->record->reporte_pizarron_id,
                    'personal_reportante_id' => $jefe->id,
                ],
                ['estado' => 'pendiente']
            );

            Notification::make()
                ->title('Reporte enviado al Jefe de Servicio')
                ->body("Firmado y enviado a {$jefe->nombre} — {$area}.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Firma guardada')
                ->body($jefe ? 'Firma guardada. No hay reporte enlazado.' : "No hay Jefe de Servicio registrado para «{$area}».")
                ->warning()
                ->send();
        }

        $this->redirect(DocumentosGenerados::getUrl());
    }
}
