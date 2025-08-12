<?php

namespace App\Filament\Resources\MovimientoResource\Pages;

use App\Filament\Resources\MovimientoResource;
use App\Models\Mobiliario;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditMovimiento extends EditRecord
{
    protected static string $resource = MovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Preparar los datos de mobiliarios para el formulario
        $movimiento = $this->record;
        if ($movimiento && $movimiento->mobiliarios) {
            $data['mobiliarios_data'] = $movimiento->mobiliarios->pluck('id')->toArray();
        }
        
        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($record, $data) {
            $mobiliariosIds = $data['mobiliarios_data'] ?? [];
            unset($data['mobiliarios_data']);
            
            // Obtener mobiliarios actuales para restaurar ubicaciones si es necesario
            $mobiliariosActuales = $record->mobiliarios->pluck('id')->toArray();
            
            // Actualizar el movimiento
            $record->update($data);
            
            // Sync mobiliarios with area anterior data
            $syncData = [];
            foreach ($mobiliariosIds as $mobiliarioId) {
                $mobiliario = Mobiliario::find($mobiliarioId);
                if ($mobiliario) {
                    // Para mobiliarios nuevos, usar su ubicación actual como anterior
                    // Para mobiliarios existentes, mantener el área anterior del pivot
                    $pivot = $record->mobiliarios()->where('mobiliario_id', $mobiliarioId)->first();
                    $areaAnteriorId = $pivot?->pivot->area_anterior_id ?? $mobiliario->localizacion_id;
                    
                    $syncData[$mobiliarioId] = [
                        'area_anterior_id' => $areaAnteriorId,
                        'updated_at' => now()
                    ];
                }
            }
            
            $record->mobiliarios()->sync($syncData);
            
            // Actualizar ubicaciones de mobiliarios
            foreach ($mobiliariosIds as $mobiliarioId) {
                $mobiliario = Mobiliario::find($mobiliarioId);
                if ($mobiliario) {
                    $mobiliario->localizacion_id = $data['area_actual_id'];
                    $mobiliario->save();
                }
            }
            
            // Restaurar ubicación anterior de mobiliarios que fueron removidos
            $mobiliariosRemovidos = array_diff($mobiliariosActuales, $mobiliariosIds);
            foreach ($mobiliariosRemovidos as $mobiliarioId) {
                $mobiliario = Mobiliario::find($mobiliarioId);
                if ($mobiliario) {
                    // Buscar el área anterior en el pivot antes de que se haga sync
                    $pivot = DB::table('movimiento_mobiliario')
                        ->where('movimiento_id', $record->id)
                        ->where('mobiliario_id', $mobiliarioId)
                        ->first();
                    
                    if ($pivot && $pivot->area_anterior_id) {
                        $mobiliario->localizacion_id = $pivot->area_anterior_id;
                        $mobiliario->save();
                    }
                }
            }
            
            return $record;
        });
    }
}
