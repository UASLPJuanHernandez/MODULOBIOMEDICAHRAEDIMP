<?php

namespace App\Filament\Resources\ValeResource\Pages;

use App\Filament\Resources\ValeResource;
use App\Models\Mobiliario;
use App\Models\Movimiento;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateVale extends CreateRecord
{
    protected static string $resource = ValeResource::class;

    protected array $mobiliarios_para_asociar = [];

    public function mount(): void
    {
        parent::mount();
        
        // Pre-cargar datos si viene de un Movimiento
        $movimientoId = request()->get('movimiento_id');
        if ($movimientoId) {
            $this->precargarDesdeMovimiento($movimientoId);
        }
    }

    protected function precargarDesdeMovimiento(int $movimientoId): void
    {
        $movimiento = Movimiento::with(['mobiliarios', 'areaActual', 'usuario'])
            ->find($movimientoId);
            
        if (!$movimiento) {
            return;
        }

        // Pre-llenar el formulario con datos del movimiento
        $mobiliariosData = $movimiento->mobiliarios->map(function ($mobiliario) {
            return [
                'mobiliario_id' => $mobiliario->id,
                'descripcion' => $mobiliario->descripcion,
                'marca' => $mobiliario->marca,
                'modelo' => $mobiliario->modelo,
                'numero_serie' => $mobiliario->numero_serie,
            ];
        })->toArray();

        $this->form->fill([
            'tipo_vale' => 'resguardo',
            'mobiliarios_data' => $mobiliariosData,
            'movimiento_id' => $movimientoId,
            'observaciones' => "Vale generado desde movimiento: {$movimiento->numero_movimiento}\n" .
                             "Fecha: {$movimiento->fecha_movimiento->format('d/m/Y H:i')}\n" .
                             ($movimiento->observacion ? "Observaciones del movimiento: {$movimiento->observacion}" : ''),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // DEBUG: Registrar qué datos llegan
        Log::info('CreateVale - mutateFormDataBeforeCreate', [
            'data_keys' => array_keys($data),
            'mobiliarios_data' => $data['mobiliarios_data'] ?? 'NO EXISTE',
            'movimiento_id' => $data['movimiento_id'] ?? 'NO EXISTE'
        ]);
        
        // Procesar los datos del repeater mobiliarios_data
        if (isset($data['mobiliarios_data']) && is_array($data['mobiliarios_data'])) {
            $mobiliarios_ids = collect($data['mobiliarios_data'])
                ->pluck('mobiliario_id')
                ->filter()
                ->toArray();
            
            Log::info('CreateVale - mobiliarios_ids extraídos', [
                'ids' => $mobiliarios_ids,
                'count' => count($mobiliarios_ids)
            ]);
            
            // Para vales con múltiples mobiliarios (desde movimientos), usar null
            // Para vales individuales (manuales), usar el primer mobiliario para compatibilidad
            if (count($mobiliarios_ids) > 1) {
                $data['mobiliario_id'] = null; // Vale múltiple
            } elseif (count($mobiliarios_ids) == 1) {
                $data['mobiliario_id'] = $mobiliarios_ids[0]; // Vale individual
            } else {
                $data['mobiliario_id'] = null; // Sin mobiliarios
            }
            
            // Guardar los IDs para la relación many-to-many
            $this->mobiliarios_para_asociar = $mobiliarios_ids;
        } else {
            Log::warning('CreateVale - No hay mobiliarios_data o no es array');
            $this->mobiliarios_para_asociar = [];
        }

        // Remover el campo del repeater para que no cause problemas
        unset($data['mobiliarios_data']);
        
        // Establecer fecha de generación
        $data['fecha_generacion'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        Log::info('CreateVale - afterCreate iniciado', [
            'vale_id' => $this->record->id,
            'mobiliarios_para_asociar' => $this->mobiliarios_para_asociar ?? 'NO DEFINIDO'
        ]);
        
        // Asociar los mobiliarios a través de la relación many-to-many (sin campos adicionales)
        if (isset($this->mobiliarios_para_asociar) && !empty($this->mobiliarios_para_asociar)) {
            $this->record->mobiliarios()->sync($this->mobiliarios_para_asociar);
            
            Log::info('CreateVale - mobiliarios asociados', [
                'vale_id' => $this->record->id,
                'mobiliarios_asociados' => $this->mobiliarios_para_asociar
            ]);
        } else {
            Log::warning('CreateVale - No hay mobiliarios para asociar', [
                'vale_id' => $this->record->id
            ]);
        }
        
        // Si el vale fue creado desde un Movimiento, marcar como vale_generado
        if ($this->record->movimiento_id) {
            $movimiento = Movimiento::find($this->record->movimiento_id);
            if ($movimiento) {
                $movimiento->update([
                    'vale_generado' => true,
                    'vale_id' => $this->record->id
                ]);
                
                Log::info('CreateVale - movimiento actualizado', [
                    'movimiento_id' => $movimiento->id,
                    'numero_movimiento' => $movimiento->numero_movimiento
                ]);
            }
        }
        
        // Verificar el resultado final
        $vale_final = $this->record->fresh(['mobiliarios']);
        Log::info('CreateVale - resultado final', [
            'vale_id' => $vale_final->id,
            'mobiliarios_count' => $vale_final->mobiliarios->count(),
            'mobiliarios_ids' => $vale_final->mobiliarios->pluck('id')->toArray()
        ]);
    }
}
