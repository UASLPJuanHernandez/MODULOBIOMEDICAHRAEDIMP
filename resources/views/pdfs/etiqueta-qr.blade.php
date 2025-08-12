<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta QR - {{ $mobiliario->numero_control }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
            text-align: center;
        }
        .etiqueta {
            border: 2px solid #333;
            padding: 15px;
            width: 250px;
            margin: 0 auto;
            background-color: #fff;
        }
        .header {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c5aa0;
        }
        .codigo {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            background-color: #f8f9fa;
            padding: 5px;
            border: 1px solid #ddd;
        }
        .descripcion {
            font-size: 9px;
            margin: 8px 0;
            max-height: 40px;
            overflow: hidden;
            line-height: 1.2;
        }
        .qr-container {
            margin: 15px 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .info-adicional {
            font-size: 8px;
            color: #666;
            margin-top: 10px;
            line-height: 1.1;
        }
        .serie {
            font-size: 9px;
            margin: 5px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="etiqueta">
        <div class="header">INVENTARIO HOSPITALARIO</div>
        
        <div class="codigo">{{ $mobiliario->numero_control }}</div>
        
        <div class="descripcion">{{ $mobiliario->descripcion }}</div>
        
        @if($mobiliario->numero_serie)
        <div class="serie">S/N: {{ $mobiliario->numero_serie }}</div>
        @endif
        
        <div class="qr-container">
            {!! $qr_code !!}
        </div>
        
        <div class="info-adicional">
            <div>{{ $mobiliario->marca }} {{ $mobiliario->modelo }}</div>
            @if($mobiliario->localizacion)
            <div>{{ $mobiliario->localizacion->ubicacion_resumida }}</div>
            @endif
            <div>Generado: {{ $fecha }}</div>
        </div>
    </div>

    <!-- Etiqueta duplicada para impresión -->
    <div style="page-break-before: always;">
        <div class="etiqueta">
            <div class="header">INVENTARIO HOSPITALARIO</div>
            
            <div class="codigo">{{ $mobiliario->numero_control }}</div>
            
            <div class="descripcion">{{ $mobiliario->descripcion }}</div>
            
            @if($mobiliario->numero_serie)
            <div class="serie">S/N: {{ $mobiliario->numero_serie }}</div>
            @endif
            
            <div class="qr-container">
                {!! $qr_code !!}
            </div>
            
            <div class="info-adicional">
                <div>{{ $mobiliario->marca }} {{ $mobiliario->modelo }}</div>
                @if($mobiliario->localizacion)
                <div>{{ $mobiliario->localizacion->ubicacion_resumida }}</div>
                @endif
                <div>Generado: {{ $fecha }}</div>
            </div>
        </div>
    </div>
</body>
</html>
