@extends('layouts.app')
@section('title', 'Cambiar Contraseña')

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('panel-inicio') }}" class="topbar-icon-btn" title="Volver al panel">
        <i data-lucide="arrow-left" style="width:20px;height:20px;"></i>
    </a>
    <div>
        
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">Volver al panel</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header py-3 px-4">
                <span class="d-flex align-items-center gap-2">
                   
                    Nueva contraseña
                </span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('perfil.cambiar-password') }}">
                    @csrf @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label" for="password_actual">Contraseña actual <span style="color:var(--color-danger);">*</span></label>
                        <input type="password" id="password_actual" name="password_actual"
                               class="form-control @error('password_actual') is-invalid @enderror"
                               placeholder="Escribe tu contraseña actual"
                               autocomplete="current-password">
                        @error('password_actual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_nuevo">Nueva contraseña <span style="color:var(--color-danger);">*</span></label>
                        <input type="password" id="password_nuevo" name="password_nuevo"
                               class="form-control @error('password_nuevo') is-invalid @enderror"
                               placeholder="Mínimo 8 caracteres con letras y números"
                               autocomplete="new-password">
                        @error('password_nuevo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password_nuevo_confirmation">Confirmar nueva contraseña <span style="color:var(--color-danger);">*</span></label>
                        <input type="password" id="password_nuevo_confirmation" name="password_nuevo_confirmation"
                               class="form-control"
                               placeholder="Repite la nueva contraseña"
                               autocomplete="new-password">
                    </div>

                    <div style="font-size:12px;color:var(--color-text-muted);background:#F8FAFC;border-radius:8px;padding:10px 14px;margin-bottom:20px;border:1px solid var(--color-border);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                           
                            <strong>Requisitos de seguridad</strong>
                        </div>
                        <ul class="mb-0 ps-3" style="line-height:1.8;">
                            <li>Mínimo 8 caracteres</li>
                            <li>Debe combinar letras y números</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 20px;">
                            <i data-lucide="lock" style="width:15px;height:15px;"></i>
                            Actualizar Contraseña
                        </button>
                        <a href="{{ route('panel-inicio') }}" class="btn btn-ghost" style="border-radius:8px;padding:9px 16px;font-size:13px;">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
