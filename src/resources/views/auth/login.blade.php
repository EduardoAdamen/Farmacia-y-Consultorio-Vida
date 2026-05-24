<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=DM+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background: #0F172A;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
        .login-icon {
            width: 52px; height: 52px;
            background: #F1F5F9;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            color: #0D9488;
        }
        .login-title {
            font-family: 'Outfit', sans-serif;
            font-size: 22px; font-weight: 700;
            color: #0F172A; text-align: center; margin-bottom: 4px;
        }
        .login-subtitle {
            font-size: 13px; color: #64748B;
            text-align: center; margin-bottom: 28px;
        }
        .form-label { font-size: 13px; font-weight: 600; color: #0F172A; }
        .form-control {
            border: 1px solid #E2E8F0; border-radius: 8px;
            padding: 10px 14px; font-size: 14px;
        }
        .form-control:focus {
            border-color: #0D9488;
            box-shadow: 0 0 0 3px rgba(13,148,136,0.15);
        }
        .btn-login {
            background: #0F172A; color: #fff;
            border-radius: 8px; padding: 11px; width: 100%;
            font-weight: 700; font-size: 14px; border: none;
            transition: background 0.2s; cursor: pointer;
        }
        .btn-login:hover { background: #0D9488; color: #fff; }
        .sesion-segura {
            text-align: center; font-size: 12px; color: #64748B;
            margin-top: 16px;
            display: flex; align-items: center; justify-content: center; gap: 4px;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-icon">
        <i data-lucide="cross" style="width:28px;height:28px;"></i>
    </div>
    <div class="login-title">Farmacia y Consultorio Vida</div>
   

    @if(session('success'))
        <div class="alert alert-success py-2 px-3 mb-3" style="font-size:13px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="username" class="form-control"
                   placeholder="Ingresa tu usuario"
                   value="{{ old('username') }}"
                   autocomplete="username" autofocus required>
        </div>
        <div class="mb-4">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control"
                   placeholder="Ingresa tu contraseña"
                   autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn-login">Iniciar Sesión</button>
    </form>
    
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
