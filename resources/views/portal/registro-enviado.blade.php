<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud enviada — HRAE</title>
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
            padding: 48px 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            text-align: center;
        }
        .icon {
            width: 64px;
            height: 64px;
            background: #f0fdf4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .icon svg { width: 32px; height: 32px; color: #16a34a; }
        h1 { font-size: 20px; font-weight: 700; color: #111; margin-bottom: 12px; }
        p { font-size: 14px; color: #6b7280; line-height: 1.6; }
        .back-link {
            display: inline-block;
            margin-top: 28px;
            font-size: 14px;
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1>Solicitud enviada</h1>
        <p>
            Tu registro fue recibido.<br>
            El departamento de Ingeniería Biomédica lo revisará y activará tu acceso en breve.
        </p>
        <a href="{{ route('portal.login') }}" class="back-link">Volver al inicio de sesión</a>
    </div>
</body>
</html>
