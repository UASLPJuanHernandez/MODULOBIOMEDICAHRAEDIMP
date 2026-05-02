<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar falla — HRAE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .header {
            margin-bottom: 28px;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111;
        }
        .header .saludo {
            font-size: 13px;
            color: #6b7280;
            margin-top: 6px;
        }
        .header .saludo strong { color: #374151; }
        textarea {
            width: 100%;
            padding: 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            color: #111;
            resize: vertical;
            min-height: 140px;
            outline: none;
            font-family: inherit;
            line-height: 1.6;
            transition: border-color 0.15s;
        }
        textarea:focus { border-color: #2563eb; }
        textarea::placeholder { color: #9ca3af; }
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #b91c1c;
            margin-bottom: 14px;
        }
        .success-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 16px;
            font-size: 14px;
            color: #15803d;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success-box svg { width: 20px; height: 20px; flex-shrink: 0; }
        .btn {
            width: 100%;
            padding: 12px;
            background: #1d4ed8;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 14px;
            transition: background 0.15s;
        }
        .btn:hover { background: #1e40af; }
        .logout-bar {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .logout-btn {
            background: none;
            border: none;
            font-size: 12px;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px 0;
        }
        .logout-btn:hover { color: #6b7280; text-decoration: underline; }
        .char-count {
            font-size: 11px;
            color: #9ca3af;
            text-align: right;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    @php $usuario = Auth::guard('personal')->user(); @endphp


    {{-- Botón firmas pendientes (solo Jefes de Servicio) --}}
    @if($usuario->es_jefe_servicio)
    @php
        $firmasPendientes = \App\Models\Registro::where('jefe_id', $usuario->id)
            ->where('estado', 'en_firma')
            ->count()
            + \App\Models\FirmaSolicitud::where('personal_reportante_id', $usuario->id)
            ->where('estado', 'pendiente')
            ->count();
    @endphp
    <div style="position:fixed;top:20px;left:20px;z-index:100;">
        <a href="{{ route('portal.firmas') }}"
           style="display:inline-flex;align-items:center;gap:8px;background:white;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;color:#374151;text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,0.08);transition:box-shadow 0.15s;"
           onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.13)'"
           onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'">
            <svg width="16" height="16" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 0l.172.172a2 2 0 010 2.828L12 16H9v-3z"/>
            </svg>
            Documentos
            @if($firmasPendientes > 0)
            <span style="background:#ef4444;color:white;font-size:11px;font-weight:700;padding:2px 7px;border-radius:999px;min-width:20px;text-align:center;">
                {{ $firmasPendientes }}
            </span>
            @endif
        </a>
    </div>
    @endif

    <div class="card">
        <div class="header">
            <h1>Reportar falla de equipo</h1>
            <p class="saludo">
                Hola, <strong>{{ $usuario->nombre }}</strong>
                &mdash; {{ $usuario->servicio }}
            </p>
        </div>

        @if(session('enviado'))
            <div class="success-box">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Reporte enviado. El departamento de Ingeniería Biomédica ya lo recibió.
            </div>
        @endif

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('portal.reportes.enviar') }}">
            @csrf
            <textarea
                name="descripcion"
                id="descripcion"
                placeholder="Describe la falla del equipo: qué equipo es, dónde está y qué le pasa..."
                maxlength="1000"
                oninput="document.getElementById('chars').textContent = this.value.length + '/1000'"
            >{{ old('descripcion') }}</textarea>
            <p class="char-count" id="chars">0/1000</p>
            <button type="submit" class="btn">Enviar reporte</button>
        </form>

        <div class="logout-bar">
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit" class="logout-btn">Cerrar sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
