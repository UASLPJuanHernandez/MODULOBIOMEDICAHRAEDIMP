<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Estadísticas del sistema -->

        <!-- Formulario de generación de reportes -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                    <x-heroicon-o-cog-6-tooth class="w-6 h-6 mr-2 text-gray-600"/>
                    Configurar Reporte
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Personaliza tu reporte seleccionando los campos que deseas incluir.
                </p>
            </div>
            
            <div class="p-6">
                {{ $this->form }}
            </div>
        </div>

        <!-- Información adicional -->
        <div class="bg-blue-50 rounded-lg border border-blue-200 p-6">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <x-heroicon-o-information-circle class="w-6 h-6 text-blue-600"/>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-blue-900">
                        Información sobre los Campos
                    </h3>
                    <div class="mt-2 text-sm text-blue-800">
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Campos Principales:</strong> Descripción, Número de Inventario, Marca, Modelo, Número de Serie, Precio, Ubicación, Responsable</li>
                            <li><strong>Campos Opcionales:</strong> Estado del Mobiliario, Método de Adquisición</li>
                            <li><strong>Orden:</strong> Los campos aparecerán en el Excel en el orden: Descripción → Núm. Inventario → Marca → Modelo → Serie → Precio → Ubicación → Responsable → Opcionales</li>
                            <li><strong>Formato:</strong> El archivo se descargará en formato .xlsx (Excel)</li>
                            <li><strong>Nota:</strong> Solo se incluyen equipos activos en el reporte. Los equipos dados de baja se gestionan por separado.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
