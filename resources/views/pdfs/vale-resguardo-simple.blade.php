<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Vale de Resguardo - {{ $vale->numero_vale }}</title>
    <style>
        @page {
            margin: 15px;
            size: A4;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .header img {
            max-width: 70%;
            height: auto;
            margin-bottom: 8px;
        }

        .header h1 {
            margin: 8px 0 4px 0;
            font-size: 16px;
            font-weight: bold;
        }

        .header h2 {
            margin: 4px 0;
            font-size: 12px;
            font-weight: normal;
        }

        .header h3 {
            margin: 4px 0 8px 0;
            font-size: 11px;
            font-weight: bold;
        }

        .info-row {
            margin: 6px 0;
            padding: 3px;
            border-bottom: 1px dotted #ccc;
        }

        .info-section {
            margin-bottom: 12px;
        }

        .info-section h3 {
            background-color: #f0f0f0;
            padding: 6px;
            margin: 10px 0 8px 0;
            border-left: 4px solid #333;
            font-size: 12px;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
        }

        .footer img {
            max-width: 70%;
            height: auto;
        }

        .signatures {
            margin-top: 25px;
            display: table;
            width: 100%;
        }

        .signature-section {
            display: table-cell;
            width: 50%;
            padding: 15px;
            text-align: center;
            vertical-align: top;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 4px;
            height: 30px;
        }

        .signature-info {
            font-size: 9px;
            margin-top: 4px;
        }

        .mobiliario-item {
            margin-bottom: 10px;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 3px;
            page-break-inside: avoid;
        }

        .mobiliario-item h4 {
            margin: 0 0 6px 0;
            color: #333;
            font-size: 11px;
        }

        .mobiliario-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .mobiliario-single {
            grid-column: span 2;
        }

        /* Estilos para máximo 4 mobiliarios en una página */
        .mobiliario-item .info-row {
            margin: 3px 0;
            padding: 2px;
            font-size: 9px;
        }

        .mobiliario-item .label {
            width: 100px;
            font-size: 9px;
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

        <h1>VALE DE RESGUARDO</h1>
        <h2>Área de Ingeniería Biomédica — Hospital Regional de Alta Especialidad "Dr. Ignacio Morones Prieto"</h2>
        <h3>N° {{ $vale->numero_vale }}</h3>
    </div>

    <div class="info-section">
        <h3>INFORMACIÓN DEL VALE</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div class="info-row">
                <span class="label">Tipo de Vale:</span>
                <span>{{ $vale->tipo_vale_formateado ?? $vale->tipo_vale ?? 'N/A' }}</span>&ensp;
                &nbsp;&nbsp;<span class="label">Fecha de Generación:</span>
                <span>{{ $vale->fecha_generacion ? $vale->fecha_generacion->format('d/m/Y H:i') : 'N/A' }}</span>
            </div>

        </div>
    </div>

    <div class="info-section">
        <h3>INFORMACIÓN DEL MOBILIARIO</h3>

        @if(isset($mobiliarios) && $mobiliarios->count() > 0)
        @php
        $cantidad = $mobiliarios->count();
        $usarGrid = $cantidad > 1 && $cantidad <= 4;
            @endphp

            <div class="{{ $usarGrid ? 'mobiliario-grid' : '' }}">
            @foreach($mobiliarios as $index => $mobiliario)
            <div class="mobiliario-item {{ $cantidad == 1 ? 'mobiliario-single' : '' }}">
                <h4>{{ $cantidad > 1 ? 'Mobiliario ' . ($index + 1) : 'Mobiliario' }}</h4>

                <div class="info-row">
                    <span class="label">No. Inventario:</span>
                    <span>{{ $mobiliario->numero_control ?? 'N/A' }}</span>
                    &nbsp;&nbsp;<span class="label">Descripción:</span>
                    <span>{{ $mobiliario->descripcion ?? 'N/A' }}</span>
                    &nbsp;&nbsp;<span class="label">Marca:</span>
                    <span>{{ $mobiliario->marca ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Modelo:</span>
                    <span>{{ $mobiliario->modelo ?? 'N/A' }}</span>
                    &nbsp;&nbsp;<span class="label">No. Serie:</span>
                    <span>{{ $mobiliario->numero_serie ?? 'N/A' }}</span>
                </div>
            </div>
            @endforeach
    </div>
    @elseif(isset($mobiliario))
    <div class="mobiliario-item mobiliario-single">
        <div class="info-row">
            <span class="label">No. Inventario:</span>
            <span>{{ $mobiliario->numero_control ?? 'N/A' }}</span>
            &nbsp;&nbsp;<span class="label">Descripción:</span>
            <span>{{ $mobiliario->descripcion ?? 'N/A' }}</span>
            &nbsp;&nbsp;<span class="label">Marca:</span>
            <span>{{ $mobiliario->marca ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Modelo:</span>
            <span>{{ $mobiliario->modelo ?? 'N/A' }}</span>
            &nbsp;&nbsp;<span class="label">No. Serie:</span>
            <span>{{ $mobiliario->numero_serie ?? 'N/A' }}</span>
        </div>
    </div>
    @else
    <div class="info-row">
        <span>No hay mobiliario asociado</span>
    </div>
    @endif
    </div>

    <div class="info-section">
        <h3>RESPONSABLES</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div>
                <div class="info-row">
                    <span class="label">Entrega:</span>
                    <span>{{ $vale->responsable_entrega ?? 'N/A' }}</span>
                      &nbsp;&nbsp;<span class="label">Matrícula:</span>
                    <span>{{ $vale->matricula_entrega ?? 'N/A' }}</span>
                </div>
         </div>

            <div>
                <div class="info-row">
                    <span class="label">Recibe:</span>
                    <span>{{ $vale->responsable_recibe ?? 'N/A' }}</span>
                     &nbsp;&nbsp;<span class="label">Matrícula:</span>
                    <span>{{ $vale->matricula_recibe ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($vale->observaciones)
    <div class="info-section">
        <h3>OBSERVACIONES</h3>
        <p>{{ $vale->observaciones }}</p>
    </div>
    @endif

    <div class="signatures">
        <div class="signature-section">
            <div class="signature-line"></div>
            <div class="signature-info">
                <strong>ENTREGA</strong><br>
                {{ $vale->responsable_entrega ?? 'N/A' }}<br>
                @if($vale->matricula_entrega)
                Matrícula: {{ $vale->matricula_entrega }}
                @endif
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-line"></div>
            <div class="signature-info">
                <strong>RECIBE</strong><br>
                {{ $vale->responsable_recibe ?? 'N/A' }}<br>
                @if($vale->matricula_recibe)
                Matrícula: {{ $vale->matricula_recibe }}
                @endif
            </div>
        </div>
    </div>

    <div style="margin-top: 20px; text-align: center; font-size: 8px;">
        <p>Documento generado el {{ $fecha }}</p>
    </div>

    <div class="footer">
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
            style="max-width:100%; height: auto; margin-top: 10px; display: block; margin-left: auto; margin-right: auto;">
        @endif
    </div>
</body>

</html>