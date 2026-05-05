@php
    $foto = $getState();
    $fotoSrc = $foto
        ? (str_starts_with($foto, 'data:')
            ? $foto
            : \Illuminate\Support\Facades\Storage::url($foto))
        : null;
@endphp
@if($fotoSrc)
    <img src="{{ $fotoSrc }}" alt="Foto"
         style="width:120px; height:120px; object-fit:cover; border-radius:10px; border:2px solid #e5e7eb;" />
@endif
