<?php

namespace App\Filament\Resources\MovimientoLoteResource\Pages;

use App\Filament\Resources\MovimientoLoteResource;
use App\Models\Mobiliario;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMovimientoLote extends CreateRecord
{
    protected static string $resource = MovimientoLoteResource::class;
    
    protected array $mobiliariosSeleccionados = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extraer los IDs de mobiliarios de mobiliarios_data
        $mobiliariosIds = $data['mobiliarios_data'] ?? [];
        unset($data['mobiliarios_data']);
        
        // Guardar los IDs para después
        $this->mobiliariosSeleccionados = $mobiliariosIds;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Crear el MovimientoLote
        $movimientoLote = static::getModel()::create($data);
        
        // Asociar los mobiliarios seleccionados
        if (!empty($this->mobiliariosSeleccionados)) {
            $mobiliariosData = [];
            
            foreach ($this->mobiliariosSeleccionados as $mobiliarioId) {
                $mobiliario = Mobiliario::find($mobiliarioId);
                if ($mobiliario) {
                    // Obtener la ubicación actual del mobiliario
                    $ubicacionActual = $mobiliario->ubicacionReal();
                    
                    $mobiliariosData[$mobiliarioId] = [
                        'area_anterior_id' => $ubicacionActual?->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    // Actualizar la ubicación del mobiliario
                    $mobiliario->update([
                        'localizacion_id' => $data['area_actual_id']
                    ]);
                }
            }
            
            // Sincronizar la relación many-to-many
            $movimientoLote->mobiliarios()->sync($mobiliariosData);
        }
        
        return $movimientoLote;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
