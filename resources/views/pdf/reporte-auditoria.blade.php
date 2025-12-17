<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Auditoría - {{ $auditoria->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 11px;
            line-height: 1.3;
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
        
        .subtitle {
            font-size: 12px;
            color: #666;
            margin: 5px 0;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #495057;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label, .info-value {
            display: table-cell;
            padding: 5px;
        }
        
        .info-label {
            font-weight: bold;
            width: 30%;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 2px solid #dee2e6;
            background-color: #fff;
        }
        
        .stat-box:not(:last-child) {
            border-right: none;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .stat-box.total { background-color: #e7f3ff; }
        .stat-box.total .stat-number { color: #0056b3; }
        
        .stat-box.presente { background-color: #d4edda; }
        .stat-box.presente .stat-number { color: #28a745; }
        
        .stat-box.ausente { background-color: #f8d7da; }
        .stat-box.ausente .stat-number { color: #dc3545; }
        
        .stat-box.vales { background-color: #fff3cd; }
        .stat-box.vales .stat-number { color: #ffc107; }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 14px;
            background-color: #343a40;
            color: #fff;
            padding: 10px;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table thead {
            background-color: #6c757d;
            color: #fff;
        }
        
        table th, table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        table th {
            font-weight: bold;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        
        .badge-success {
            background-color: #28a745;
            color: #fff;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: #fff;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        
        .signatures {
            margin-top: 60px;
            page-break-inside: avoid;
        }
        
        .signature-container {
            display: table;
            width: 100%;
        }
        
        .signature {
            display: table-cell;
            text-align: center;
            width: 50%;
            padding: 0 30px;
        }
        
        .signature-line {
            border-top: 2px solid #000;
            margin-bottom: 10px;
            height: 70px;
        }
        
        .signature strong {
            font-size: 12px;
        }
        
        .signature .details {
            font-size: 10px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @media print {
            .no-print { display: none; }
            body { padding: 15px; }
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
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

        <div class="title">REPORTE DE AUDITORÍA DE MOBILIARIO Y EQUIPO</div>
        <div class="subtitle">{{ $auditoria->ubicacion->ubicacion_completa }}</div>
        <div class="subtitle">Auditoría No. {{ $auditoria->id }} | {{ $auditoria->fecha_inicio->format('d/m/Y') }}</div>
    </div>
    
    <!-- Información General -->
    <div class="info-box">
        <h3>📋 INFORMACIÓN GENERAL DE LA AUDITORÍA</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Ubicación Auditada:</div>
                <div class="info-value">{{ $auditoria->ubicacion->ubicacion_completa }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Responsable del Área:</div>
                <div class="info-value">{{ $auditoria->responsable_nombre }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Auditor:</div>
                <div class="info-value">{{ $auditoria->usuario->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Inicio:</div>
                <div class="info-value">{{ $auditoria->fecha_inicio->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Finalización:</div>
                <div class="info-value">{{ $auditoria->fecha_fin ? $auditoria->fecha_fin->format('d/m/Y H:i') : 'En progreso' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Duración:</div>
                <div class="info-value">
                    @if($auditoria->fecha_fin)
                        {{ $auditoria->fecha_inicio->diffInHours($auditoria->fecha_fin) }} horas {{ $auditoria->fecha_inicio->diffInMinutes($auditoria->fecha_fin) % 60 }} minutos
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
        
        @if($auditoria->observaciones_generales)
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dee2e6;">
            <strong>Observaciones Generales:</strong>
            <p style="margin: 5px 0 0 0;">{{ $auditoria->observaciones_generales }}</p>
        </div>
        @endif
    </div>
    
    <!-- Estadísticas -->
    <div class="section">
        <div class="section-title">📊 RESUMEN ESTADÍSTICO</div>
        <div class="stats-grid">
            <div class="stat-box total">
                <div class="stat-number">{{ $auditoria->total_mobiliarios }}</div>
                <div class="stat-label">Total Verificado</div>
            </div>
            <div class="stat-box presente">
                <div class="stat-number">{{ $auditoria->mobiliarios_presentes }}</div>
                <div class="stat-label">Presentes</div>
            </div>
            <div class="stat-box ausente">
                <div class="stat-number">{{ $auditoria->mobiliarios_ausentes }}</div>
                <div class="stat-label">Ausentes</div>
            </div>
            <div class="stat-box vales">
                <div class="stat-number">{{ $auditoria->vales_generados }}</div>
                <div class="stat-label">Vales Generados</div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 10px;">
            <strong>Porcentaje de Cumplimiento: </strong>
            <span style="font-size: 18px; color: {{ $auditoria->total_mobiliarios > 0 && ($auditoria->mobiliarios_presentes / $auditoria->total_mobiliarios * 100) >= 95 ? '#28a745' : '#dc3545' }}">
                {{ $auditoria->total_mobiliarios > 0 ? number_format($auditoria->mobiliarios_presentes / $auditoria->total_mobiliarios * 100, 2) : 0 }}%
            </span>
        </div>
    </div>
    
    <!-- Mobiliario Presente -->
    @if($auditoria->mobiliarios_presentes > 0)
    <div class="section">
        <div class="section-title">✅ MOBILIARIO ENCONTRADO ({{ $auditoria->mobiliarios_presentes }})</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">No. Control</th>
                    <th style="width: 15%;">No. Inventario</th>
                    <th style="width: 35%;">Descripción</th>
                    <th style="width: 15%;">Marca/Modelo</th>
                    <th style="width: 20%;">Comentarios</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auditoria->itemsPresentes as $item)
                <tr>
                    <td>{{ $item->mobiliario->numero_control }}</td>
                    <td>{{ $item->mobiliario->numero_inventario }}</td>
                    <td>{{ $item->mobiliario->descripcion }}</td>
                    <td>{{ $item->mobiliario->marca }} {{ $item->mobiliario->modelo }}</td>
                    <td>{{ $item->comentarios ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    
    <!-- Mobiliario Ausente -->
    @if($auditoria->mobiliarios_ausentes > 0)
    <div class="section page-break">
        <div class="section-title">❌ MOBILIARIO NO LOCALIZADO ({{ $auditoria->mobiliarios_ausentes }})</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">No. Control</th>
                    <th style="width: 12%;">No. Inventario</th>
                    <th style="width: 28%;">Descripción</th>
                    <th style="width: 12%;">Marca/Modelo</th>
                    <th style="width: 15%;">Folio Vale</th>
                    <th style="width: 21%;">Comentarios</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auditoria->itemsAusentes as $item)
                <tr>
                    <td>{{ $item->mobiliario->numero_control }}</td>
                    <td>{{ $item->mobiliario->numero_inventario }}</td>
                    <td>{{ $item->mobiliario->descripcion }}</td>
                    <td>{{ $item->mobiliario->marca }} {{ $item->mobiliario->modelo }}</td>
                    <td>
                        @if($item->folio_vale)
                            <span class="badge badge-warning">{{ $item->folio_vale }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $item->comentarios ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="background-color: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin-top: 10px;">
            <strong>⚠️ NOTA IMPORTANTE:</strong> 
            Los mobiliarios no localizados requieren seguimiento inmediato. Se deben tomar las acciones administrativas correspondientes.
        </div>
    </div>
    @endif
    
    <!-- Conclusiones -->
    <div class="section">
        <div class="section-title">📝 CONCLUSIONES Y RECOMENDACIONES</div>
        <div style="padding: 15px; background-color: #fff; border: 1px solid #dee2e6;">
            @if($auditoria->mobiliarios_ausentes == 0)
                <p style="margin: 0; color: #28a745;">
                    <strong>✓</strong> La auditoría se completó satisfactoriamente. Todo el mobiliario registrado fue localizado en la ubicación asignada.
                </p>
            @else
                <p style="margin: 0 0 10px 0; color: #dc3545;">
                    <strong>⚠️</strong> Se detectaron {{ $auditoria->mobiliarios_ausentes }} mobiliario(s) no localizado(s) que requieren atención inmediata.
                </p>
                <p style="margin: 0;"><strong>Recomendaciones:</strong></p>
                <ul style="margin: 5px 0 0 0; padding-left: 20px;">
                    <li>Realizar búsqueda exhaustiva en ubicaciones cercanas</li>
                    <li>Verificar últimos movimientos registrados en el sistema</li>
                    <li>Entrevistar al personal del área</li>
                    <li>Iniciar proceso administrativo según normativa vigente</li>
                    <li>Actualizar registros en el sistema una vez localizado</li>
                </ul>
            @endif
        </div>
    </div>
    
    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-container">
            <div class="signature">
                <div class="signature-line"></div>
                <div><strong>AUDITOR</strong></div>
                <div>{{ $auditoria->usuario->name }}</div>
                <div class="details">Encargado de Activo Fijo</div>
                <div class="details">Fecha: {{ now()->format('d/m/Y') }}</div>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <div><strong>RESPONSABLE DEL ÁREA</strong></div>
                <div>{{ $auditoria->responsable_nombre }}</div>
                <div class="details">{{ $auditoria->ubicacion->ubicacion_completa }}</div>
                <div class="details">Fecha: {{ now()->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} por el Sistema de Activo Fijo del Hospital Regional de Alta Especialidad "Dr. Ignacio Morones Prieto"</p>
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
