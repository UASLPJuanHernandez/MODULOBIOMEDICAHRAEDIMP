@extends('layouts.formatos')

@section('title', 'Definir campos — ' . $formato->nombre)
@section('breadcrumb', 'Definir campos')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Definir campos: <span class="text-blue-700">{{ $formato->nombre }}</span></h1>
    <p class="text-gray-500 text-sm mt-1">
        Marca los fragmentos que deben convertirse en campos editables del formulario.
        Asigna nombre, tipo, sección y orden a cada uno.
    </p>
</div>

@if(empty($contenido))
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-4 py-4 text-sm mb-6">
        No se pudo extraer contenido del documento. Verifica que el archivo .docx sea válido y vuelve a subirlo.
    </div>
@endif

<form action="{{ route('formatos.guardar-campos', $formato) }}" method="POST" id="form-campos">
    @csrf

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
        <ul class="list-disc pl-4 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="space-y-3" id="contenido-doc">
        @foreach($contenido as $idx => $bloque)

            @if($bloque['tipo'] === 'parrafo')
            <div class="bg-white border border-gray-200 rounded-xl p-4 hover:border-blue-300 transition" data-bloque="{{ $idx }}">
                <div class="flex items-start gap-4">
                    <input type="checkbox" id="check_{{ $idx }}" class="campo-check mt-1 w-4 h-4 accent-blue-600 cursor-pointer flex-shrink-0"
                           data-idx="{{ $idx }}" onchange="toggleCampo(this)">
                    <div class="flex-1">
                        <label for="check_{{ $idx }}" class="block text-sm text-gray-700 cursor-pointer mb-3 font-mono bg-gray-50 rounded p-2 border border-gray-100">
                            {{ $bloque['texto'] }}
                        </label>

                        <div class="campo-opciones hidden grid grid-cols-2 md:grid-cols-4 gap-3" id="opciones_{{ $idx }}">
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del campo</label>
                                <input type="text" name="campos[{{ $idx }}][nombre]"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej: fecha_ingreso">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                <select name="campos[{{ $idx }}][tipo]"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="text">Texto</option>
                                    <option value="number">Número</option>
                                    <option value="date">Fecha</option>
                                    <option value="textarea">Área de texto</option>
                                    <option value="checkbox">Casilla</option>
                                    <option value="select">Selección</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Sección</label>
                                <input type="text" name="campos[{{ $idx }}][seccion]"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej: Datos del paciente">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Orden</label>
                                <input type="number" name="campos[{{ $idx }}][orden]" value="{{ $idx }}" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            {{-- Campo oculto para enviar el índice sólo si está marcado --}}
                            <input type="hidden" name="campos[{{ $idx }}][_activo]" value="0" class="campo-activo-hidden">
                        </div>
                    </div>
                </div>
            </div>

            @elseif($bloque['tipo'] === 'tabla')
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Tabla</p>
                <div class="overflow-x-auto rounded-lg border border-gray-100">
                    <table class="min-w-full text-sm">
                        @foreach($bloque['filas'] as $filaIdx => $fila)
                        <tr class="{{ $filaIdx === 0 ? 'bg-gray-50 font-medium' : 'border-t border-gray-100' }}">
                            @foreach($fila as $celdaIdx => $celda)
                            @php $celdaKey = $idx . '_' . $filaIdx . '_' . $celdaIdx; @endphp
                            <td class="px-3 py-2 border border-gray-100 align-top">
                                <div class="flex items-start gap-2">
                                    <input type="checkbox" id="check_{{ $celdaKey }}"
                                           class="campo-check mt-0.5 w-4 h-4 accent-blue-600 flex-shrink-0"
                                           data-idx="{{ $celdaKey }}" onchange="toggleCampo(this)">
                                    <div class="flex-1">
                                        <label for="check_{{ $celdaKey }}" class="block cursor-pointer text-gray-700">
                                            {{ $celda ?: '(vacío)' }}
                                        </label>
                                        <div class="campo-opciones hidden mt-2 space-y-2" id="opciones_{{ $celdaKey }}">
                                            <input type="text" name="campos[{{ $celdaKey }}][nombre]"
                                                   class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                   placeholder="Nombre del campo">
                                            <div class="grid grid-cols-3 gap-1">
                                                <select name="campos[{{ $celdaKey }}][tipo]"
                                                        class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                    <option value="text">Texto</option>
                                                    <option value="number">Número</option>
                                                    <option value="date">Fecha</option>
                                                    <option value="textarea">Área</option>
                                                    <option value="checkbox">Casilla</option>
                                                    <option value="select">Selección</option>
                                                </select>
                                                <input type="text" name="campos[{{ $celdaKey }}][seccion]"
                                                       class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none"
                                                       placeholder="Sección">
                                                <input type="number" name="campos[{{ $celdaKey }}][orden]" value="{{ $celdaKey }}" min="0"
                                                       class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none">
                                            </div>
                                            <input type="hidden" name="campos[{{ $celdaKey }}][_activo]" value="0" class="campo-activo-hidden">
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            @endif

        @endforeach
    </div>

    <div class="mt-8 flex items-center gap-4">
        <button type="submit" id="btn-guardar"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition text-sm">
            Guardar campos seleccionados
        </button>
        <a href="{{ route('formatos.index') }}"
           class="border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-6 py-2.5 rounded-lg transition text-sm">
            Cancelar
        </a>
        <span class="text-sm text-gray-400" id="contador-campos">0 campos seleccionados</span>
    </div>
</form>

<script>
function toggleCampo(checkbox) {
    const idx = checkbox.dataset.idx;
    const opciones = document.getElementById('opciones_' + idx);
    const hidden = opciones?.querySelector('.campo-activo-hidden');

    if (checkbox.checked) {
        opciones?.classList.remove('hidden');
        if (hidden) hidden.value = '1';
    } else {
        opciones?.classList.add('hidden');
        if (hidden) hidden.value = '0';
    }
    actualizarContador();
}

function actualizarContador() {
    const total = document.querySelectorAll('.campo-check:checked').length;
    document.getElementById('contador-campos').textContent = total + ' campo' + (total !== 1 ? 's' : '') + ' seleccionado' + (total !== 1 ? 's' : '');
}

// Antes de enviar, eliminar los campos no activos del formulario
document.getElementById('form-campos').addEventListener('submit', function(e) {
    const activos = document.querySelectorAll('.campo-activo-hidden[value="0"]');
    activos.forEach(el => {
        // Deshabilitar todos los inputs del bloque inactivo para que no se envíen
        const contenedor = el.closest('.campo-opciones');
        if (contenedor) {
            contenedor.querySelectorAll('input, select').forEach(input => {
                if (!input.classList.contains('campo-activo-hidden')) {
                    input.disabled = true;
                }
            });
        }
    });

    const seleccionados = document.querySelectorAll('.campo-check:checked').length;
    if (seleccionados === 0) {
        e.preventDefault();
        alert('Selecciona al menos un campo antes de guardar.');
    }
});
</script>
@endsection
