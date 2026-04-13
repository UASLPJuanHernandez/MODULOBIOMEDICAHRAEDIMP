@extends('layouts.formatos')

@section('title', 'Subir formato')
@section('breadcrumb', 'Subir formato .docx')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-xl font-bold text-gray-900 mb-6">Subir nuevo formato</h1>

        <form action="{{ route('formatos.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre del formato</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required
                       placeholder="Ej: Hoja de reporte médico"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nombre') border-red-400 @enderror">
                @error('nombre')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="archivo" class="block text-sm font-medium text-gray-700 mb-1">Archivo .docx</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition cursor-pointer"
                     onclick="document.getElementById('archivo').click()">
                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm text-gray-500" id="archivo-label">Haz clic para seleccionar un archivo .docx</p>
                    <p class="text-xs text-gray-400 mt-1">Máximo 10 MB</p>
                    <input type="file" id="archivo" name="archivo" accept=".docx" required class="hidden"
                           onchange="document.getElementById('archivo-label').textContent = this.files[0]?.name || 'Selecciona un archivo'">
                </div>
                @error('archivo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition text-sm">
                    Subir y extraer contenido
                </button>
                <a href="{{ route('formatos.index') }}"
                   class="flex-1 text-center border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 rounded-lg transition text-sm">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
