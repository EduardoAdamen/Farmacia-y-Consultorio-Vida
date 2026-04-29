@extends('layouts.app')
@section('title', 'Editar Usuario')

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('usuarios.index') }}" class="topbar-icon-btn" title="Volver">
        <i data-lucide="arrow-left" style="width:20px;height:20px;"></i>
    </a>
    <div>
        <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;">Editar Usuario</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">Modifica los datos de <strong>{{ $usuario->nombre_completo }}</strong>.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header py-3 px-4">
                <span class="d-flex align-items-center gap-2">
                    <i data-lucide="user-cog" style="width:16px;height:16px;color:var(--color-accent);"></i>
                    Datos del usuario
                </span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="nombre_completo">Nombre completo <span style="color:var(--color-danger);">*</span></label>
                        <input type="text" id="nombre_completo" name="nombre_completo"
                               class="form-control @error('nombre_completo') is-invalid @enderror"
                               value="{{ old('nombre_completo', $usuario->nombre_completo) }}"
                               autofocus>
                        @error('nombre_completo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="username">Nombre de usuario <span style="color:var(--color-danger);">*</span></label>
                        <input type="text" id="username" name="username"
                               class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username', $usuario->username) }}"
                               autocomplete="off">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="rol">Rol <span style="color:var(--color-danger);">*</span></label>
                        <select id="rol" name="rol" class="form-select @error('rol') is-invalid @enderror"
                                {{ $usuario->id === auth()->id() ? 'disabled' : '' }}>
                            <option value="dueno"    {{ old('rol', $usuario->rol) === 'dueno'    ? 'selected' : '' }}>Dueño</option>
                            <option value="vendedor" {{ old('rol', $usuario->rol) === 'vendedor' ? 'selected' : '' }}>Vendedor</option>
                            <option value="medico"   {{ old('rol', $usuario->rol) === 'medico'   ? 'selected' : '' }}>Médico</option>
                        </select>
                        @if($usuario->id === auth()->id())
                        {{-- Campo oculto para que su rol actual se envíe igual --}}
                        <input type="hidden" name="rol" value="{{ $usuario->rol }}">
                        <div style="font-size:12px;color:var(--color-text-muted);margin-top:5px;">
                            <i data-lucide="info" style="width:12px;height:12px;"></i>
                            No puedes cambiar tu propio rol.
                        </div>
                        @endif
                        @error('rol')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 20px;">
                            <i data-lucide="save" style="width:15px;height:15px;"></i>
                            Guardar Cambios
                        </button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-ghost" style="border-radius:8px;padding:9px 16px;font-size:13px;">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
