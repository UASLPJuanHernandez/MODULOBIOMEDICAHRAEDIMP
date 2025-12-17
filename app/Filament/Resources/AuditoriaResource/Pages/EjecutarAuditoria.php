<?php

namespace App\Filament\Resources\AuditoriaResource\Pages;

use App\Filament\Resources\AuditoriaResource;
use App\Models\Auditoria;
use App\Models\AuditoriaItem;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;
use Filament\Actions;

class EjecutarAuditoria extends Page
{
    use InteractsWithRecord;
    
    protected static string $resource = AuditoriaResource::class;

    protected static string $view = 'filament.pages.ejecutar-auditoria';
    
    protected static ?string $title = 'Ejecutar Auditoría';
    
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        if ($this->record->estaCompletada()) {
            Notification::make()
                ->warning()
                ->title('Auditoría ya completada')
                ->body('Esta auditoría ya ha sido completada.')
                ->send();
                
            $this->redirect(AuditoriaResource::getUrl('view', ['record' => $this->record]));
        }
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('completar')
                ->label('Finalizar Auditoría')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Finalizar Auditoría')
                ->modalDescription('¿Estás seguro de que deseas completar esta auditoría? Verifica que todos los mobiliarios hayan sido revisados.')
                ->modalSubmitActionLabel('Sí, Finalizar')
                ->action('completarAuditoria'),
        ];
    }
    
    public function marcarPresente(int $itemId): void
    {
        $item = AuditoriaItem::findOrFail($itemId);
        $item->update([
            'presente' => true,
            'requiere_vale' => false,
            'fecha_verificacion' => now(),
        ]);
        
        $this->record->calcularEstadisticas();
        
        Notification::make()
            ->success()
            ->title('Mobiliario marcado como presente')
            ->send();
    }
    
    public function marcarAusente(int $itemId): void
    {
        $item = AuditoriaItem::findOrFail($itemId);
        $item->update([
            'presente' => false,
            'requiere_vale' => true,
            'fecha_verificacion' => now(),
        ]);
        
        $this->record->calcularEstadisticas();
        
        Notification::make()
            ->warning()
            ->title('Mobiliario marcado como ausente')
            ->body('Se requiere generar vale para este mobiliario.')
            ->send();
    }
    
    public function agregarComentario(int $itemId, string $comentario): void
    {
        $item = AuditoriaItem::findOrFail($itemId);
        $item->update([
            'comentarios' => $comentario,
        ]);
        
        // Si el mobiliario está ausente y requiere vale, generarlo automáticamente
        if ($item->requiere_vale && !$item->folio_vale && !$item->presente) {
            // Generar folio único para el vale
            $year = now()->year;
            $lastVale = AuditoriaItem::whereNotNull('folio_vale')
                ->whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();
                
            $nextNumber = $lastVale 
                ? intval(substr($lastVale->folio_vale, -4)) + 1 
                : 1;
                
            $folio = 'VALE-AUD-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            
            $item->update([
                'folio_vale' => $folio,
            ]);
            
            $this->record->calcularEstadisticas();
            
            Notification::make()
                ->success()
                ->title('Comentario guardado y vale generado')
                ->body("Vale creado con folio: {$folio}")
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title('Comentario guardado')
                ->send();
        }
    }
    
    public function generarVale(int $itemId): string
    {
        $item = AuditoriaItem::findOrFail($itemId);
        
        // Generar folio único para el vale
        $year = now()->year;
        $lastVale = AuditoriaItem::whereNotNull('folio_vale')
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
            
        $nextNumber = $lastVale 
            ? intval(substr($lastVale->folio_vale, -4)) + 1 
            : 1;
            
        $folio = 'VALE-AUD-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        $item->update([
            'folio_vale' => $folio,
        ]);
        
        $this->record->calcularEstadisticas();
        
        Notification::make()
            ->success()
            ->title('Vale generado')
            ->body("Folio: {$folio}")
            ->send();
            
        return route('auditoria.vale.pdf', [
            'auditoria' => $this->record->id,
            'item' => $item->id
        ]);
    }
    
    public function completarAuditoria(): void
    {
        $itemsSinVerificar = $this->record->items()
            ->whereNull('fecha_verificacion')
            ->count();
            
        if ($itemsSinVerificar > 0) {
            Notification::make()
                ->danger()
                ->title('Auditoría incompleta')
                ->body("Faltan {$itemsSinVerificar} mobiliarios por verificar.")
                ->send();
            return;
        }
        
        $this->record->update([
            'estado' => 'completada',
            'fecha_fin' => now(),
        ]);
        
        $this->record->calcularEstadisticas();
        
        Notification::make()
            ->success()
            ->title('Auditoría completada')
            ->body('La auditoría ha sido completada exitosamente.')
            ->send();
            
        $this->redirect(AuditoriaResource::getUrl('view', ['record' => $this->record]));
    }
    
    public function getItems()
    {
        return $this->record->items()
            ->with('mobiliario')
            ->orderBy('presente', 'asc')
            ->orderBy('fecha_verificacion', 'desc')
            ->get();
    }
}
