<x-filament-panels::page>

@php $ingenieros = $this->getIngenieros(); @endphp

@if($ingenieros->isEmpty())
    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:40vh; color:#9ca3af;">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:56px;height:56px;margin-bottom:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <p style="font-size:16px; font-family:sans-serif;">No hay ingenieros registrados</p>
    </div>
@else
    <div style="display:flex; flex-wrap:wrap; gap:22px; padding:8px 0; align-items:flex-start;">
        @foreach($ingenieros as $ing)
            @php
                $iniciales = collect(explode(' ', trim($ing->nombre)))
                    ->filter()
                    ->take(2)
                    ->map(fn($p) => strtoupper(substr($p, 0, 1)))
                    ->implode('');
                $paleta = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#ec4899','#06b6d4','#84cc16'];
                $color  = $paleta[$ing->id % count($paleta)];
            @endphp
            <a href="{{ \App\Filament\Resources\IngenierResource::getUrl('view', ['record' => $ing]) }}"
               style="display:block;width:220px;background:white;border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.09);border:1.5px solid #e5e7eb;text-decoration:none;color:inherit;transition:transform 0.15s,box-shadow 0.15s;"
               onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.15)'"
               onmouseout="this.style.transform='';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.09)'"
            >
                <div style="width:100%;height:160px;background:{{ $color }}18;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
                    @if($ing->foto)
                        @php
                            $fotoSrc = str_starts_with($ing->foto, 'data:')
                                ? $ing->foto
                                : \Illuminate\Support\Facades\Storage::url($ing->foto);
                        @endphp
                        <img src="{{ $fotoSrc }}" alt="{{ $ing->nombre }}" style="width:100%;height:100%;object-fit:cover;" />
                    @else
                        <span style="font-size:56px;font-weight:800;color:{{ $color }};opacity:0.7;line-height:1;user-select:none;">{{ $iniciales }}</span>
                    @endif
                    <div style="position:absolute;top:10px;right:10px;">
                        @if($ing->activo)
                            <span style="font-size:10px;padding:3px 9px;border-radius:999px;background:rgba(22,163,74,0.9);color:white;font-weight:600;backdrop-filter:blur(4px);">Activo</span>
                        @else
                            <span style="font-size:10px;padding:3px 9px;border-radius:999px;background:rgba(107,114,128,0.85);color:white;font-weight:600;backdrop-filter:blur(4px);">Inactivo</span>
                        @endif
                    </div>
                </div>
                <div style="padding:16px;">
                    <p style="font-size:14px;font-weight:700;color:#111827;margin:0 0 3px 0;line-height:1.3;">{{ $ing->nombre }}</p>
                    @if($ing->cargo)
                        <p style="font-size:12px;color:#6b7280;margin:0 0 14px 0;">{{ $ing->cargo }}</p>
                    @else
                        <p style="font-size:12px;color:#d1d5db;margin:0 0 14px 0;font-style:italic;">Sin cargo</p>
                    @endif
                    <div style="border-top:1px solid #f3f4f6;padding-top:12px;display:flex;gap:10px;">
                        <div style="flex:1;text-align:center;">
                            <p style="font-size:20px;font-weight:800;color:#1d4ed8;margin:0;line-height:1;">{{ $ing->total_reportes }}</p>
                            <p style="font-size:10px;color:#9ca3af;margin:2px 0 0 0;">Total</p>
                        </div>
                        <div style="width:1px;background:#f3f4f6;"></div>
                        <div style="flex:1;text-align:center;">
                            <p style="font-size:20px;font-weight:800;color:{{ $ing->reportes_activos > 0 ? '#d97706' : '#6b7280' }};margin:0;line-height:1;">{{ $ing->reportes_activos }}</p>
                            <p style="font-size:10px;color:#9ca3af;margin:2px 0 0 0;">Activos</p>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif

</x-filament-panels::page>
