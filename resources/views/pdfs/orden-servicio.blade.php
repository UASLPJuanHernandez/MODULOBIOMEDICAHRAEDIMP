<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Servicio - {{ $orden->numero_orden }}</title>
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
        .priority-alta, .priority-critica {
            color: #e74c3c;
            font-weight: bold;
        }
        .priority-media {
            color: #f39c12;
            font-weight: bold;
        }
        .priority-baja {
            color: #27ae60;
            font-weight: bold;
        }
        .estado-pendiente {
            color: #f39c12;
        }
        .estado-en_proceso {
            color: #3498db;
        }
        .estado-completado {
            color: #27ae60;
        }
        .estado-cancelado {
            color: #e74c3c;
        }
        .problema-section {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #e74c3c;
            margin: 15px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .signature-area {
            margin-top: 40px;
            border: 1px solid #ccc;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SISTEMA DE INVENTARIO HOSPITALARIO</div>
        <div class="title">ORDEN DE SERVICIO</div>
        <div>No. {{ $orden->numero_orden }}</div>
    </div>

    <div class="info-section">
        <h3>INFORMACIÓN DEL EQUIPO</h3>
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
            <span class="value">{{ $mobiliario->numero_serie }}</span>
        </div>
        <div class="info-row">
            <span class="label">Ubicación:</span>
            <span class="value">{{ $mobiliario->localizacion->ubicacion_resumida ?? 'No especificada' }}</span>
        </div>
    </div>

    <div class="info-section">
        <h3>DETALLES DE LA ORDEN</h3>
        <div class="info-row">
            <span class="label">Tipo de Servicio:</span>
            <span class="value">{{ ucfirst(str_replace('_', ' ', $orden->tipo_servicio)) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Prioridad:</span>
            <span class="value priority-{{ $orden->prioridad }}">{{ ucfirst($orden->prioridad) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Estado:</span>
            <span class="value estado-{{ $orden->estado }}">{{ ucfirst(str_replace('_', ' ', $orden->estado)) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Fecha de Solicitud:</span>
            <span class="value">{{ $orden->fecha_solicitud->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Fecha Programada:</span>
            <span class="value">{{ $orden->fecha_programada->format('d/m/Y') }}</span>
        </div>
        @if($orden->fecha_inicio)
        <div class="info-row">
            <span class="label">Fecha de Inicio:</span>
            <span class="value">{{ $orden->fecha_inicio->format('d/m/Y') }}</span>
        </div>
        @endif
        @if($orden->fecha_finalizacion)
        <div class="info-row">
            <span class="label">Fecha de Finalización:</span>
            <span class="value">{{ $orden->fecha_finalizacion->format('d/m/Y') }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="label">Solicitante:</span>
            <span class="value">{{ $orden->usuario->name }}</span>
        </div>
        @if($orden->tecnico_asignado)
        <div class="info-row">
            <span class="label">Técnico Asignado:</span>
            <span class="value">{{ $orden->tecnico_asignado }}</span>
        </div>
        @endif
    </div>

    <div class="problema-section">
        <h3>PROBLEMA DETECTADO</h3>
        <p>{{ $orden->problema_detectado }}</p>
    </div>

    @if($orden->observaciones)
    <div class="info-section">
        <h3>OBSERVACIONES</h3>
        <p>{{ $orden->observaciones }}</p>
    </div>
    @endif

    <div class="info-section">
        <h3>COSTOS</h3>
        @if($orden->costo_estimado)
        <div class="info-row">
            <span class="label">Costo Estimado:</span>
            <span class="value">${{ number_format($orden->costo_estimado, 2) }}</span>
        </div>
        @endif
        @if($orden->costo_real)
        <div class="info-row">
            <span class="label">Costo Real:</span>
            <span class="value">${{ number_format($orden->costo_real, 2) }}</span>
        </div>
        @endif
    </div>

    <div class="signature-area">
        <p><strong>ÁREA DE FIRMAS Y OBSERVACIONES DEL TÉCNICO</strong></p>
        <br><br><br>
        <p>_______________________________</p>
        <p>Firma del Técnico</p>
        <br>
        <p>Fecha de Servicio: _______________</p>
    </div>

    <div class="footer">
        <p>Documento generado el {{ $fecha }} — Área de Ingeniería Biomédica, Hospital Regional de Alta Especialidad "Dr. Ignacio Morones Prieto"</p>
        <p>Orden de Servicio No. {{ $orden->numero_orden }} - Versión {{ $orden->version }}</p>
    </div>
</body>
</html>
