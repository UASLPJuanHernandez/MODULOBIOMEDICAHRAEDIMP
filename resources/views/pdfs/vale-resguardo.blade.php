<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vale de Resguardo - {{ $vale->numero_vale }}</title>
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
        .info-section {
            margin: 15px 0;
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
            min-width: 150px;
        }
        .value {
            flex: 1;
            text-align: right;
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
        .qr-section {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SISTEMA DE ACTIVO FIJO</div>
        <div class="title">VALE DE RESGUARDO</div>
        <div>No. {{ $vale->numero_vale }}</div>
    </div>

    <div class="info-section">
        <h3>INFORMACIÓN DEL MOBILIARIO</h3>
        <div class="info-row">
            <span class="label">Código Institucional:</span>
            <span class="value">{{ $mobiliario->numero_control }}</span>
        </div>
        <div class="info-row">
            <span class="label">Descripción:</span>
            <span class="value">{{ $mobiliario->descripcion }}</span>
        </div>
        <div class="info-row">
            <span class="label">Marca:</span>
            <span class="value">{{ $mobiliario->marca }}</span>
        </div>
        <div class="info-row">
            <span class="label">Modelo:</span>
            <span class="value">{{ $mobiliario->modelo }}</span>
        </div>
        <div class="info-row">
            <span class="label">Serie:</span>
            <span class="value">{{ $mobiliario->numero_serie ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <h3>INFORMACIÓN DEL VALE</h3>
        <div class="info-row">
            <span class="label">Tipo de Vale:</span>
            <span class="value">{{ ucfirst($vale->tipo_vale) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Fecha de Generación:</span>
            <span class="value">{{ $vale->fecha_generacion->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Tipo de Vale:</span>
            <span class="value">{{ $vale->tipo_vale_formateado }}</span>
        </div>
    </div>

    @if($vale->observaciones)
    <div class="info-section">
        <h3>OBSERVACIONES</h3>
        <p>{{ $vale->observaciones }}</p>
    </div>
    @endif

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                <strong>ENTREGA</strong><br>
                {{ $vale->responsable_entrega }}<br>
                {{ $vale->cargo_entrega ?? 'Responsable de Entrega' }}
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <strong>RECIBE</strong><br>
                {{ $vale->responsable_recibe }}<br>
                Responsable de Recepción
            </div>
        </div>
    </div>

    @if($mobiliario->qr_code)
    <div class="qr-section">
        <p><strong>Código QR del Mobiliario:</strong></p>
        <div style="text-align: center;">
            {!! $mobiliario->qr_code !!}
        </div>
    </div>
    @endif

    <div class="footer">
        <p>Documento generado el {{ $fecha }} — Área de Ingeniería Biomédica, Hospital Regional de Alta Especialidad "Dr. Ignacio Morones Prieto"</p>
        <p>Este documento es válido sin firma autógrafa conforme a las disposiciones vigentes</p>
    </div>
</body>
</html>
