<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Información de la auditoría -->
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        Reporte de Auditoría #{{ $this->record->id }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $this->record->ubicacion->ubicacion_completa }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                        <x-heroicon-o-check-circle class="w-4 h-4 mr-1"/>
                        Completada
                    </div>
                </div>
            </div>
        </div>

        <!-- Previsualización del Reporte -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <!-- Encabezado de previsualización -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 flex items-center">
                        <x-heroicon-o-document-text class="w-5 h-5 mr-2 text-blue-600"/>
                        Previsualización del Reporte
                    </h3>
                    <span class="text-sm text-gray-500">
                        Vista previa del documento PDF
                    </span>
                </div>
            </div>

            <!-- Contenido del reporte en HTML -->
            <div class="p-8 max-w-5xl mx-auto" style="background: white;">
                @include('filament.pages.partials.reporte-preview', ['auditoria' => $this->getAuditoria()])
            </div>
        </div>

        <!-- Nota informativa -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-blue-600"/>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Información sobre el reporte
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Esta es una vista previa del reporte de auditoría. Para obtener el documento oficial en formato PDF, haz clic en el botón "Descargar Reporte PDF" en la parte superior.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
