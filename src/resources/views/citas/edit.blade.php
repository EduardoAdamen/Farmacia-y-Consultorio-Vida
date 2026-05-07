@extends('layouts.app')
@section('title', 'Editar Cita')
@section('page-title', 'Editar Cita')

@section('content')
<div class="card max-w-2xl mx-auto" style="max-width: 800px;">
    <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Actualizar Cita #{{ $cita->id }}</h5>
        <span class="badge badge-{{ $cita->estado }}">{{ ucfirst($cita->estado) }}</span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('citas.update', $cita->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted" style="font-size: 12px;">Médico</label>
                    <div class="form-control bg-light">{{ $cita->medico->nombre_completo }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted" style="font-size: 12px;">Paciente</label>
                    <div class="form-control bg-light">{{ $cita->nombre_temporal ?: $cita->expediente->nombre_completo }}</div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Nuevo Estado <span class="text-danger">*</span></label>
                <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                    <option value="programada" {{ $cita->estado === 'programada' ? 'selected' : '' }}>Programada</option>
                    <option value="completada" {{ $cita->estado === 'completada' ? 'selected' : '' }}>Completada</option>
                    <option value="reprogramada" {{ $cita->estado === 'reprogramada' ? 'selected' : '' }}>Reprogramada</option>
                    <option value="cancelada" {{ $cita->estado === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
                @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div id="div-reprogramar" class="row g-3 mb-4 {{ $cita->estado === 'reprogramada' ? '' : 'd-none' }}">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nueva Fecha <span class="text-danger">*</span></label>
                    <input type="date" name="fecha" id="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', $cita->fecha->toDateString()) }}" min="{{ now()->toDateString() }}">
                    @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nueva Hora <span class="text-danger">*</span></label>
                    <input type="time" name="hora" id="hora" class="form-control @error('hora') is-invalid @enderror" value="{{ old('hora', \Carbon\Carbon::parse($cita->hora)->format('H:i')) }}" step="1800">
                    @error('hora') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-4" id="div-motivo" style="{{ in_array($cita->estado, ['reprogramada', 'cancelada']) ? '' : 'display: none;' }}">
                <label class="form-label fw-bold">Motivo del Cambio <span class="text-danger">*</span></label>
                <textarea name="motivo" id="motivo" class="form-control @error('motivo') is-invalid @enderror" rows="3">{{ old('motivo', $cita->motivo) }}</textarea>
                <div class="form-text">Si reprograma o cancela, explique el motivo del cambio (ej. "A petición del paciente").</div>
                @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('citas.show', $cita->id) }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-accent d-flex align-items-center gap-2">
                    <i data-lucide="save" style="width:18px;height:18px;"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectEstado = document.getElementById('estado');
        const divReprogramar = document.getElementById('div-reprogramar');
        const divMotivo = document.getElementById('div-motivo');
        const inputMotivo = document.getElementById('motivo');

        selectEstado.addEventListener('change', function() {
            const val = this.value;
            
            if (val === 'reprogramada') {
                divReprogramar.classList.remove('d-none');
                divMotivo.style.display = 'block';
                inputMotivo.required = true;
            } else if (val === 'cancelada') {
                divReprogramar.classList.add('d-none');
                divMotivo.style.display = 'block';
                inputMotivo.required = true;
            } else {
                divReprogramar.classList.add('d-none');
                divMotivo.style.display = 'none';
                inputMotivo.required = false;
            }
        });
    });
</script>
@endpush
@endsection
