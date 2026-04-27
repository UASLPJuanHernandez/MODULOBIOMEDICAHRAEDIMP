<!-- Encabezado -->
<div class="text-center border-b-2 border-gray-800 pb-4 mb-6">
    <div class="text-2xl font-bold text-blue-700">SISTEMA DE ACTIVO FIJO</div>
    <div class="text-xl font-bold text-gray-900 mt-2">REPORTE DE AUDITORÍA DE MOBILIARIO Y EQUIPO</div>
    <div class="text-base text-gray-600 mt-2">{{ $auditoria->ubicacion->ubicacion_completa }}</div>
    <div class="text-sm text-gray-600">Auditoría No. {{ $auditoria->id }} | {{ $auditoria->fecha_inicio->format('d/m/Y') }}</div>
</div>

<!-- Información General -->
<div class="bg-gray-50 border border-gray-300 rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 INFORMACIÓN GENERAL DE LA AUDITORÍA</h3>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <span class="font-bold text-gray-700">Ubicación Auditada:</span>
            <span class="text-gray-900">{{ $auditoria->ubicacion->ubicacion_completa }}</span>
        </div>
        <div>
            <span class="font-bold text-gray-700">Responsable del Área:</span>
            <span class="text-gray-900">{{ $auditoria->responsable_nombre }}</span>
        </div>
        <div>
            <span class="font-bold text-gray-700">Auditor:</span>
            <span class="text-gray-900">{{ $auditoria->usuario->name }}</span>
        </div>
        <div>
            <span class="font-bold text-gray-700">Fecha de Inicio:</span>
            <span class="text-gray-900">{{ $auditoria->fecha_inicio->format('d/m/Y H:i') }}</span>
        </div>
        <div>
            <span class="font-bold text-gray-700">Fecha de Finalización:</span>
            <span class="text-gray-900">{{ $auditoria->fecha_fin ? $auditoria->fecha_fin->format('d/m/Y H:i') : 'En progreso' }}</span>
        </div>
        <div>
            <span class="font-bold text-gray-700">Duración:</span>
            <span class="text-gray-900">
                @if($auditoria->fecha_fin)
                    {{ $auditoria->fecha_inicio->diffInHours($auditoria->fecha_fin) }} horas {{ $auditoria->fecha_inicio->diffInMinutes($auditoria->fecha_fin) % 60 }} minutos
                @else
                    —
                @endif
            </span>
        </div>
    </div>
    
    @if($auditoria->observaciones_generales)
    <div class="mt-4 pt-4 border-t border-gray-300">
        <p class="font-bold text-gray-700">Observaciones Generales:</p>
        <p class="text-gray-900 mt-1">{{ $auditoria->observaciones_generales }}</p>
    </div>
    @endif
</div>

<!-- Estadísticas -->
<div class="mb-6">
    <div class="bg-gray-800 text-white font-bold text-lg p-3 mb-4 rounded">
        📊 RESUMEN ESTADÍSTICO
    </div>
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 text-center">
            <div class="text-4xl font-bold text-blue-700">{{ $auditoria->total_mobiliarios }}</div>
            <div class="text-sm text-gray-600 mt-2 uppercase">Total Verificado</div>
        </div>
        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6 text-center">
            <div class="text-4xl font-bold text-green-600">{{ $auditoria->mobiliarios_presentes }}</div>
            <div class="text-sm text-gray-600 mt-2 uppercase">Presentes</div>
        </div>
        <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6 text-center">
            <div class="text-4xl font-bold text-red-600">{{ $auditoria->mobiliarios_ausentes }}</div>
            <div class="text-sm text-gray-600 mt-2 uppercase">Ausentes</div>
        </div>
        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-6 text-center">
            <div class="text-4xl font-bold text-yellow-600">{{ $auditoria->vales_generados }}</div>
            <div class="text-sm text-gray-600 mt-2 uppercase">Vales Generados</div>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <span class="font-bold text-gray-700">Porcentaje de Cumplimiento: </span>
        <span class="text-2xl font-bold {{ $auditoria->total_mobiliarios > 0 && ($auditoria->mobiliarios_presentes / $auditoria->total_mobiliarios * 100) >= 95 ? 'text-green-600' : 'text-red-600' }}">
            {{ $auditoria->total_mobiliarios > 0 ? number_format($auditoria->mobiliarios_presentes / $auditoria->total_mobiliarios * 100, 2) : 0 }}%
        </span>
    </div>
</div>

<!-- Mobiliario Presente -->
@if($auditoria->mobiliarios_presentes > 0)
<div class="mb-6">
    <div class="bg-gray-800 text-white font-bold text-lg p-3 mb-4 rounded">
        ✅ MOBILIARIO ENCONTRADO ({{ $auditoria->mobiliarios_presentes }})
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300">
            <thead class="bg-gray-600 text-white">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left">No. Control</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">No. Inventario</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Descripción</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Marca/Modelo</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Comentarios</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auditoria->itemsPresentes as $item)
                <tr class="even:bg-gray-50">
                    <td class="border border-gray-300 px-4 py-2">{{ $item->mobiliario->numero_control }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->mobiliario->numero_inventario }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->mobiliario->descripcion }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->mobiliario->marca }} {{ $item->mobiliario->modelo }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->comentarios ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Mobiliario Ausente -->
@if($auditoria->mobiliarios_ausentes > 0)
<div class="mb-6">
    <div class="bg-gray-800 text-white font-bold text-lg p-3 mb-4 rounded">
        ❌ MOBILIARIO NO LOCALIZADO ({{ $auditoria->mobiliarios_ausentes }})
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300">
            <thead class="bg-gray-600 text-white">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left">No. Control</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">No. Inventario</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Descripción</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Marca/Modelo</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Razón</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Vale</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auditoria->itemsAusentes as $item)
                <tr class="even:bg-gray-50">
                    <td class="border border-gray-300 px-4 py-2">{{ $item->mobiliario->numero_control }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->mobiliario->numero_inventario }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->mobiliario->descripcion }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->mobiliario->marca }} {{ $item->mobiliario->modelo }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->comentarios ?? '—' }}</td>
                    <td class="border border-gray-300 px-4 py-2">
                        @if($item->folio_vale)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">{{ $item->folio_vale }}</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Conclusiones y Recomendaciones -->
<div class="mb-6">
    <div class="bg-gray-800 text-white font-bold text-lg p-3 mb-4 rounded">
        📝 CONCLUSIONES Y RECOMENDACIONES
    </div>
    <div class="bg-white border border-gray-300 rounded p-4">
        @if($auditoria->mobiliarios_ausentes > 0)
            <h4 class="font-bold text-red-700 mb-2">Hallazgos Importantes:</h4>
            <ul class="list-disc list-inside space-y-2 text-gray-700 mb-4">
                <li>Se detectaron <strong class="text-red-600">{{ $auditoria->mobiliarios_ausentes }} mobiliarios ausentes</strong> en la ubicación</li>
                <li>Se generaron <strong class="text-yellow-600">{{ $auditoria->vales_generados }} vales</strong> de seguimiento</li>
                <li>Cumplimiento del <strong>{{ $auditoria->total_mobiliarios > 0 ? number_format($auditoria->mobiliarios_presentes / $auditoria->total_mobiliarios * 100, 2) : 0 }}%</strong></li>
            </ul>
            
            <h4 class="font-bold text-blue-700 mb-2">Recomendaciones:</h4>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li>Realizar búsqueda exhaustiva del mobiliario no localizado</li>
                <li>Actualizar ubicaciones en el sistema una vez encontrado el mobiliario</li>
                <li>Iniciar proceso administrativo según normativa vigente</li>
                <li>Actualizar registros en el sistema una vez localizado</li>
            </ul>
        @else
            <p class="text-green-700 font-semibold">✅ La auditoría concluyó satisfactoriamente con el 100% del mobiliario localizado en su ubicación asignada.</p>
        @endif
    </div>
</div>

<!-- Firmas -->
<div class="mt-12 mb-8">
    <div class="grid grid-cols-2 gap-12">
        <div class="text-center">
            <div class="border-t-2 border-gray-800 pt-2 mt-16">
                <p class="font-bold text-gray-900">AUDITOR</p>
                <p class="text-gray-700">{{ $auditoria->usuario->name }}</p>
                <p class="text-sm text-gray-600">Encargado de Activo Fijo</p>
                <p class="text-sm text-gray-600">Fecha: {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>
        <div class="text-center">
            <div class="border-t-2 border-gray-800 pt-2 mt-16">
                <p class="font-bold text-gray-900">RESPONSABLE DEL ÁREA</p>
                <p class="text-gray-700">{{ $auditoria->responsable_nombre }}</p>
                <p class="text-sm text-gray-600">{{ $auditoria->ubicacion->ubicacion_completa }}</p>
                <p class="text-sm text-gray-600">Fecha: {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="mt-8 text-center text-sm text-gray-600 border-t border-gray-300 pt-4">
    <p>Documento generado el {{ now()->format('d/m/Y H:i') }} — Área de Ingeniería Biomédica, Hospital Regional de Alta Especialidad "Dr. Ignacio Morones Prieto"</p>
    <p class="mt-1">Este documento es válido sin firma autógrafa conforme a las disposiciones vigentes</p>
</div>
