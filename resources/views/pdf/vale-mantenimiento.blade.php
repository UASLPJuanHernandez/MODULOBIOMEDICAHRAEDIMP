<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vale de Mantenimiento - {{ $mantenimiento->folio_vale }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            margin: 0;
            padding: 15px;
            padding-bottom: 150px;
            position: relative;
            min-height: 100vh;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .section {
            margin-bottom: 12px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 11px;
            background-color: #333;
            color: white;
            padding: 5px 6px;
            margin-bottom: 6px;
        }
        
        .compact-info {
            font-size: 10px;
            line-height: 1.4;
        }
        
        .info-line {
            margin: 3px 0;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 120px;
        }
        
        .two-column {
            display: table;
            width: 100%;
        }
        
        .column {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
            vertical-align: top;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
        }
        
        .status-pendiente { background-color: #fff3cd; color: #856404; }
        .status-aceptado { background-color: #d4edda; color: #155724; }
        .status-completado { background-color: #d1ecf1; color: #0c5460; }
        .status-rechazado { background-color: #f8d7da; color: #721c24; }
        
        .footer {
            position: absolute;
            bottom: 15px;
            left: 15px;
            right: 15px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        
        .signatures {
            margin-top: 40px;
            margin-bottom: 0;
            display: table;
            width: 100%;
        }
        
        .signature {
            display: table-cell;
            text-align: center;
            width: 33.33%;
            padding: 0 5px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 3px;
            height: 40px;
        }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        @php
        $imagePath = public_path('images/vales/encabezado vale.jpg');
        $imageData = '';
        if (file_exists($imagePath)) {
            $imageData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($imagePath));
        }
        @endphp

        @if($imageData)
        <img src="{{ $imageData }}"
            alt="Encabezado Hospital"
            style="max-width: 100%; height: auto; margin-bottom: 5px; display: block; margin-left: auto; margin-right: auto;">
        @endif

        <div class="title">VALE DE MANTENIMIENTO N° {{ $mantenimiento->folio_vale }}</div>
    </div>
    
    <!-- Información del Equipo y Ubicación en dos columnas -->
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL EQUIPO Y UBICACIÓN</div>
        <div class="two-column compact-info">
            <div class="column">
                <div class="info-line"><span class="info-label">No. Control:</span> {{ $mobiliario->numero_control }}</div>
                <div class="info-line"><span class="info-label">Descripción:</span> {{ $mobiliario->descripcion }}</div>
                <div class="info-line"><span class="info-label">Marca/Modelo:</span> {{ $mobiliario->marca }} / {{ $mobiliario->modelo }}</div>
                <div class="info-line"><span class="info-label">No. Serie:</span> {{ $mobiliario->numero_serie ?: 'N/A' }}</div>
            </div>
            <div class="column">
                <div class="info-line"><span class="info-label">Área:</span> {{ $ubicacion['area'] ?? 'Sin ubicación' }}</div>
                <div class="info-line"><span class="info-label">Responsable:</span> {{ $ubicacion['responsable'] ?? 'Sin responsable' }}</div>
            </div>
        </div>
    </div>
    
    <!-- Información del Mantenimiento -->
    <div class="section">
        <div class="section-title">DETALLES DEL MANTENIMIENTO</div>
        <div class="two-column compact-info">
            <div class="column">
                <div class="info-line"><span class="info-label">Fecha Programada:</span> {{ $mantenimiento->fecha_programada->format('d/m/Y H:i') }}</div>
                <div class="info-line"><span class="info-label">Fecha Aceptación:</span> {{ $mantenimiento->fecha_aceptacion ? $mantenimiento->fecha_aceptacion->format('d/m/Y H:i') : 'Pendiente' }}</div>
                <div class="info-line"><span class="info-label">Tipo:</span> {{ $mantenimiento->tipo_mantenimiento === 'mantenimiento' ? 'Interno' : 'Proveedor Externo' }}
                    @if($mantenimiento->tipo_mantenimiento === 'proveedor' && $mantenimiento->proveedor_nombre) - {{ $mantenimiento->proveedor_nombre }}@endif
                </div>
            </div>
            <div class="column">
                <div class="info-line"><span class="info-label">Estado:</span> <span class="status-badge status-{{ $mantenimiento->estado }}">{{ ucfirst($mantenimiento->estado) }}</span></div>
                <div class="info-line"><span class="info-label">Solicitado por:</span> {{ $mantenimiento->usuarioSolicitante->name }}</div>
                <div class="info-line"><span class="info-label">Asignado a:</span> {{ $mantenimiento->usuarioMantenimiento ? $mantenimiento->usuarioMantenimiento->name : 'Sin asignar' }}</div>
            </div>
        </div>
        <div class="compact-info" style="margin-top: 4px;">
            <div class="info-line"><span class="info-label">Motivo:</span> {{ $mantenimiento->motivo }}</div>
            @if($mantenimiento->observaciones)
            <div class="info-line"><span class="info-label">Observaciones:</span> {{ $mantenimiento->observaciones }}</div>
            @endif
        </div>
    </div>
    
    <!-- Firmas -->
    <div class="signatures">
        <div class="signature">
            <div class="signature-line"></div>
            <div style="font-size: 9px;"><strong>SOLICITANTE</strong></div>
            <div style="font-size: 8px;">{{ $mantenimiento->usuarioSolicitante->name }}</div>
        </div>
        <div class="signature">
            <div class="signature-line"></div>
            <div style="font-size: 9px;"><strong>RECIBIDO POR</strong></div>
            <div style="font-size: 8px;">{{ $mantenimiento->usuarioMantenimiento ? $mantenimiento->usuarioMantenimiento->name : '________________' }}</div>
        </div>
        <div class="signature">
            <div class="signature-line"></div>
            <div style="font-size: 9px;"><strong>ENTREGADO POR</strong></div>
            <div style="font-size: 8px;">________________</div>
        </div>
    </div>
    
    <div class="footer">
        <p>Generado el {{ $fechaGeneracion }} — Área de Ingeniería Biomédica, Hospital Regional de Alta Especialidad "Dr. Ignacio Morones Prieto"</p>
        
        @php
        $footerImagePath = public_path('images/vales/pie de pagina vale.jpg');
        $footerImageData = '';
        if (file_exists($footerImagePath)) {
            $footerImageData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($footerImagePath));
        }
        @endphp

        @if($footerImageData)
        <img src="{{ $footerImageData }}"
            alt="Pie de página Hospital"
            style="max-width: 100%; height: auto; margin-top: 5px; display: block; margin-left: auto; margin-right: auto;">
        @endif
    </div>
</body>
</html>