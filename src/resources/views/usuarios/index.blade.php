@extends('layouts.app')
@section('title', 'Gestión de Usuarios')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1" style="font-family:'Outfit',sans-serif;font-weight:700;">Usuarios del Sistema</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">Administra los accesos y roles del personal.</p>
    </div>
    <a href="{{ route('usuarios.create') }}" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
        <i data-lucide="user-plus" style="width:16px;height:16px;"></i>
        Nuevo Usuario
    </a>
</div>

{{-- ── Barra de búsqueda flotante alineada a la derecha ── --}}
<form method="GET" action="{{ route('usuarios.index') }}" class="d-flex gap-2 align-items-center justify-content-end mb-4">
    <div class="d-flex align-items-center gap-2"
         style="background:#fff;border:1px solid var(--color-border);border-radius:10px;padding:9px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.04);min-width:280px;">
        <i data-lucide="search" style="width:16px;height:16px;color:var(--color-text-muted);flex-shrink:0;"></i>
        <input type="text" name="buscar" value="{{ request('buscar') }}"
               placeholder="Buscar por nombre o usuario..."
               style="border:none;outline:none;font-size:13.5px;width:100%;background:transparent;color:inherit;">
    </div>
    <button type="submit" class="btn btn-accent"
            style="font-size:13px;border-radius:10px;padding:9px 18px;font-weight:600;">
        Buscar
    </button>
    @if(request('buscar'))
    <a href="{{ route('usuarios.index') }}" class="btn btn-ghost"
       style="border-radius:10px;padding:9px 14px;font-size:13px;">
        Limpiar
    </a>
    @endif
</form>

{{-- ── Tabla de usuarios ─────────────────────────────────── --}}
<div class="card" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background:#F8FAFC;border-bottom:1px solid var(--color-border);font-size:12.5px;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.5px;">
                <tr>
                    <th class="px-4 py-3" style="font-weight:600;border:none;">Nombre</th>
                    <th class="py-3" style="font-weight:600;border:none;">Usuario</th>
                    <th class="py-3" style="font-weight:600;border:none;">Rol</th>
                    <th class="py-3" style="font-weight:600;border:none;">Estado</th>
                    <th class="py-3 text-end px-4" style="font-weight:600;border:none;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                @php
                    $buscar = request('buscar');
                    $visible = !$buscar || str_contains(strtolower($usuario->nombre_completo), strtolower($buscar))
                                        || str_contains(strtolower($usuario->username), strtolower($buscar));
                @endphp
                @if($visible)
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td class="px-4 py-3" style="border:none;">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;">
                                {{ strtoupper(substr($usuario->nombre_completo, 0, 1)) }}
                            </div>
                            <div style="font-weight:600;font-size:13.5px;">{{ $usuario->nombre_completo }}</div>
                        </div>
                    </td>
                    <td class="py-3" style="border:none;">
                        <code style="font-size:12px;background:#F1F5F9;padding:3px 7px;border-radius:5px;">{{ $usuario->username }}</code>
                    </td>
                    <td class="py-3" style="border:none;">
                        <span class="badge badge-{{ $usuario->rol }} rounded-pill px-3">{{ ucfirst($usuario->rol) }}</span>
                    </td>
                    <td class="py-3" style="border:none;">
                        <span class="badge badge-{{ $usuario->estado }} rounded-pill px-3">
                            {{ $usuario->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="py-3 px-4" style="border:none;">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('usuarios.edit', $usuario->id) }}"
                               class="btn btn-ghost btn-sm d-flex align-items-center gap-1"
                               style="font-size:12px;border-radius:6px;padding:5px 10px;">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i> Editar
                            </a>

                            <form method="POST" action="{{ route('usuarios.toggle-estado', $usuario->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-ghost btn-sm d-flex align-items-center gap-1"
                                        style="font-size:12px;border-radius:6px;padding:5px 10px;{{ $usuario->id === auth()->id() ? 'opacity:0.4;cursor:not-allowed;' : '' }}"
                                        {{ $usuario->id === auth()->id() ? 'disabled' : '' }}>
                                    @if($usuario->estado === 'activo')
                                        <i data-lucide="user-x" style="width:14px;height:14px;color:var(--color-danger);"></i>
                                        <span style="color:var(--color-danger);">Desactivar</span>
                                    @else
                                        <i data-lucide="user-check" style="width:14px;height:14px;color:var(--color-success);"></i>
                                        <span style="color:var(--color-success);">Activar</span>
                                    @endif
                                </button>
                            </form>

                            <form method="POST" action="{{ route('usuarios.reset-password', $usuario->id) }}"
                                  onsubmit="return confirm('¿Restablecer la contraseña de {{ $usuario->nombre_completo }}?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-ghost btn-sm d-flex align-items-center gap-1"
                                        style="font-size:12px;border-radius:6px;padding:5px 10px;">
                                    <i data-lucide="key" style="width:14px;height:14px;"></i> Restablecer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5" style="color:var(--color-text-muted);font-size:13px;border:none;">
                        <i data-lucide="users" style="width:32px;height:32px;display:block;margin:0 auto 8px;opacity:0.4;"></i>
                        No hay usuarios registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
