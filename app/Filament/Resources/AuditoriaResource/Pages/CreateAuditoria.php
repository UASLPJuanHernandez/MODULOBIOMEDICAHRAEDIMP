<?php

namespace App\Filament\Resources\AuditoriaResource\Pages;

use App\Filament\Resources\AuditoriaResource;
use App\Models\Auditoria;
use App\Models\AuditoriaItem;
use App\Models\Mobiliario;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateAuditoria extends CreateRecord
{
    protected static string $resource = AuditoriaResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = Auth::id();
        $data['estado'] = 'en_progreso';
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Obtener todos los mobiliarios de la ubicación seleccionada
        $mobiliarios = Mobiliario::where('localizacion_id', $this->record->ubicacion_id)
            ->where('dado_de_baja', false)
            ->get();
        
        // Crear items de auditoría para cada mobiliario
        foreach ($mobiliarios as $mobiliario) {
            AuditoriaItem::create([
                'auditoria_id' => $this->record->id,
                'mobiliario_id' => $mobiliario->id,
                'presente' => false,
                'requiere_vale' => false,
            ]);
        }
        
        // Actualizar estadísticas
        $this->record->calcularEstadisticas();
    }
    
    protected function getRedirectUrl(): string
    {
        return AuditoriaResource::getUrl('ejecutar', ['record' => $this->record]);
    }
}
