@extends('layouts.app')
@section('title', 'Registrar Consulta Médica')
@section('page-title', 'Registrar Consulta Médica')

@section('content')
<form action="{{ route('consultas.store') }}" method="POST">
    @csrf
    
    @if($expediente)
        <input type="hidden" name="expediente_id" value="{{ $expediente->id }}">
    @endif
    @if($cita)
        <input type="hidden" name="cita_id" value="{{ $cita->id }}">
    @endif

    <div class="row g-4">
        {{-- COLUMNA IZQUIERDA --}}
        <div class="col-md-4">
            {{-- Paciente Info --}}
            <div class="card mb-4">
                <div class="card-header py-3 px-4 bg-white border-bottom">
                    <h6 class="mb-0 fw-bold" style="color: var(--color-primary);">Datos del Paciente</h6>
                </div>
                <div class="card-body p-4">
                    @if($expediente)
                        <div class="fw-bold mb-1" style="font-size: 16px;">{{ $expediente->nombre_completo }}</div>
                        <div class="text-muted" style="font-size: 13px;">{{ $expediente->edad }} años | Sexo: {{ $expediente->sexo }} | Sangre: {{ $expediente->tipo_sangre ?: 'N/E' }}</div>
                        @if($expediente->alergias)
                            <div class="mt-3 p-2 bg-danger bg-opacity-10 text-danger rounded" style="font-size: 12px;">
                                <strong>Alergias:</strong> {{ $expediente->alergias }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning mb-0" style="font-size: 13px;">
                            <i data-lucide="alert-triangle" style="width:16px;height:16px;" class="me-1"></i>
                            Por favor seleccione un expediente previamente.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Signos Vitales --}}
            <div class="card mb-4">
                <div class="card-header py-3 px-4 bg-white border-bottom">
                    <h6 class="mb-0 fw-bold" style="color: var(--color-accent);"><i data-lucide="activity" style="width:16px;height:16px;margin-right:8px;"></i>Signos Vitales</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Presión Arterial (mmHg)</label>
                        <input type="text" name="presion_arterial" class="form-control form-control-sm @error('presion_arterial') is-invalid @enderror" value="{{ old('presion_arterial') }}" placeholder="Ej. 120/80">
                        @error('presion_arterial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Temperatura (°C)</label>
                            <input type="number" step="0.1" name="temperatura" class="form-control form-control-sm @error('temperatura') is-invalid @enderror" value="{{ old('temperatura') }}" placeholder="Ej. 36.5">
                            @error('temperatura') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Frec. Cardíaca (lpm)</label>
                            <input type="number" name="frecuencia_cardiaca" class="form-control form-control-sm @error('frecuencia_cardiaca') is-invalid @enderror" value="{{ old('frecuencia_cardiaca') }}" placeholder="Ej. 80">
                            @error('frecuencia_cardiaca') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Peso (kg)</label>
                            <input type="number" step="0.1" name="peso" class="form-control form-control-sm @error('peso') is-invalid @enderror" value="{{ old('peso') }}" placeholder="Ej. 70.5">
                            @error('peso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Talla (m)</label>
                            <input type="number" step="0.01" name="talla" class="form-control form-control-sm @error('talla') is-invalid @enderror" value="{{ old('talla') }}" placeholder="Ej. 1.75">
                            @error('talla') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Cierre de Consulta --}}
            <div class="card mb-4">
                <div class="card-header py-3 px-4 bg-white border-bottom">
                    <h6 class="mb-0 fw-bold" style="color: var(--color-warning);"><i data-lucide="check-circle" style="width:16px;height:16px;margin-right:8px;"></i>Cierre de Consulta</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Tipo de Consulta <span class="text-danger">*</span></label>
                        <select name="tipo_consulta" class="form-select form-select-sm @error('tipo_consulta') is-invalid @enderror" required>
                            <option value="primera_vez" {{ old('tipo_consulta') == 'primera_vez' ? 'selected' : '' }}>Primera Vez</option>
                            <option value="seguimiento" {{ old('tipo_consulta') == 'seguimiento' ? 'selected' : '' }}>Seguimiento</option>
                            <option value="urgencia" {{ old('tipo_consulta') == 'urgencia' ? 'selected' : '' }}>Urgencia</option>
                        </select>
                        @error('tipo_consulta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Costo ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="costo" class="form-control form-control-sm @error('costo') is-invalid @enderror" value="{{ old('costo', 0) }}" required min="0">
                            @error('costo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Estado de Pago <span class="text-danger">*</span></label>
                            <select name="estado_pago" class="form-select form-select-sm @error('estado_pago') is-invalid @enderror" required>
                                <option value="pagado" {{ old('estado_pago') == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                <option value="pendiente" {{ old('estado_pago') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="cortesia" {{ old('estado_pago') == 'cortesia' ? 'selected' : '' }}>Cortesía</option>
                            </select>
                            @error('estado_pago') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-size:12px;font-weight:600;">Próxima Cita Sugerida</label>
                        <input type="date" name="proxima_cita" class="form-control form-control-sm @error('proxima_cita') is-invalid @enderror" value="{{ old('proxima_cita') }}" min="{{ now()->addDay()->toDateString() }}">
                        @error('proxima_cita') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA --}}
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header py-3 px-4 bg-white border-bottom">
                    <h6 class="mb-0 fw-bold" style="color: var(--color-info);"><i data-lucide="file-text" style="width:16px;height:16px;margin-right:8px;"></i>Registro Clínico</h6>
                </div>
                <div class="card-body p-4 d-flex flex-column gap-4">
                    
                    {{-- Motivo y Síntomas --}}
                    <div>
                        <label class="form-label fw-bold">Motivo de la Consulta <span class="text-danger">*</span></label>
                        <textarea name="motivo" class="form-control @error('motivo') is-invalid @enderror" rows="2" required placeholder="Motivo principal por el que acude el paciente...">{{ old('motivo', $cita?->motivo ?? '') }}</textarea>
                        @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="form-label fw-bold">Síntomas y Subjetivo <span class="text-danger">*</span></label>
                        <textarea name="sintomas" class="form-control @error('sintomas') is-invalid @enderror" rows="4" required placeholder="Describa los síntomas referidos por el paciente...">{{ old('sintomas') }}</textarea>
                        @error('sintomas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-0 text-muted">

                    {{-- Diagnóstico y Tratamiento --}}
                    <div>
                        <label class="form-label fw-bold">Diagnóstico <span class="text-danger">*</span></label>
                        <textarea name="diagnostico" class="form-control @error('diagnostico') is-invalid @enderror" rows="3" required placeholder="Diagnóstico médico establecido...">{{ old('diagnostico') }}</textarea>
                        @error('diagnostico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="form-label fw-bold">Plan de Tratamiento / Receta</label>
                        <textarea name="tratamiento" class="form-control @error('tratamiento') is-invalid @enderror" rows="4" placeholder="Indique el tratamiento a seguir...">{{ old('tratamiento') }}</textarea>
                        <div class="form-text">Nota: Podrá generar la receta digital en PDF en el siguiente paso.</div>
                        @error('tratamiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="form-label fw-bold">Estudios Solicitados</label>
                        <textarea name="estudios_solicitados" class="form-control @error('estudios_solicitados') is-invalid @enderror" rows="2" placeholder="Laboratorios, rayos X, etc. (Opcional)">{{ old('estudios_solicitados') }}</textarea>
                        @error('estudios_solicitados') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>
                <div class="card-footer bg-white py-3 px-4 d-flex justify-content-end gap-2 border-top">
                    @if($expediente)
                        <a href="{{ route('expedientes.show', $expediente->id) }}" class="btn btn-outline-secondary">Cancelar</a>
                    @else
                        <a href="{{ route('panel-inicio') }}" class="btn btn-outline-secondary">Cancelar</a>
                    @endif
                    
                    <button type="submit" class="btn btn-accent d-flex align-items-center gap-2">
                        <i data-lucide="save" style="width:18px;height:18px;"></i> Guardar Consulta
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
