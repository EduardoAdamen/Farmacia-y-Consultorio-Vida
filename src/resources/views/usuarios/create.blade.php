@extends('layouts.app')
@section('title', 'Nuevo Usuario')

@section('content')
<div class="mx-auto" style="max-width: 600px;">
    <div class="mb-4">
        <a href="{{ route('usuarios.index') }}" class="text-decoration-none" style="font-size:13px;color:var(--color-text-muted);display:inline-flex;align-items:center;gap:6px;font-weight:500;transition:all 0.2s;">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Volver a usuarios
        </a>
    </div>

    <div class="card" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;">
        <div class="card-body p-4">
            <h5 class="mb-1" style="font-family:'Outfit',sans-serif;font-weight:700;">Crear Usuario</h5>
            <p style="font-size:13px;color:var(--color-text-muted);margin-bottom:24px;">Complete los datos para registrar un nuevo acceso al sistema.</p>

            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="nombre_completo">Nombre completo <span style="color:var(--color-danger);">*</span></label>
                    <input type="text" id="nombre_completo" name="nombre_completo"
                           class="form-control @error('nombre_completo') is-invalid @enderror"
                           value="{{ old('nombre_completo') }}"
                           placeholder="Ej. María González López"
                           autofocus>
                    @error('nombre_completo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="username">Nombre de usuario <span style="color:var(--color-danger);">*</span></label>
                    <input type="text" id="username" name="username"
                           class="form-control @error('username') is-invalid @enderror"
                           value="{{ old('username') }}"
                           placeholder="Ej. mgonzalez"
                           autocomplete="off">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Contraseña <span style="color:var(--color-danger);">*</span></label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Mínimo 8 caracteres con letras y números"
                           autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div style="font-size:12px;color:var(--color-text-muted);margin-top:5px;">
                        <i data-lucide="info" style="width:12px;height:12px;"></i>
                        Debe combinar letras y números (mínimo 8 caracteres).
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="rol">Rol <span style="color:var(--color-danger);">*</span></label>
                    <select id="rol" name="rol" class="form-select @error('rol') is-invalid @enderror">
                        <option value="" disabled {{ old('rol') ? '' : 'selected' }}>Selecciona un rol…</option>
                        <option value="dueno"    {{ old('rol') === 'dueno'    ? 'selected' : '' }}>Dueño</option>
                        <option value="vendedor" {{ old('rol') === 'vendedor' ? 'selected' : '' }}>Vendedor</option>
                        <option value="medico"   {{ old('rol') === 'medico'   ? 'selected' : '' }}>Médico</option>
                    </select>
                    @error('rol')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 20px;">
                        <i data-lucide="save" style="width:15px;height:15px;"></i>
                        Guardar Usuario
                    </button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-ghost" style="border-radius:8px;padding:9px 16px;font-size:13px;">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
