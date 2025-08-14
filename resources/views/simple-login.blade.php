<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Acceso - Sistema de Activo Fijo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            background: linear-gradient(135deg, #BC955C 0%, #a67f4a 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .logo-container {
            background: white;
            padding: 15px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .logo-circle {
            border: 2px solid #2E8B57;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #2E8B57;
        }
        
        .logo-text {
            font-size: 10px;
            color: #666;
            text-align: center;
            line-height: 1.1;
        }
        
        .login-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #BC955C;
            box-shadow: 0 0 0 3px rgba(188, 149, 92, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: #BC955C;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-btn:hover {
            background: #a67f4a;
        }
        
        .login-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #BC955C;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <div class="logo">
                    <div class="logo-circle">HR</div>
                    <div class="logo-text">
                        <div style="font-weight: bold;">HOSPITAL REGIONAL</div>
                        <div>DE ALTA ESPECIALIDAD</div>
                        <div style="font-weight: bold;">DR. IGNACIO MORONES PRIETO</div>
                        <div style="letter-spacing: 1px;">SAN LUIS POTOSÍ</div>
                    </div>
                </div>
            </div>
            <h1 style="font-size: 20px; margin-bottom: 5px;">Sistema de Activo Fijo</h1>
            <p style="opacity: 0.9; font-size: 14px;">Ingrese sus credenciales para acceder</p>
        </div>
        
        <div class="login-form">
            @if(session('error'))
                <div class="error-message">
                    {{ session('error') }}
                </div>
            @endif
            
            <form id="loginForm" method="POST" action="{{ route('simple.login.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Correo electrónico:</label>
                    <input type="email" id="email" name="email" value="{{ old('email', 'admin@inventario.hospital') }}" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Recordarme</label>
                </div>
                
                <button type="submit" class="login-btn" id="submitBtn">
                    Acceder al Sistema
                </button>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Verificando credenciales...</p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('loading').style.display = 'block';
        });
        
        // Auto-focus en el campo de password si el email ya está lleno
        window.onload = function() {
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            
            if (emailField.value) {
                passwordField.focus();
            } else {
                emailField.focus();
            }
        };
    </script>
</body>
</html>
