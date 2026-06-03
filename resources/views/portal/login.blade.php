<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Reportes — HRAE</title>
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
            max-width: 400px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111;
        }
        .logo p {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            color: #111;
            outline: none;
            transition: border-color 0.15s;
        }
        input:focus { border-color: #2563eb; }
        .error {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
        }
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #b91c1c;
            margin-bottom: 16px;
        }
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
            margin-top: 8px;
            transition: background 0.15s;
        }
        .btn:hover { background: #1e40af; }
        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #6b7280;
        }
        .footer-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
        .footer-link a:hover { text-decoration: underline; }
        .divider {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <h1>Portal de Reportes</h1>
            <p>Ingeniería Biomédica — HRAE</p>
        </div>

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('portal.login.submit') }}">
            @csrf
            <div class="form-group">
                <label for="numero_empleado">Número de empleado</label>
                <input type="text" id="numero_empleado" name="numero_empleado"
                       value="{{ old('numero_empleado') }}" autocomplete="username" autofocus>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" autocomplete="current-password">
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>

    </div>
</body>
</html>
