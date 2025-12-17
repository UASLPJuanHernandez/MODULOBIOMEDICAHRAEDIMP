<?php

namespace App\Filament\Resources\MobiliarioResource\Pages;

use App\Filament\Resources\MobiliarioResource;
use App\Models\Vale;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateMobiliario extends CreateRecord
{
    protected static string $resource = MobiliarioResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Guardar los datos del vale temporalmente
        $this->valeData = [
            'responsable_entrega' => $data['responsable_entrega'] ?? null,
            'matricula_entrega' => $data['matricula_entrega'] ?? null,
            'responsable_recibe' => $data['responsable_recibe'] ?? null,
            'matricula_recibe' => $data['matricula_recibe'] ?? null,
            'fecha_asignacion' => $data['fecha_asignacion'] ?? now(),
            'observaciones_vale' => $data['observaciones_vale'] ?? null,
        ];

        // Remover los campos del vale de los datos del mobiliario
        unset(
            $data['responsable_entrega'],
            $data['matricula_entrega'],
            $data['responsable_recibe'],
            $data['matricula_recibe'],
            $data['fecha_asignacion'],
            $data['observaciones_vale']
        );

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Crear el mobiliario
        $mobiliario = parent::handleRecordCreation($data);

        // Generar el vale de resguardo automáticamente
        if (isset($this->valeData) && $this->valeData['responsable_entrega'] && $this->valeData['responsable_recibe']) {
            $this->generarValeResguardo($mobiliario);
        }

        return $mobiliario;
    }

    protected function generarValeResguardo($mobiliario): void
    {
        try {
            // Generar número de vale automático
            $year = now()->year;
            $ultimoVale = Vale::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();
            
            $numeroSecuencial = $ultimoVale ? (intval(substr($ultimoVale->numero_vale, -4)) + 1) : 1;
            $numeroVale = 'VALE-' . $year . '-' . str_pad($numeroSecuencial, 4, '0', STR_PAD_LEFT);

            // Crear el vale de resguardo
            $vale = Vale::create([
                'numero_vale' => $numeroVale,
                'tipo_vale' => 'resguardo',
                'mobiliario_id' => $mobiliario->id,
                'responsable_entrega' => $this->valeData['responsable_entrega'],
                'matricula_entrega' => $this->valeData['matricula_entrega'],
                'responsable_recibe' => $this->valeData['responsable_recibe'],
                'matricula_recibe' => $this->valeData['matricula_recibe'],
                'fecha_generacion' => $this->valeData['fecha_asignacion'],
                'observaciones' => $this->valeData['observaciones_vale'],
            ]);

            // Asociar el mobiliario al vale (relación many-to-many)
            $vale->mobiliarios()->attach($mobiliario->id);

            // Notificar éxito
            Notification::make()
                ->title('Vale de resguardo generado')
                ->body("Se ha generado automáticamente el vale {$numeroVale} para el mobiliario {$mobiliario->numero_control}")
                ->success()
                ->duration(5000)
                ->send();

        } catch (\Exception $e) {
            // Notificar error pero no fallar la creación del mobiliario
            Notification::make()
                ->title('Error al generar vale')
                ->body('El mobiliario se creó correctamente, pero hubo un error al generar el vale de resguardo: ' . $e->getMessage())
                ->warning()
                ->duration(8000)
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Mobiliario creado exitosamente';
    }
}
