@php
    use Carbon\Carbon;
@endphp

<div class="historial-mantenimientos">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Historial de Mantenimientos
                    </h3>
                    <div class="mt-2 max-w-xl text-sm text-gray-500">
                        <p>Equipo: <strong>{{ $record->numero_control }}</strong> - {{ $record->descripcion }}</p>
                    </div>
                </div>
                <div class="mt-5 sm:mt-0 sm:ml-6 sm:flex-shrink-0 sm:flex sm:items-center">
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Total de mantenimientos</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $mantenimientos->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($mantenimientos->isEmpty())
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Sin mantenimientos registrados</h3>
            <p class="mt-1 text-sm text-gray-500">Este equipo no tiene historial de mantenimientos.</p>
        </div>
    @else
        <!-- Estadísticas rápidas -->
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pendientes</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $mantenimientos->where('estado', 'pendiente')->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Aceptados</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $mantenimientos->where('estado', 'aceptado')->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Completados</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $mantenimientos->where('estado', 'completado')->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Rechazados</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $mantenimientos->where('estado', 'rechazado')->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline de mantenimientos -->
        <div class="mt-8 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Timeline de Mantenimientos</h4>
                
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($mantenimientos as $index => $mantenimiento)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    
                                    <div class="relative flex space-x-3">
                                        <div>
                                            @php
                                                $iconClass = match($mantenimiento->estado) {
                                                    'pendiente' => 'bg-yellow-500',
                                                    'aceptado' => 'bg-green-500',
                                                    'completado' => 'bg-blue-500',
                                                    'rechazado' => 'bg-red-500',
                                                    default => 'bg-gray-500'
                                                };
                                            @endphp
                                            <span class="h-8 w-8 rounded-full {{ $iconClass }} flex items-center justify-center ring-8 ring-white">
                                                @if($mantenimiento->estado === 'completado')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @elseif($mantenimiento->estado === 'pendiente')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @elseif($mantenimiento->estado === 'aceptado')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <div class="min-w-0 flex-1">
                                            <div class="bg-gray-50 rounded-lg p-4">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                            {{ $mantenimiento->estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                            {{ $mantenimiento->estado === 'aceptado' ? 'bg-green-100 text-green-800' : '' }}
                                                            {{ $mantenimiento->estado === 'completado' ? 'bg-blue-100 text-blue-800' : '' }}
                                                            {{ $mantenimiento->estado === 'rechazado' ? 'bg-red-100 text-red-800' : '' }}">
                                                            {{ ucfirst($mantenimiento->estado) }}
                                                        </span>
                                                        
                                                        @if($mantenimiento->folio_vale)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                                {{ $mantenimiento->folio_vale }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="flex items-center space-x-2">
                                                        @if($mantenimiento->folio_vale)
                                                            <a href="{{ route('mantenimiento.vale.pdf', $mantenimiento) }}" 
                                                               target="_blank"
                                                               class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                Ver Vale
                                                            </a>
                                                        @endif
                                                        
                                                        <span class="text-sm text-gray-500">
                                                            {{ $mantenimiento->created_at->format('d/m/Y H:i') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        Tipo: {{ $mantenimiento->tipo_mantenimiento === 'mantenimiento' ? 'Interno' : 'Proveedor Externo' }}
                                                        @if($mantenimiento->proveedor_nombre)
                                                            - {{ $mantenimiento->proveedor_nombre }}
                                                        @endif
                                                    </p>
                                                    <p class="text-sm text-gray-600 mt-1">{{ $mantenimiento->motivo }}</p>
                                                    
                                                    @if($mantenimiento->observaciones)
                                                        <div class="mt-2 p-2 bg-white rounded border-l-4 border-blue-400">
                                                            <p class="text-sm text-gray-700">
                                                                <span class="font-medium">Observaciones:</span> {{ $mantenimiento->observaciones }}
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="mt-3 grid grid-cols-2 gap-4 text-sm text-gray-600">
                                                    <div>
                                                        <span class="font-medium">Solicitado por:</span> {{ $mantenimiento->usuarioSolicitante->name ?? 'N/A' }}
                                                    </div>
                                                    @if($mantenimiento->usuarioMantenimiento)
                                                        <div>
                                                            <span class="font-medium">Asignado a:</span> {{ $mantenimiento->usuarioMantenimiento->name }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <span class="font-medium">Programado:</span> {{ $mantenimiento->fecha_programada->format('d/m/Y H:i') }}
                                                    </div>
                                                    @if($mantenimiento->fecha_aceptacion)
                                                        <div>
                                                            <span class="font-medium">Aceptado:</span> {{ $mantenimiento->fecha_aceptacion->format('d/m/Y H:i') }}
                                                        </div>
                                                    @endif
                                                    @if($mantenimiento->fecha_completado)
                                                        <div class="col-span-2">
                                                            <span class="font-medium">Completado:</span> {{ $mantenimiento->fecha_completado->format('d/m/Y H:i') }}
                                                        </div>
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
            </div>
        </div>
    @endif
</div>

<style>
    .historial-mantenimientos {
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .historial-mantenimientos::-webkit-scrollbar {
        width: 6px;
    }
    
    .historial-mantenimientos::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .historial-mantenimientos::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .historial-mantenimientos::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
</style>