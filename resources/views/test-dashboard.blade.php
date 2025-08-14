<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Prueba</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .widget-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }
        .widget {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem;
            border: 1px solid #e5e7eb;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            margin: 0.5rem 0;
            background: #f9fafb;
            border-radius: 6px;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
        }
        .stat-success { border-left: 4px solid #10b981; }
        .stat-warning { border-left: 4px solid #f59e0b; }
        .stat-info { border-left: 4px solid #3b82f6; }
        .stat-danger { border-left: 4px solid #ef4444; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">
                            🏥 Dashboard de Prueba - Sistema de Activo Fijo
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-500">
                            👤 {{ auth()->user()->name }}
                        </span>
                        <a href="/simple-logout" class="text-sm text-red-600 hover:text-red-900">
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <h2 class="text-lg font-medium text-gray-900 mb-4">� Resumen de Movimientos</h2>

                <div class="widget-container">
                    <div class="widget">
                        <h3 class="text-lg font-semibold mb-3">📦 Movimientos Pendientes</h3>
                        @php
                            try {
                                $widget = new \App\Filament\Widgets\MovimientosPendientesWidget();
                                $reflection = new ReflectionClass($widget);
                                $method = $reflection->getMethod('getStats');
                                $method->setAccessible(true);
                                $stats = $method->invoke($widget);
                        @endphp
                                @foreach($stats as $stat)
                                    <div class="stat-item stat-{{ $stat->getColor() }}">
                                        <div>
                                            <div class="stat-label">{{ $stat->getLabel() }}</div>
                                            <div class="stat-value">{{ $stat->getValue() }}</div>
                                            @if($stat->getDescription())
                                                <div class="text-xs text-gray-500">{{ $stat->getDescription() }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                        @php
                            } catch (Exception $e) {
                        @endphp
                                <div class="stat-item stat-danger">
                                    <div>
                                        <div class="stat-label">Error</div>
                                        <div class="stat-value">❌</div>
                                        <div class="text-xs text-gray-500">{{ $e->getMessage() }}</div>
                                    </div>
                                </div>
                        @php
                            }
                        @endphp
                    </div>
                </div>

                <div class="mt-8 bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">🔗 Enlaces del Sistema</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="/admin" class="block p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <div class="font-medium text-blue-900">🎛️ Dashboard Filament</div>
                            <div class="text-sm text-blue-600">Panel de administración oficial</div>
                        </a>
                        <a href="/dashboard-debug" class="block p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                            <div class="font-medium text-green-900">🔍 Diagnóstico</div>
                            <div class="text-sm text-green-600">Información de debug del sistema</div>
                        </a>
                        <a href="/simple-logout" class="block p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <div class="font-medium text-red-900">🚪 Cerrar Sesión</div>
                            <div class="text-sm text-red-600">Salir del sistema</div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
