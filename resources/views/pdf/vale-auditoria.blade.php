<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vale de Auditoría - {{ $item->folio_vale }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 18px;
            font-weight: bold;
            color: #2c5aa0;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .alert-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert-box h3 {
            margin: 0 0 5px 0;
            color: #856404;
            font-size: 16px;
        }
        .info-section {
            margin: 15px 0;
        }
        .info-section h3 {
            font-weight: bold;
            font-size: 14px;
            background-color: #f0f0f0;
            padding: 8px;
            border: 1px solid #ccc;
            margin: 0 0 10px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 3px;
        }
        .label {
            font-weight: bold;
            min-width: 180px;
        }
        .value {
            flex: 1;
            text-align: right;
        }
        .acciones-box {
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ccc;
        }
        .acciones-box p {
            margin: 0 0 8px 0;
            font-size: 12px;
        }
        .acciones-box p:last-child {
            margin-bottom: 0;
        }
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
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
            style="max-width: 100%; height: auto; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
        @endif

        <div class="title">VALE DE AUDITORÍA - MOBILIARIO NO LOCALIZADO</div>
        <div>N° {{ $item->folio_vale }}</div>
    </div>
    
    <!-- Alerta -->
    <div class="alert-box">
        <h3>⚠️ MOBILIARIO NO ENCONTRADO EN UBICACIÓN ASIGNADA</h3>
        <p style="margin: 5px 0 0 0; font-size: 11px;">Este vale documenta la ausencia del mobiliario durante la auditoría</p>
    </div>
    
    <!-- Información de la Auditoría -->
    <div class="info-section">
        <h3>INFORMACIÓN DE LA AUDITORÍA</h3>
        <div class="info-row">
            <span class="label">Fecha de Auditoría:</span>
            <span class="value">{{ $auditoria->fecha_inicio->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Ubicación Auditada:</span>
            <span class="value">{{ $auditoria->ubicacion->ubicacion_completa }}</span>
        </div>
        <div class="info-row">
            <span class="label">Auditor:</span>
            <span class="value">{{ $auditoria->usuario->name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Responsable del Área:</span>
            <span class="value">{{ $auditoria->responsable_nombre }}</span>
        </div>
    </div>
    
    <!-- Información del Mobiliario -->
    <div class="info-section">
        <h3>INFORMACIÓN DEL MOBILIARIO NO LOCALIZADO</h3>
        <div class="info-row">
            <span class="label">Código Institucional:</span>
            <span class="value"><strong>{{ $mobiliario->numero_control }}</strong></span>
        </div>
        <div class="info-row">
            <span class="label">Número de Inventario:</span>
            <span class="value">{{ $mobiliario->numero_inventario }}</span>
        </div>
        <div class="info-row">
            <span class="label">Descripción:</span>
            <span class="value">{{ $mobiliario->descripcion }}</span>
        </div>
        @if($mobiliario->marca)
        <div class="info-row">
            <span class="label">Marca:</span>
            <span class="value">{{ $mobiliario->marca }}</span>
        </div>
        @endif
        @if($mobiliario->modelo)
        <div class="info-row">
            <span class="label">Modelo:</span>
            <span class="value">{{ $mobiliario->modelo }}</span>
        </div>
        @endif
        @if($mobiliario->numero_serie)
        <div class="info-row">
            <span class="label">Número de Serie:</span>
            <span class="value">{{ $mobiliario->numero_serie }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="label">Ubicación Registrada:</span>
            <span class="value">{{ $mobiliario->localizacion->ubicacion_completa ?? 'Sin ubicación' }}</span>
        </div>
    </div>
    
    <!-- Detalles de la Verificación -->
    <div class="info-section">
        <h3>DETALLES DE LA VERIFICACIÓN</h3>
        <div class="info-row">
            <span class="label">Fecha de Verificación:</span>
            <span class="value">{{ $item->fecha_verificacion ? $item->fecha_verificacion->format('d/m/Y H:i') : 'No verificado' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Estado:</span>
            <span class="value"><strong style="color: #dc3545;">❌ NO LOCALIZADO</strong></span>
        </div>
        @if($item->comentarios)
        <div class="info-row">
            <span class="label">Razón de la Ausencia:</span>
            <span class="value">{{ $item->comentarios }}</span>
        </div>
        @endif
    </div>
    
    <!-- Sección de Acciones -->
    <div class="info-section">
        <h3>ACCIONES A REALIZAR</h3>
        <div class="acciones-box">
            <p>☐ Localizar el mobiliario en otras ubicaciones</p>
            <p>☐ Actualizar ubicación en el sistema</p>
            <p>☐ Investigar posible extravío o sustracción</p>
            <p>☐ Iniciar proceso administrativo según corresponda</p>
            <p>☐ Reportar a la Dirección General</p>
        </div>
    </div>
    
    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                <strong>AUDITOR</strong><br>
                {{ $auditoria->usuario->name }}<br>
                Encargado de Activo Fijo
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <strong>RESPONSABLE DEL ÁREA</strong><br>
                {{ $auditoria->responsable_nombre }}<br>
                {{ $auditoria->ubicacion->ubicacion_completa }}
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} — Área de Ingeniería Biomédica, Hospital Regional de Alta Especialidad "Dr. Ignacio Morones Prieto"</p>
        <p>Este documento es válido sin firma autógrafa conforme a las disposiciones vigentes</p>
        
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
            style="max-width: 100%; height: auto; margin-top: 10px; display: block; margin-left: auto; margin-right: auto;">
        @endif
    </div>
</body>
</html>
