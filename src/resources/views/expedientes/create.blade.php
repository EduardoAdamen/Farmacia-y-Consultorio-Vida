@extends('layouts.app')
@section('title', 'Nuevo Expediente Clínico')
@section('page-title', 'Nuevo Expediente Clínico')

@section('content')
<div class="card max-w-4xl mx-auto" style="max-width: 900px;">
    <div class="card-header py-3 px-4">
        <h5 class="mb-0 fw-bold">Detalles del Paciente</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('expedientes.store') }}" method="POST">
            @csrf
            
            <h6 class="fw-bold mb-3" style="color: var(--color-accent); border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">Datos Personales</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre_completo" class="form-control @error('nombre_completo') is-invalid @enderror" value="{{ old('nombre_completo') }}" required maxlength="150">
                    @error('nombre_completo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Fecha de Nac. <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_nacimiento" class="form-control @error('fecha_nacimiento') is-invalid @enderror" value="{{ old('fecha_nacimiento') }}" required max="{{ now()->toDateString() }}">
                    @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Sexo <span class="text-danger">*</span></label>
                    <select name="sexo" class="form-select @error('sexo') is-invalid @enderror" required>
                        <option value="">Seleccione...</option>
                        <option value="masculino" {{ old('sexo') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                        <option value="femenino" {{ old('sexo') == 'femenino' ? 'selected' : '' }}>Femenino</option>
                        <option value="otro" {{ old('sexo') == 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('sexo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tipo de Sangre</label>
                    <select name="tipo_sangre" class="form-select @error('tipo_sangre') is-invalid @enderror">
                        <option value="">No especificado</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $ts)
                            <option value="{{ $ts }}" {{ old('tipo_sangre') == $ts ? 'selected' : '' }}>{{ $ts }}</option>
                        @endforeach
                    </select>
                    @error('tipo_sangre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="tel" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono') }}" maxlength="20">
                    @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control @error('correo') is-invalid @enderror" value="{{ old('correo') }}" maxlength="100">
                    @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <h6 class="fw-bold mb-3" style="color: var(--color-accent); border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">Antecedentes Médicos</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Alergias</label>
                    <textarea name="alergias" class="form-control @error('alergias') is-invalid @enderror" rows="2" placeholder="Ej. Penicilina, polen...">{{ old('alergias') }}</textarea>
                    @error('alergias') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Enfermedades Crónicas</label>
                    <textarea name="enfermedades_cronicas" class="form-control @error('enfermedades_cronicas') is-invalid @enderror" rows="2" placeholder="Ej. Hipertensión, diabetes...">{{ old('enfermedades_cronicas') }}</textarea>
                    @error('enfermedades_cronicas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Medicamentos Actuales</label>
                    <textarea name="medicamentos_actuales" class="form-control @error('medicamentos_actuales') is-invalid @enderror" rows="2">{{ old('medicamentos_actuales') }}</textarea>
                    @error('medicamentos_actuales') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Antecedentes Familiares</label>
                    <textarea name="antecedentes_familiares" class="form-control @error('antecedentes_familiares') is-invalid @enderror" rows="2">{{ old('antecedentes_familiares') }}</textarea>
                    @error('antecedentes_familiares') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('expedientes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-accent d-flex align-items-center gap-2">
                    <i data-lucide="save" style="width:18px;height:18px;"></i> Guardar Expediente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
