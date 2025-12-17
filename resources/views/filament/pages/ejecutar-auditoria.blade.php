<x-filament-panels::page>
    <div class="space-y-4">
        <!-- Header con información de la auditoría - COMPACTO -->
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">
                        Auditoría en {{ $record->ubicacion->ubicacion_completa }}
                    </h2>
                    <p class="text-xs text-gray-600 mt-1">
                        Responsable: {{ $record->responsable_nombre }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                        <x-heroicon-o-clock class="w-3 h-3 mr-1"/>
                        En Progreso
                    </div>
                </div>
            </div>

            <!-- Estadísticas en UNA SOLA LÍNEA -->
            <div class="mt-3 flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-600">Total:</span>
                    <span class="text-lg font-bold text-gray-900">{{ $record->total_mobiliarios }}</span>
                </div>
                <div class="h-6 w-px bg-gray-300"></div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-600">Presentes:</span>
                    <span class="text-lg font-bold text-green-600">{{ $record->mobiliarios_presentes }}</span>
                </div>
                <div class="h-6 w-px bg-gray-300"></div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-600">Ausentes:</span>
                    <span class="text-lg font-bold text-red-600">{{ $record->mobiliarios_ausentes }}</span>
                </div>
                <div class="h-6 w-px bg-gray-300"></div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-600">Vales:</span>
                    <span class="text-lg font-bold text-yellow-600">{{ $record->vales_generados }}</span>
                </div>
            </div>
        </div>

        <!-- Lista de mobiliarios en TABLA -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="p-3 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base font-semibold text-gray-900">
                    Mobiliario a Verificar
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider w-12">
                                Estado
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                                Descripción
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider w-32">
                                No. Control
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider w-32">
                                No. Inventario
                            </th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider w-32">
                                Marca/Modelo
                            </th>
                            <th scope="col" class="px-3 py-2 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider w-48">
                                ¿Está el Mobiliario?
                            </th>
                            <th scope="col" class="px-3 py-2 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider w-32">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($this->getItems() as $item)
                            <tr class="{{ $item->fecha_verificacion ? ($item->presente ? 'bg-green-50' : 'bg-red-50') : 'bg-white hover:bg-gray-50' }}">
                                <!-- Estado -->
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if($item->fecha_verificacion)
                                        @if($item->presente)
                                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mx-auto">
                                                <x-heroicon-o-check class="w-5 h-5 text-white"/>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mx-auto">
                                                <x-heroicon-o-x-mark class="w-5 h-5 text-white"/>
                                            </div>
                                        @endif
                                    @else
                                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mx-auto">
                                            <x-heroicon-o-question-mark-circle class="w-5 h-5 text-white"/>
                                        </div>
                                    @endif
                                </td>

                                <!-- Descripción -->
                                <td class="px-3 py-3">
                                    <div class="text-sm font-semibold text-gray-900">{{ $item->mobiliario->descripcion }}</div>
                                </td>

                                <!-- No. Control -->
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item->mobiliario->numero_control }}</div>
                                </td>

                                <!-- No. Inventario -->
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item->mobiliario->numero_inventario }}</div>
                                </td>

                                <!-- Marca/Modelo -->
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="text-xs text-gray-600">
                                        @if($item->mobiliario->marca || $item->mobiliario->modelo)
                                            {{ $item->mobiliario->marca }} {{ $item->mobiliario->modelo }}
                                        @else
                                            —
                                        @endif
                                    </div>
                                </td>

                                <!-- ¿Está el Mobiliario? -->
                                <td class="px-3 py-3">
                                    @if(!$item->fecha_verificacion)
                                        <div class="flex gap-2 justify-center">
                                            <button
                                                type="button"
                                                wire:click="marcarPresente({{ $item->id }})"
                                                style="background-color: #16a34a !important; color: white !important;"
                                                class="px-5 py-2.5 rounded-md shadow-md hover:opacity-90 font-extrabold text-base border-2 border-green-700"
                                            >
                                                ✓ SÍ
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="marcarAusente({{ $item->id }})"
                                                style="background-color: #dc2626 !important; color: white !important;"
                                                class="px-5 py-2.5 rounded-md shadow-md hover:opacity-90 font-extrabold text-base border-2 border-red-700"
                                            >
                                                ✗ NO
                                            </button>
                                        </div>
                                    @else
                                        <div class="flex justify-center">
                                            <span style="{{ $item->presente ? 'background-color: #16a34a; color: white;' : 'background-color: #dc2626; color: white;' }}" class="px-4 py-2 rounded-full text-base font-extrabold shadow-md border-2 {{ $item->presente ? 'border-green-700' : 'border-red-700' }}">
                                                {{ $item->presente ? '✓ SÍ' : '✗ NO' }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Acciones -->
                                <td class="px-3 py-3 text-center">
                                    @if($item->folio_vale)
                                        <a
                                            href="{{ route('auditoria.vale.pdf', ['auditoria' => $record->id, 'item' => $item->id]) }}"
                                            target="_blank"
                                            class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold hover:bg-yellow-200"
                                        >
                                            <x-heroicon-o-document-text class="w-3 h-3 mr-1"/>
                                            {{ $item->folio_vale }}
                                        </a>
                                    @elseif($item->requiere_vale && !$item->folio_vale && $item->fecha_verificacion && !$item->comentarios)
                                        <span class="text-xs text-gray-500 italic">
                                            Se generará con comentario
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Fila expandible para razón/comentarios cuando está ausente -->
                            @if($item->fecha_verificacion && !$item->presente)
                                <tr class="bg-yellow-50">
                                    <td colspan="7" class="px-3 py-2">
                                        <div class="flex items-start gap-2">
                                            <span class="text-xs font-bold text-red-700 whitespace-nowrap">⚠️ Razón:</span>
                                            @if($item->comentarios)
                                                <p class="text-xs text-gray-800">{{ $item->comentarios }}</p>
                                            @else
                                                <div class="flex-1 flex gap-2" x-data="{ comentario: '' }">
                                                    <textarea
                                                        x-model="comentario"
                                                        rows="1"
                                                        class="flex-1 text-xs rounded border-red-300 focus:border-red-500 focus:ring-red-500"
                                                        placeholder="Explica por qué no se encuentra aquí..."
                                                        required
                                                    ></textarea>
                                                    <button
                                                        type="button"
                                                        @click="$wire.agregarComentario({{ $item->id }}, comentario)"
                                                        x-bind:disabled="!comentario || comentario.trim() === ''"
                                                        class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:bg-gray-400 text-xs"
                                                    >
                                                        Guardar
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Botón para completar - COMPACTO -->
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Finalizar Auditoría</h3>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Verifica que todos los mobiliarios hayan sido revisados.
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="completarAuditoria"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center space-x-1 text-sm font-semibold"
                >
                    <x-heroicon-o-check-circle class="w-4 h-4"/>
                    <span>Completar</span>
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
