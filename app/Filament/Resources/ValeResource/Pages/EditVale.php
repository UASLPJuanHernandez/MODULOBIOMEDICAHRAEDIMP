<?php

namespace App\Filament\Resources\ValeResource\Pages;

use App\Filament\Resources\ValeResource;
use App\Models\Mobiliario;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVale extends EditRecord
{
    protected static string $resource = ValeResource::class;

    protected array $mobiliarios_para_asociar = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar los mobiliarios asociados para el repeater
        $mobiliarios = $this->record->mobiliarios;
        
        $data['mobiliarios_data'] = $mobiliarios->map(function ($mobiliario) {
            return [
                'mobiliario_id' => $mobiliario->id,
                'descripcion' => $mobiliario->descripcion,
                'marca' => $mobiliario->marca,
                'modelo' => $mobiliario->modelo,
                'numero_serie' => $mobiliario->numero_serie,
            ];
        })->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Procesar los datos del repeater mobiliarios_data
        if (isset($data['mobiliarios_data']) && is_array($data['mobiliarios_data'])) {
            $mobiliarios_ids = collect($data['mobiliarios_data'])
                ->pluck('mobiliario_id')
                ->filter()
                ->toArray();
            
            // Para vales con múltiples mobiliarios, usar null
            // Para vales individuales, usar el primer mobiliario para compatibilidad
            if (count($mobiliarios_ids) > 1) {
                $data['mobiliario_id'] = null; // Vale múltiple
            } elseif (count($mobiliarios_ids) == 1) {
                $data['mobiliario_id'] = $mobiliarios_ids[0]; // Vale individual
            } else {
                $data['mobiliario_id'] = null; // Sin mobiliarios
            }
            
            // Guardar los IDs para la relación many-to-many
            $this->mobiliarios_para_asociar = $mobiliarios_ids;
        }

        // Remover el campo del repeater para que no cause problemas
        unset($data['mobiliarios_data']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Actualizar los mobiliarios asociados a través de la relación many-to-many (sin campos adicionales)
        if (isset($this->mobiliarios_para_asociar)) {
            $this->record->mobiliarios()->sync($this->mobiliarios_para_asociar);
        }
    }
}
