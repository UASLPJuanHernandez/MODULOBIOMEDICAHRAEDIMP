@php $firma = $getState(); @endphp

@if($firma)
    <div class="p-3 bg-white border border-gray-200 rounded-lg inline-block">
        <img src="{{ $firma }}" alt="Firma" style="max-height: 90px; max-width: 300px; object-fit: contain;" />
    </div>
@else
    <span class="text-sm text-gray-400 italic">Sin firma registrada</span>
@endif
