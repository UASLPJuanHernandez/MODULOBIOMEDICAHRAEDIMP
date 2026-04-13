@extends('layouts.formatos')

@section('title', 'Registro — ' . $formato->nombre)
@section('breadcrumb', 'Ver registro')

@section('content')
<div class="mb-6 flex items-start justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $formato->nombre }}</h1>
        <p class="text-gray-500 text-sm mt-1">
            Registro #{{ $registro->id }}
            &mdash; Llenado por <span class="font-medium">{{ $registro->usuario->name ?? 'Desconocido' }}</span>
            el {{ $registro->created_at->format('d/m/Y H:i') }}
        </p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('formatos.llenar', $formato) }}"
           class="text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 font-medium px-4 py-2 rounded-lg transition">
            Nuevo registro
        </a>
        <a href="{{ route('formatos.index') }}"
           class="text-sm border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-lg transition">
            Volver a lista
        </a>
    </div>
</div>

@if(empty($secciones))
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-4 py-4 text-sm">
        Este registro no tiene valores guardados.
    </div>
@else
    @foreach($secciones as $seccion => $valores)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
            <h2 class="font-semibold text-gray-800 text-sm uppercase tracking-wide">{{ $seccion }}</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($valores as $item)
            <div class="px-6 py-3 grid grid-cols-3 gap-4 hover:bg-gray-50 transition">
                <div class="col-span-1">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $item['nombre'] }}</span>
                </div>
                <div class="col-span-2">
                    @if($item['tipo'] === 'checkbox')
                        @if($item['valor'] === '1')
                            <span class="inline-flex items-center gap-1 text-green-700 font-medium text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Sí
                            </span>
                        @else
                            <span class="text-gray-400 text-sm">No</span>
                        @endif
                    @elseif($item['tipo'] === 'textarea')
                        <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $item['valor'] ?: '—' }}</p>
                    @else
                        <span class="text-sm text-gray-800">{{ $item['valor'] ?: '—' }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
@endif
@endsection
