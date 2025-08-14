<?php

namespace App\Filament\Widgets;

use App\Models\Movimiento;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MovimientosPendientesWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        try {
            $total = Movimiento::count();
            $pendientes = Movimiento::where(function($q){
                $q->whereNull('vale_id')->orWhere('vale_generado', false);
            })->count();
            $conVale = $total - $pendientes;
            $hoy = Movimiento::whereDate('created_at', today())->count();
            $ult7 = Movimiento::where('created_at', '>=', now()->subDays(7))->count();

            $porcentaje = $total > 0 ? round(($conVale / $total) * 100, 1) : 0;

            return [
                Stat::make('Pendientes de Vale', $pendientes)
                    ->description('Sin vale generado')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color($pendientes > 0 ? 'warning' : 'success'),

                Stat::make('Con Vale', $conVale)
                    ->description($porcentaje . '% completados')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('Hoy', $hoy)
                    ->description('Generados hoy')
                    ->descriptionIcon('heroicon-m-bolt')
                    ->color('info'),

                Stat::make('Últimos 7 días', $ult7)
                    ->description('Actividad reciente')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('info'),

                Stat::make('Total Movimientos', $total)
                    ->description('En el sistema')
                    ->descriptionIcon('heroicon-m-arrow-path')
                    ->color('gray'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make('Error Widget Movimientos', 'N/A')
                    ->description($e->getMessage())
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'),
            ];
        }
    }

    protected function getColumns(): int
    {
        return 3;
    }

    public static function canView(): bool
    {
        return true;
    }
}
