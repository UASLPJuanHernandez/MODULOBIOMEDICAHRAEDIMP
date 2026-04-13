@extends('layouts.formatos')

@section('title', 'Llenar — ' . $formato->nombre)
@section('breadcrumb', 'Llenar formato')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ $formato->nombre }}</h1>
    <p class="text-gray-500 text-sm mt-1">Completa todos los campos del formulario.</p>
</div>

@if($campos->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-4 py-4 text-sm">
        Este formato no tiene campos definidos todavía.
        <a href="{{ route('formatos.definir-campos', $formato) }}" class="underline font-medium">Definir campos</a>
    </div>
@else
<form action="{{ route('formatos.guardar-registro', $formato) }}" method="POST" class="space-y-8">
    @csrf

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
        <ul class="list-disc pl-4 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @foreach($campos as $seccion => $camposSec)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
            <h2 class="font-semibold text-gray-800 text-sm uppercase tracking-wide">
                {{ $seccion ?: 'General' }}
            </h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($camposSec as $campo)
            <div class="{{ in_array($campo->tipo, ['textarea']) ? 'md:col-span-2' : '' }}">
                <label for="campo_{{ $campo->id }}"
                       class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $campo->nombre }}
                </label>

                @switch($campo->tipo)
                    @case('text')
                        <input type="text" id="campo_{{ $campo->id }}" name="campo_{{ $campo->id }}"
                               value="{{ old('campo_' . $campo->id) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('campo_' . $campo->id) border-red-400 @enderror">
                        @break
                    @case('number')
                        <input type="number" id="campo_{{ $campo->id }}" name="campo_{{ $campo->id }}"
                               value="{{ old('campo_' . $campo->id) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('campo_' . $campo->id) border-red-400 @enderror">
                        @break
                    @case('date')
                        <input type="date" id="campo_{{ $campo->id }}" name="campo_{{ $campo->id }}"
                               value="{{ old('campo_' . $campo->id) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('campo_' . $campo->id) border-red-400 @enderror">
                        @break
                    @case('textarea')
                        <textarea id="campo_{{ $campo->id }}" name="campo_{{ $campo->id }}" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('campo_' . $campo->id) border-red-400 @enderror">{{ old('campo_' . $campo->id) }}</textarea>
                        @break
                    @case('checkbox')
                        <div class="flex items-center gap-2 mt-1">
                            <input type="checkbox" id="campo_{{ $campo->id }}" name="campo_{{ $campo->id }}" value="1"
                                   {{ old('campo_' . $campo->id) ? 'checked' : '' }}
                                   class="w-5 h-5 accent-blue-600 cursor-pointer">
                            <label for="campo_{{ $campo->id }}" class="text-sm text-gray-600 cursor-pointer">Sí</label>
                        </div>
                        @break
                    @case('select')
                        <select id="campo_{{ $campo->id }}" name="campo_{{ $campo->id }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí" {{ old('campo_' . $campo->id) === 'Sí' ? 'selected' : '' }}>Sí</option>
                            <option value="No" {{ old('campo_' . $campo->id) === 'No' ? 'selected' : '' }}>No</option>
                            <option value="N/A" {{ old('campo_' . $campo->id) === 'N/A' ? 'selected' : '' }}>N/A</option>
                        </select>
                        @break
                @endswitch

                @error('campo_' . $campo->id)
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="flex items-center gap-4">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-2.5 rounded-lg transition text-sm">
            Guardar registro
        </button>
        <a href="{{ route('formatos.index') }}"
           class="border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-6 py-2.5 rounded-lg transition text-sm">
            Cancelar
        </a>
    </div>
</form>
@endif
@endsection
