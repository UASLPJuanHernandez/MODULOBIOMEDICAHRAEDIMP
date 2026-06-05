<?php

namespace App\Filament\Widgets;

use App\Models\Mantenimiento;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdenesServicioWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        try {
            $total = Mantenimiento::count();
            $pendientes = Mantenimiento::where('estado', 'pendiente')->count();
            $proceso = Mantenimiento::where('estado', 'aceptado')->count();
            $completadas = Mantenimiento::where('estado', 'completado')->count();
            $hoy = Mantenimiento::whereDate('created_at', today())->count();

            $porcentajeCompletadas = $total > 0 ? round(($completadas / $total) * 100, 1) : 0;

            return [
                Stat::make('Pendientes', $pendientes)
                    ->description('Órdenes sin atender')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color($pendientes > 0 ? 'warning' : 'success'),

                Stat::make('En Proceso', $proceso)
                    ->description('En atención')
                    ->descriptionIcon('heroicon-m-wrench-screwdriver')
                    ->color('info'),

                Stat::make('Completadas', $completadas)
                    ->description($porcentajeCompletadas . '% del total')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('Generadas Hoy', $hoy)
                    ->description('Órdenes nuevas')
                    ->descriptionIcon('heroicon-m-bolt')
                    ->color('info'),

                Stat::make('Total', $total)
                    ->description('Órdenes en el sistema')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('gray'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make('Error Widget Órdenes', 'N/A')
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
        return false;
    }
}
