<?php

namespace App\Filament\Widgets;

use App\Models\Movimiento;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MovimientosPendientesWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $movimientosPendientes = Movimiento::sinVale()->count();
        $movimientosRecientes = Movimiento::sinVale()->recientes(7)->count();
        
        return [
            Stat::make('Movimientos Pendientes de Vale', $movimientosPendientes)
                ->description('Total de movimientos sin vale generado')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($movimientosPendientes > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.movimientos.index', [
                    'tableFilters[sin_vale][isActive]' => true
                ])),
                
            Stat::make('Pendientes Últimos 7 días', $movimientosRecientes)
                ->description('Movimientos recientes sin vale')
                ->descriptionIcon('heroicon-m-clock')
                ->color($movimientosRecientes > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.movimientos.index', [
                    'tableFilters[sin_vale][isActive]' => true
                ])),
                
            Stat::make('Acción Requerida', $movimientosPendientes > 0 ? 'Generar Vales' : 'Todo al día')
                ->description($movimientosPendientes > 0 ? 'Haga clic para ver movimientos pendientes' : 'No hay movimientos pendientes')
                ->descriptionIcon($movimientosPendientes > 0 ? 'heroicon-m-document-plus' : 'heroicon-m-check-circle')
                ->color($movimientosPendientes > 0 ? 'info' : 'success')
                ->url($movimientosPendientes > 0 ? route('filament.admin.resources.movimientos.index') : null),
        ];
    }
    
    protected function getColumns(): int
    {
        return 3;
    }
    
    public static function canView(): bool
    {
        return Movimiento::sinVale()->exists();
    }
}
