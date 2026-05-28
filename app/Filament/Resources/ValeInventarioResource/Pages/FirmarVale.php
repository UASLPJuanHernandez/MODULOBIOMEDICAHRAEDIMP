<?php

namespace App\Filament\Resources\ValeInventarioResource\Pages;

use App\Filament\Pages\DocumentosGenerados;
use App\Filament\Resources\ValeInventarioResource;
use App\Models\Ingeniero;
use App\Models\PersonalReportante;
use App\Services\AuditService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

class FirmarVale extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ValeInventarioResource::class;
    protected static string $view     = 'filament.pages.firmar-vale';

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
        $savedData = ($firmaPosicion)
            ? json_encode(['posicion' => json_decode($firmaPosicion, true), 'imagen' => $firmaData])
            : $firmaData;

        $this->record->update(['firma_ingeniero' => $savedData]);

        $area     = $this->record->area ?? '';
        $areaNorm = strtolower(trim(Str::ascii($area)));

        $jefe = PersonalReportante::where('es_jefe_servicio', true)
            ->where('estado', 'aprobado')
            ->get()
            ->first(fn ($j) => strtolower(trim(Str::ascii($j->area_jefe_servicio ?? ''))) === $areaNorm);

        if ($jefe) {
            $this->record->update([
                'estado'  => 'en_firma',
                'jefe_id' => $jefe->id,
            ]);

            AuditService::log('firma', "Vale #{$this->record->id} firmado por ingeniero y enviado a {$jefe->nombre}", [
                'actor_tipo'     => 'admin',
                'actor_id'       => auth()->id() ?? 0,
                'actor_nombre'   => auth()->user()?->name ?? 'Ingeniero',
                'documento_tipo' => 'vale',
                'documento_id'   => $this->record->id,
                'metadata'       => ['area' => $area, 'jefe' => $jefe->nombre],
            ]);

            Notification::make()
                ->title('Vale enviado al Jefe de Servicio')
                ->body("Firmado y enviado a {$jefe->nombre} — {$area}.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Firma guardada')
                ->body("No hay Jefe de Servicio registrado para «{$area}». El vale queda pendiente de asignación.")
                ->warning()
                ->send();
        }

        $this->redirect(DocumentosGenerados::getUrl());
    }
}
