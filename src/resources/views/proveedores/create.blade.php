@extends('layouts.app')
@section('title', 'Nuevo Proveedor')

@section('content')
<div class="mx-auto" style="max-width: 800px;">
    <div class="mb-4">
        <a href="{{ route('proveedores.index') }}" class="text-decoration-none" style="font-size:13px;color:var(--color-text-muted);display:inline-flex;align-items:center;gap:6px;font-weight:500;transition:all 0.2s;">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Volver a proveedores
        </a>
    </div>

    <div class="card" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;">
        <div class="card-body p-4">
            <h5 class="mb-4" style="font-family:'Outfit',sans-serif;font-weight:700;">Registrar Proveedor</h5>

            <form method="POST" action="{{ route('proveedores.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_empresa" class="form-control" value="{{ old('nombre_empresa') }}" required>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <label class="form-label">Nombre del Contacto <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_contacto" class="form-control" value="{{ old('nombre_contacto') }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" required>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="correo_electronico" class="form-control" value="{{ old('correo_electronico') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">RFC</label>
                    <input type="text" name="rfc" class="form-control text-uppercase" value="{{ old('rfc') }}" maxlength="13">
                </div>

                <div class="mb-4">
                    <label class="form-label">Días de Visita (Opcional)</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['lun','mar','mie','jue','vie','sab','dom'] as $dia)
                            <div class="form-check form-switch col-3 mt-1">
                                <input class="form-check-input" type="checkbox" name="dias_visita[]" value="{{ $dia }}" id="dia_{{ $dia }}">
                                <label class="form-check-label text-capitalize" for="dia_{{ $dia }}">
                                    {{ $dia }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr style="border-color: var(--color-border);margin: 24px 0;">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                    <button type="submit" class="btn btn-accent px-4 d-flex align-items-center gap-2">
                        <i data-lucide="save" style="width:16px;height:16px;"></i> Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
