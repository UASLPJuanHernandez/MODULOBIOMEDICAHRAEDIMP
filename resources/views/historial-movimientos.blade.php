<div class="p-6">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">
            Historial de Movimientos - {{ $record->numero_control }}
        </h3>
        <p class="text-sm text-gray-600">
            {{ $record->descripcion }}
        </p>
    </div>

    <div class="flow-root">
        <ul role="list" class="-mb-8">
            @foreach($movimientos as $index => $movimiento)
                <li>
                    <div class="relative pb-8">
                        @if(!$loop->last)
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                        @endif
                        
                        <div class="relative flex space-x-3">
                            <div>
                                <span class="h-8 w-8 rounded-full {{ $index === 0 ? 'bg-green-500' : 'bg-blue-500' }} flex items-center justify-center ring-8 ring-white">
                                    @if($index === 0)
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                                        </svg>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="min-w-0 flex-1 pt-1.5">
                                <div class="flex justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $movimiento->tipo_movimiento }}
                                            @if($index === 0)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Ubicación Actual
                                                </span>
                                            @endif
                                        </p>
                                        
                                        <div class="mt-2 space-y-1">
                                            @if($movimiento->areaAnterior)
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">De:</span> {{ $movimiento->areaAnterior->ubicacion_completa }}
                                                </p>
                                            @endif
                                            
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">{{ $movimiento->areaAnterior ? 'A:' : 'Ubicación inicial:' }}</span> 
                                                {{ $movimiento->areaActual->ubicacion_completa }}
                                            </p>
                                            
                                            @if($movimiento->se_entrega_con)
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Se entrega con:</span> {{ $movimiento->se_entrega_con }}
                                                </p>
                                            @endif
                                            
                                            @if($movimiento->se_retira_con)
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Se retira con:</span> {{ $movimiento->se_retira_con }}
                                                </p>
                                            @endif
                                            
                                            @if($movimiento->observacion)
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Observaciones:</span> {{ $movimiento->observacion }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="text-right text-sm text-gray-500">
                                        <time datetime="{{ $movimiento->fecha_movimiento->toISOString() }}">
                                            {{ $movimiento->fecha_movimiento->format('d/m/Y H:i') }}
                                        </time>
                                        @if($movimiento->usuario)
                                            <p class="mt-1">Por: {{ $movimiento->usuario->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    
    @if($movimientos->count() > 0)
        <div class="mt-6 pt-4 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center">
                Total de movimientos: {{ $movimientos->count() }}
            </p>
        </div>
    @endif
</div>
