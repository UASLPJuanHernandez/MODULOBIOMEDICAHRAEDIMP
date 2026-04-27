<x-filament-panels::page>

    <div class="space-y-4">

        {{-- Descripción --}}
        <div class="rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
            Registro completo de todos los cambios realizados en el inventario de equipos biomédicos.
            Cada modificación — por mínima que sea — queda registrada con usuario, fecha y valores anteriores/nuevos.
            Usa los filtros para acotar el período y descarga el PDF con el botón correspondiente.
        </div>

        {{-- Tabla --}}
        {{ $this->table }}

    </div>

</x-filament-panels::page>
