<div class="p-4 space-y-4">
    {{-- Meta del evento --}}
    <div class="flex flex-wrap gap-4 text-sm">
        <div>
            <span class="font-semibold text-gray-600">Evento:</span>
            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                {{ $tipo === 'creado' ? 'bg-green-100 text-green-800' : ($tipo === 'eliminado' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                {{ match($tipo) { 'creado' => 'Creado', 'actualizado' => 'Actualizado', 'eliminado' => 'Eliminado', default => ucfirst($tipo) } }}
            </span>
        </div>
        <div>
            <span class="font-semibold text-gray-600">Usuario:</span>
            <span class="ml-1 text-gray-800">{{ $usuario }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-600">Fecha:</span>
            <span class="ml-1 text-gray-800">{{ $fecha }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-600">IP:</span>
            <span class="ml-1 text-gray-500 font-mono text-xs">{{ $ip }}</span>
        </div>
    </div>

    @if(!empty($cambios))
        <div class="overflow-hidden rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600 w-1/4">Campo</th>
                        <th class="px-4 py-2 text-left font-semibold text-red-600 w-2/5">Valor anterior</th>
                        <th class="px-4 py-2 text-left font-semibold text-green-600 w-2/5">Valor nuevo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($cambios as $cambio)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-700">
                                {{ $cambio['etiqueta'] ?? $cambio['campo'] }}
                            </td>
                            <td class="px-4 py-2 text-red-700 bg-red-50">
                                {{ $cambio['anterior'] ?? '(vacío)' }}
                            </td>
                            <td class="px-4 py-2 text-green-700 bg-green-50">
                                {{ $cambio['nuevo'] ?? '(vacío)' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400 text-right">{{ count($cambios) }} campo(s) modificado(s)</p>
    @else
        <p class="text-sm text-gray-500 italic">Sin detalle de cambios para este evento.</p>
    @endif
</div>
