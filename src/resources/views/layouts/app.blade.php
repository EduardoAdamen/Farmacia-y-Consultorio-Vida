<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — Farmacia y Consultorio Vida</title>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700;1,9..40,400&family=DM+Mono:wght@500&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --color-primary:    #0F172A;
            --color-secondary:  #F1F5F9;
            --color-accent:     #0D9488;
            --color-accent-dk:  #0b7a72;
            --color-info:       #0EA5E9;
            --color-warning:    #F59E0B;
            --color-danger:     #F43F5E;
            --color-success:    #22C55E;
            --color-surface:    #FFFFFF;
            --color-text-main:  #0F172A;
            --color-text-muted: #64748B;
            --color-border:     #E2E8F0;
            --sidebar-width:    240px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            background: var(--color-secondary);
            color: var(--color-text-main);
            margin: 0;
        }

        h1,h2,h3,h4,h5,h6,.fw-bold,.brand-name { font-family: 'Outfit', sans-serif; }
        code, .folio { font-family: 'DM Mono', monospace; font-size: 12px; }

        /* ── Sidebar ─────────────────────────────────────────────── */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--color-primary);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        #sidebar .brand {
            padding: 20px 16px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        #sidebar .brand-name {
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.3px;
        }

        #sidebar .brand-icon {
            width: 34px; height: 34px;
            background: var(--color-accent);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .nav-section {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.35);
            padding: 20px 20px 6px;
            font-weight: 600;
        }

        .nav-link {
            color: rgba(255,255,255,0.65);
            padding: 9px 16px;
            border-radius: 8px;
            margin: 1px 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.15s ease;
            font-size: 13.5px;
            text-decoration: none;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.07);
            color: #fff;
        }

        .nav-link.active {
            background: var(--color-accent);
            color: #fff;
            box-shadow: 0 2px 8px rgba(13,148,136,0.35);
        }

        .nav-link i { flex-shrink: 0; }

        /* ── Topbar ──────────────────────────────────────────────── */
        #topbar {
            margin-left: var(--sidebar-width);
            background: var(--color-surface);
            border-bottom: 1px solid var(--color-border);
            padding: 0 24px;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .topbar-right { display: flex; align-items: center; gap: 12px; }

        .avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--color-accent);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            font-family: 'Outfit', sans-serif;
            flex-shrink: 0;
        }

        .topbar-icon-btn {
            background: none;
            border: none;
            color: var(--color-text-muted);
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            transition: background 0.15s, color 0.15s;
            text-decoration: none;
        }

        .topbar-icon-btn:hover {
            background: var(--color-secondary);
            color: var(--color-text-main);
        }

        /* ── Main Content ────────────────────────────────────────── */
        #main-content {
            margin-left: var(--sidebar-width);
            padding: 28px 28px 40px;
            min-height: calc(100vh - 56px);
        }

        /* ── Cards ───────────────────────────────────────────────── */
        .card {
            border: 1px solid var(--color-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            background: var(--color-surface);
        }

        .card-header {
            background: var(--color-surface);
            border-bottom: 1px solid var(--color-border);
            border-radius: 12px 12px 0 0 !important;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 14px;
        }

        /* ── Botones ─────────────────────────────────────────────── */
        .btn-accent {
            background: var(--color-accent);
            color: #fff;
            border: none;
            font-weight: 600;
        }

        .btn-accent:hover {
            background: var(--color-accent-dk);
            color: #fff;
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid var(--color-border);
            color: var(--color-text-muted);
            font-size: 13px;
        }

        .btn-ghost:hover {
            background: var(--color-secondary);
            color: var(--color-text-main);
            border-color: #CBD5E1;
        }

        /* ── Badges ──────────────────────────────────────────────── */
        .badge-activo     { background: #DCFCE7; color: #166534; font-weight: 600; }
        .badge-inactivo   { background: #F1F5F9; color: #64748B; font-weight: 600; }
        .badge-pendiente  { background: #FEF9C3; color: #854D0E; font-weight: 600; }
        .badge-recibido   { background: #E0F2FE; color: #075985; font-weight: 600; }
        .badge-pagado     { background: #DCFCE7; color: #166534; font-weight: 600; }
        .badge-cancelado  { background: #FEE2E2; color: #991B1B; font-weight: 600; }
        .badge-critico    { background: #FEE2E2; color: #991B1B; font-weight: 600; }
        .badge-dueno      { background: #EDE9FE; color: #5B21B6; font-weight: 600; }
        .badge-vendedor   { background: #E0F2FE; color: #075985; font-weight: 600; }
        .badge-medico     { background: #DCFCE7; color: #166534; font-weight: 600; }

        /* ── Tablas ──────────────────────────────────────────────── */
        .table th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--color-text-muted);
            font-weight: 600;
            background: #FAFAFA;
            border-bottom: 1px solid var(--color-border);
        }

        .table td { vertical-align: middle; font-size: 13.5px; }

        /* ── Alertas Flash ───────────────────────────────────────── */
        .alert-success-custom {
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            color: #166534;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-danger-custom {
            background: #FFF1F2;
            border: 1px solid #FECDD3;
            color: #9F1239;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Formularios ─────────────────────────────────────────── */
        .form-label { font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; }

        .form-control, .form-select {
            border: 1px solid var(--color-border);
            border-radius: 8px;
            padding: 9px 13px;
            font-size: 14px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px rgba(13,148,136,0.12);
            outline: none;
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: var(--color-danger);
        }

        .invalid-feedback { font-size: 12px; }

        /* ── Alerta de alerta en topbar ──────────────────────────── */
        .alerta-badge {
            display: flex;
            align-items: center;
            gap: 5px;
            background: #FEF2F2;
            color: var(--color-danger);
            border: 1px solid #FECACA;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
<nav id="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i data-lucide="cross" style="width:18px;height:18px;color:#fff;"></i>
        </div>
        <span class="brand-name">Farmacia y Consultorio Vida</span>
    </div>

    <div class="mt-1 pb-4 flex-grow-1">
        <a href="{{ route('panel-inicio') }}" class="nav-link {{ request()->routeIs('panel-inicio') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard" style="width:18px;height:18px;"></i> Panel de Inicio
        </a>

        @if(in_array(auth()->user()->rol, ['dueno', 'vendedor']))
        <a href="{{ Route::has('ventas.index') ? route('ventas.index') : '#' }}" class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}">
            <i data-lucide="shopping-cart" style="width:18px;height:18px;"></i> Ventas
        </a>
        <a href="{{ Route::has('productos.index') ? route('productos.index') : '#' }}" class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}">
            <i data-lucide="package" style="width:18px;height:18px;"></i> Inventario
        </a>
        <a href="{{ Route::has('categorias.index') ? route('categorias.index') : '#' }}" class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
            <i data-lucide="tags" style="width:18px;height:18px;"></i> Categorías
        </a>
        @endif
        
        @if(auth()->user()->rol === 'dueno')
        <a href="{{ Route::has('proveedores.index') ? route('proveedores.index') : '#' }}" class="nav-link {{ request()->routeIs('proveedores.*') ? 'active' : '' }}">
            <i data-lucide="truck" style="width:18px;height:18px;"></i> Proveedores
        </a>
        <a href="{{ Route::has('pedidos.index') ? route('pedidos.index') : '#' }}" class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}">
            <i data-lucide="clipboard-list" style="width:18px;height:18px;"></i> Pedidos
        </a>
        @endif

        @if(in_array(auth()->user()->rol, ['dueno', 'medico', 'vendedor']))
        <a href="{{ Route::has('citas.index') ? route('citas.index') : '#' }}" class="nav-link {{ request()->routeIs('citas.*') ? 'active' : '' }}">
            <i data-lucide="calendar-days" style="width:18px;height:18px;"></i> Agenda
        </a>
        @endif

        @if(in_array(auth()->user()->rol, ['dueno', 'medico']))
        <a href="{{ Route::has('expedientes.index') ? route('expedientes.index') : '#' }}" class="nav-link {{ request()->routeIs('expedientes.*') ? 'active' : '' }}">
            <i data-lucide="folder-open" style="width:18px;height:18px;"></i> Expedientes
        </a>
        @endif

        @if(auth()->user()->rol === 'dueno')
        <a href="{{ Route::has('usuarios.index') ? route('usuarios.index') : '#' }}" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            <i data-lucide="users" style="width:18px;height:18px;"></i> Usuarios
        </a>
        <a href="{{ Route::has('reportes.index') ? route('reportes.index') : '#' }}" class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
            <i data-lucide="bar-chart-2" style="width:18px;height:18px;"></i> Reportes
        </a>
        @endif
    </div>
</nav>

{{-- TOPBAR --}}
<div id="topbar">
    <div class="topbar-right">
        @php $totalAlertas = isset($totalAlertas) ? $totalAlertas : 0; @endphp
        @if($totalAlertas > 0)
        <span class="alerta-badge">
            <i data-lucide="alert-triangle" style="width:14px;height:14px;"></i>
            {{ $totalAlertas }} {{ $totalAlertas === 1 ? 'alerta' : 'alertas' }}
        </span>
        @endif

        <div class="d-flex align-items-center gap-2">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->nombre_completo, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:13px;font-weight:600;line-height:1.2;">{{ auth()->user()->nombre_completo }}</div>
                <div style="font-size:11px;color:var(--color-text-muted);">{{ ucfirst(auth()->user()->rol) }}</div>
            </div>
        </div>

        <a href="{{ route('perfil.password') }}" class="topbar-icon-btn" title="Cambiar contraseña">
            <i data-lucide="settings" style="width:18px;height:18px;"></i>
        </a>

        <form action="{{ route('logout') }}" method="POST" class="mb-0">
            @csrf
            <button type="submit" class="topbar-icon-btn" title="Cerrar sesión">
                <i data-lucide="log-out" style="width:18px;height:18px;"></i>
            </button>
        </form>
    </div>
</div>

{{-- CONTENIDO PRINCIPAL --}}
<main id="main-content">
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '¡Éxito!',
                    text: "{!! addslashes(session('success')) !!}",
                    icon: 'success',
                    confirmButtonColor: '#0D9488',
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    @endif

    @if(session('error') || $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let errorMessage = "{!! session('error') ? addslashes(session('error')) : '' !!}";
                @if($errors->any())
                    errorMessage = "{!! addslashes(implode('<br>', $errors->all())) !!}";
                @endif
                Swal.fire({
                    title: 'Atención',
                    html: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#F43F5E',
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>lucide.createIcons();</script>
@stack('scripts')
</body>
</html>
