<!-- Encabezado -->
<div class="text-center border-b-2 border-gray-800 pb-3 mb-4">
    @php
    $imagePath = public_path('images/vales/encabezado vale.jpg');
    if (file_exists($imagePath)) {
        $imageData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($imagePath));
        echo '<img src="' . $imageData . '" alt="Encabezado Hospital" class="mx-auto mb-2" style="max-width: 70%; height: auto;">';
    }
    @endphp
    <div class="text-lg font-bold text-gray-900 mt-2">VALE DE MANTENIMIENTO N° {{ $mantenimiento->folio_vale }}</div>
</div>

<!-- Información del Equipo y Ubicación -->
<div class="mb-4">
    <div class="bg-gray-800 text-white font-bold text-sm p-2 mb-2 rounded">
        INFORMACIÓN DEL EQUIPO Y UBICACIÓN
    </div>
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div class="space-y-1">
            <div><span class="font-bold text-gray-700">No. Control:</span> <span class="text-gray-900">{{ $mobiliario->numero_control }}</span></div>
            <div><span class="font-bold text-gray-700">Descripción:</span> <span class="text-gray-900">{{ $mobiliario->descripcion }}</span></div>
            <div><span class="font-bold text-gray-700">Marca/Modelo:</span> <span class="text-gray-900">{{ $mobiliario->marca }} / {{ $mobiliario->modelo }}</span></div>
            <div><span class="font-bold text-gray-700">No. Serie:</span> <span class="text-gray-900">{{ $mobiliario->numero_serie ?: 'N/A' }}</span></div>
        </div>
        <div class="space-y-1">
            <div><span class="font-bold text-gray-700">Área:</span> <span class="text-gray-900">{{ $ubicacion['area'] ?? 'Sin ubicación' }}</span></div>
            <div><span class="font-bold text-gray-700">Responsable:</span> <span class="text-gray-900">{{ $ubicacion['responsable'] ?? 'Sin responsable' }}</span></div>
        </div>
    </div>
</div>

<!-- Información del Mantenimiento -->
<div class="mb-4">
    <div class="bg-gray-800 text-white font-bold text-sm p-2 mb-2 rounded">
        DETALLES DEL MANTENIMIENTO
    </div>
    <div class="grid grid-cols-2 gap-4 text-sm mb-2">
        <div class="space-y-1">
            <div><span class="font-bold text-gray-700">Fecha Programada:</span> <span class="text-gray-900">{{ $mantenimiento->fecha_programada->format('d/m/Y H:i') }}</span></div>
            <div><span class="font-bold text-gray-700">Fecha Aceptación:</span> <span class="text-gray-900">{{ $mantenimiento->fecha_aceptacion ? $mantenimiento->fecha_aceptacion->format('d/m/Y H:i') : 'Pendiente' }}</span></div>
            <div><span class="font-bold text-gray-700">Tipo:</span> <span class="text-gray-900">{{ $mantenimiento->tipo_mantenimiento === 'mantenimiento' ? 'Interno' : 'Proveedor Externo' }}
                @if($mantenimiento->tipo_mantenimiento === 'proveedor' && $mantenimiento->proveedor_nombre) - {{ $mantenimiento->proveedor_nombre }}@endif
            </span></div>
        </div>
        <div class="space-y-1">
            <div><span class="font-bold text-gray-700">Estado:</span> 
                @php
                    $estados = [
                        'pendiente' => ['text' => 'Pendiente', 'class' => 'bg-yellow-100 text-yellow-800'],
                        'aceptado' => ['text' => 'Aceptado', 'class' => 'bg-green-100 text-green-800'],
                        'completado' => ['text' => 'Completado', 'class' => 'bg-blue-100 text-blue-800'],
                        'rechazado' => ['text' => 'Rechazado', 'class' => 'bg-red-100 text-red-800'],
                    ];
                    $estado = $estados[$mantenimiento->estado] ?? ['text' => 'Desconocido', 'class' => 'bg-gray-100 text-gray-800'];
                @endphp
                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $estado['class'] }}">
                    {{ $estado['text'] }}
                </span>
            </div>
            <div><span class="font-bold text-gray-700">Solicitado por:</span> <span class="text-gray-900">{{ $mantenimiento->usuarioSolicitante->name }}</span></div>
            <div><span class="font-bold text-gray-700">Asignado a:</span> <span class="text-gray-900">{{ $mantenimiento->usuarioMantenimiento ? $mantenimiento->usuarioMantenimiento->name : 'Sin asignar' }}</span></div>
        </div>
    </div>
    <div class="text-sm space-y-1">
        <div><span class="font-bold text-gray-700">Motivo:</span> <span class="text-gray-900">{{ $mantenimiento->motivo }}</span></div>
        @if($mantenimiento->observaciones)
        <div><span class="font-bold text-gray-700">Observaciones:</span> <span class="text-gray-900">{{ $mantenimiento->observaciones }}</span></div>
        @endif
    </div>
</div>

<!-- Firmas -->
<div class="mt-8 mb-6">
    <div class="grid grid-cols-3 gap-6">
        <div class="text-center">
            <div class="border-t-2 border-gray-800 pt-2 mt-12">
                <p class="font-bold text-gray-900 text-sm">SOLICITANTE</p>
                <p class="text-gray-700 text-xs">{{ $mantenimiento->usuarioSolicitante->name }}</p>
            </div>
        </div>
        <div class="text-center">
            <div class="border-t-2 border-gray-800 pt-2 mt-12">
                <p class="font-bold text-gray-900 text-sm">RECIBIDO POR</p>
                <p class="text-gray-700 text-xs">{{ $mantenimiento->usuarioMantenimiento ? $mantenimiento->usuarioMantenimiento->name : '________________' }}</p>
            </div>
        </div>
        <div class="text-center">
            <div class="border-t-2 border-gray-800 pt-2 mt-12">
                <p class="font-bold text-gray-900 text-sm">ENTREGADO POR</p>
                <p class="text-gray-700 text-xs">________________</p>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="mt-6 text-center text-xs text-gray-600 border-t border-gray-300 pt-3">
    <p>Generado el {{ $fechaGeneracion }} — Área de Ingeniería Biomédica, Hospital Regional de Alta Especialidad "Dr. Ignacio Morones Prieto"</p>
    @php
    $footerImagePath = public_path('images/vales/pie de pagina vale.jpg');
    if (file_exists($footerImagePath)) {
        $footerImageData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($footerImagePath));
        echo '<img src="' . $footerImageData . '" alt="Pie de página Hospital" class="mx-auto mt-3" style="max-width: 70%; height: auto;">';
    }
    @endphp
</div>
