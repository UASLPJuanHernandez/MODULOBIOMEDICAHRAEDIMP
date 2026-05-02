<div class="w-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
                <th class="w-[130px] py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap" style="padding-left:1.5rem">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Identificador</th>
                <th class="w-[160px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Formato</th>
                <th class="w-[130px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Ingeniero</th>
                <th class="w-[175px] px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Estado</th>
                <th class="w-[180px] py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap" style="padding-right:1.5rem">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($registros as $reg)
            @php $estado = $reg->estado ?? 'pendiente'; @endphp
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                <td class="py-3.5 text-sm text-gray-500 dark:text-gray-400" style="padding-left:1.5rem">
                    {{ $reg->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3.5 font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ $reg->identificador ?: '—' }}
                </td>
                <td class="px-4 py-3.5 text-sm text-gray-600 dark:text-gray-400 truncate">
                    {{ $reg->formato?->nombre ?? '—' }}
                </td>
                <td class="px-4 py-3.5 text-sm text-gray-500 dark:text-gray-400 truncate">
                    {{ $reg->usuario?->name ?? '—' }}
                </td>
                <td class="px-4 py-3.5">
                    @if($estado === 'culminado')
                        <div class="flex flex-col gap-0.5">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full w-fit
                                         bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                                </svg>
                                Culminado
                            </span>
                            @if($reg->jefe)
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate">
                                {{ $reg->jefe->nombre }}
                                @if($reg->firmado_at) · {{ $reg->firmado_at->format('d/m/Y') }} @endif
                            </span>
                            @endif
                        </div>

                    @elseif($estado === 'en_firma')
                        <div class="flex flex-col gap-0.5">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full w-fit
                                         bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                En espera de firma
                            </span>
                            @if($reg->jefe)
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate">
                                → {{ $reg->jefe->nombre }}
                            </span>
                            @endif
                        </div>

                    @elseif($estado === 'en_curso')
                        <div class="flex flex-col gap-0.5">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full w-fit
                                         bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                                </svg>
                                En Curso
                            </span>
                            @if($reg->firmadoPor)
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate">
                                {{ $reg->firmadoPor->name }}
                                @if($reg->firmado_at) · {{ $reg->firmado_at->format('d/m/Y') }} @endif
                            </span>
                            @endif
                        </div>

                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full
                                     bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Pendiente
                        </span>
                    @endif
                </td>
                <td class="py-3.5 text-right" style="padding-right:1.5rem">
                    <div class="flex items-center justify-end gap-1.5">
                        {{-- Botón verde: previsualizar --}}
                        <button wire:click="verRegistroDoc({{ $reg->id }})"
                                class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg transition text-white"
                                style="background-color:#16a34a;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ver
                        </button>

                        {{-- Botón enviar (solo si no ha sido enviado aún) --}}
                        @if($estado === 'pendiente')
                        <button wire:click="abrirModalEnviar({{ $reg->id }})"
                                class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg transition
                                       bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50
                                       text-indigo-700 dark:text-indigo-300">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Enviar
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
