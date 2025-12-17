<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Mantenimiento;
use Illuminate\Support\Facades\DB;

class MantenimientoStatsWidget extends ChartWidget
{
    protected static ?string $heading = 'Estado de Mantenimientos';
    
    protected static ?string $description = 'Distribución de mantenimientos por estado';

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = Mantenimiento::select('estado', DB::raw('count(*) as count'))
            ->groupBy('estado')
            ->pluck('count', 'estado')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Mantenimientos',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#f59e0b', // Pendiente - amarillo
                        '#10b981', // Aceptado - verde
                        '#3b82f6', // Completado - azul
                        '#ef4444', // Rechazado - rojo
                    ],
                    'borderWidth' => 2,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => array_map('ucfirst', array_keys($data)),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ": " + context.parsed + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }

    public function getDescription(): ?string
    {
        $total = Mantenimiento::count();
        $pendientes = Mantenimiento::where('estado', 'pendiente')->count();
        
        if ($total === 0) {
            return 'No hay mantenimientos registrados';
        }
        
        return "Total: {$total} mantenimientos • {$pendientes} pendientes";
    }
}
