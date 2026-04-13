@php
    $firma = $getRecord()?->firma_svg ?? $getState();
    $esImagen = $firma && str_starts_with($firma, 'data:');
@endphp

<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4" style="max-width:320px">
    <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">Vista previa de la firma</p>
    @if($esImagen)
        <img src="{{ $firma }}" alt="Firma" style="max-height:70px; width:auto; display:block; mix-blend-mode:multiply;">
    @elseif($firma)
        <svg viewBox="0 0 200 70" width="100%" height="70"
             preserveAspectRatio="xMidYMid meet"
             style="display:block">
            {!! $firma !!}
        </svg>
    @endif
</div>
