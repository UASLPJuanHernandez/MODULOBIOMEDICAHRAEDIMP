<?php

namespace App\Filament\Resources\MovimientoResource\Pages;

use App\Filament\Resources\MovimientoResource;
use App\Models\Mobiliario;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateMovimiento extends CreateRecord
{
    protected static string $resource = MovimientoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asegurar que el usuario actual se asigna
        $data['usuario_id'] = Auth::id();
        
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            // Extraer los IDs de mobiliarios seleccionados
            $mobiliariosIds = $data['mobiliarios_data'] ?? [];
            unset($data['mobiliarios_data']);
            
            // Crear el movimiento
            $movimiento = static::getModel()::create($data);
            
            // Obtener ubicaciones anteriores y asociar mobiliarios
            foreach ($mobiliariosIds as $mobiliarioId) {
                $mobiliario = Mobiliario::find($mobiliarioId);
                if ($mobiliario) {
                    $ubicacionAnterior = $mobiliario->ubicacionReal();
                    
                    // Asociar mobiliario con datos de área anterior
                    $movimiento->mobiliarios()->attach($mobiliarioId, [
                        'area_anterior_id' => $ubicacionAnterior?->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Actualizar la ubicación del mobiliario
                    $mobiliario->localizacion_id = $data['area_actual_id'];
                    $mobiliario->save();
                }
            }
            
            return $movimiento;
        });
    }
}
