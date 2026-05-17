@extends('layouts.app')
@section('title', 'Detalle de Consulta Médica')
@section('page-title', 'Consulta Médica')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0 fw-bold">Consulta del {{ $consulta->fecha_hora->format('d/m/Y') }}</h5>
        <div style="font-size: 13px; color: var(--color-text-muted);">
            Dr/a. {{ $consulta->medico->nombre_completo }} | 
            {{ ucfirst(str_replace('_', ' ', $consulta->tipo_consulta)) }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('expedientes.show', $consulta->expediente_id) }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Volver a Expediente
        </a>
        @if(auth()->user()->rol === 'medico')
        <a href="{{ route('consultas.edit', $consulta->id) }}" class="btn btn-outline-primary d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
            <i data-lucide="edit" style="width:16px;height:16px;"></i> Editar
        </a>
        <a href="{{ route('recetas.create', $consulta->id) }}" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
            <i data-lucide="file-text" style="width:16px;height:16px;"></i> Generar Receta
        </a>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        {{-- Paciente Info --}}
        <div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold" style="color: var(--color-primary);">Paciente</h6>
            </div>
            <div class="card-body p-4">
                <div class="fw-bold mb-1" style="font-size: 16px;">{{ $consulta->expediente->nombre_completo }}</div>
                <div class="text-muted" style="font-size: 13px;">{{ $consulta->expediente->edad }} años | Sexo: {{ $consulta->expediente->sexo }}</div>
                <div class="text-muted" style="font-size: 13px;">Sangre: {{ $consulta->expediente->tipo_sangre ?: 'N/E' }}</div>
            </div>
        </div>

        {{-- Signos Vitales --}}
        <div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold" style="color: var(--color-accent);"><i data-lucide="activity" style="width:16px;height:16px;margin-right:8px;"></i>Signos Vitales</h6>
            </div>
            <div class="card-body p-4" style="font-size: 13px;">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-muted">Presión Arterial:</span>
                    <span>{{ $consulta->presion_arterial ?: 'N/R' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-muted">Temperatura:</span>
                    <span>{{ $consulta->temperatura ? $consulta->temperatura.' °C' : 'N/R' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-muted">Frec. Cardíaca:</span>
                    <span>{{ $consulta->frecuencia_cardiaca ? $consulta->frecuencia_cardiaca.' lpm' : 'N/R' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-muted">Peso:</span>
                    <span>{{ $consulta->peso ? $consulta->peso.' kg' : 'N/R' }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold text-muted">Talla:</span>
                    <span>{{ $consulta->talla ? $consulta->talla.' m' : 'N/R' }}</span>
                </div>
            </div>
        </div>

        {{-- Cierre de Consulta --}}
        <div class="card" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold" style="color: var(--color-warning);"><i data-lucide="check-circle" style="width:16px;height:16px;margin-right:8px;"></i>Cierre de Consulta</h6>
            </div>
            <div class="card-body p-4" style="font-size: 13px;">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-muted">Costo:</span>
                    <span class="fw-bold">${{ number_format($consulta->costo, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-muted">Estado Pago:</span>
                    <span class="badge {{ $consulta->estado_pago === 'pagado' ? 'bg-success' : ($consulta->estado_pago === 'pendiente' ? 'bg-warning text-dark' : 'bg-info') }} text-capitalize">
                        {{ $consulta->estado_pago }}
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold text-muted">Próxima Cita:</span>
                    <span>{{ $consulta->proxima_cita ? $consulta->proxima_cita->format('d/m/Y') : 'No agendada' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- COLUMNA DERECHA --}}
    <div class="col-md-8">
        <div class="card h-100" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold" style="color: var(--color-info);"><i data-lucide="file-text" style="width:16px;height:16px;margin-right:8px;"></i>Registro Clínico</h6>
            </div>
            <div class="card-body p-4 d-flex flex-column gap-4">
                
                <div>
                    <div class="fw-bold mb-2 text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em;">Motivo de la Consulta</div>
                    <div class="p-3 bg-light rounded" style="font-size: 14px;">{{ $consulta->motivo }}</div>
                </div>

                <div>
                    <div class="fw-bold mb-2 text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em;">Síntomas y Subjetivo</div>
                    <div class="p-3 bg-light rounded" style="font-size: 14px; white-space: pre-wrap;">{{ $consulta->sintomas }}</div>
                </div>

                <div>
                    <div class="fw-bold mb-2 text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em;">Diagnóstico</div>
                    <div class="p-3 bg-light rounded" style="font-size: 14px; font-weight: 500; white-space: pre-wrap;">{{ $consulta->diagnostico }}</div>
                </div>

                @if($consulta->tratamiento)
                <div>
                    <div class="fw-bold mb-2 text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em;">Plan de Tratamiento / Receta</div>
                    <div class="p-3 bg-light rounded" style="font-size: 14px; white-space: pre-wrap;">{{ $consulta->tratamiento }}</div>
                </div>
                @endif

                @if($consulta->estudios_solicitados)
                <div>
                    <div class="fw-bold mb-2 text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em;">Estudios Solicitados</div>
                    <div class="p-3 bg-light rounded" style="font-size: 14px; white-space: pre-wrap;">{{ $consulta->estudios_solicitados }}</div>
                </div>
                @endif

                <hr class="my-0 text-muted">

                {{-- Notas de Evolución --}}
                <div class="mt-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em;">Notas de Evolución (Privadas)</div>
                    </div>
                    @if(auth()->user()->rol === 'medico')
                    <form action="{{ route('consultas.update-notas', $consulta->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <textarea name="notas_evolucion" class="form-control mb-3" rows="3" placeholder="Agregue notas internas sobre la evolución del paciente...">{{ old('notas_evolucion', $consulta->notas_evolucion) }}</textarea>
                        <div class="text-end">
                            <button type="submit" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-2 ms-auto">
                                <i data-lucide="save" style="width:14px;height:14px;"></i> Guardar Notas
                            </button>
                        </div>
                    </form>
                    @else
                        @if($consulta->notas_evolucion)
                        <div class="p-3 bg-light rounded" style="font-size: 14px; white-space: pre-wrap;">{{ $consulta->notas_evolucion }}</div>
                        @else
                        <div class="text-muted" style="font-size: 13px;">No hay notas registradas.</div>
                        @endif
                    @endif
                </div>

                <hr class="my-0 text-muted">

                {{-- Recetas Generadas --}}
                <div class="mt-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em;">Recetas Generadas</div>
                    </div>
                    @if($consulta->recetas && $consulta->recetas->count() > 0)
                        <div class="list-group">
                            @foreach($consulta->recetas as $receta)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $receta->folio }}</strong><br>
                                    <small class="text-muted">{{ $receta->fecha->format('d/m/Y') }}</small>
                                </div>
                                <a href="{{ route('recetas.imprimir', $receta->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i data-lucide="printer" style="width:14px;height:14px;"></i> Imprimir
                                </a>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted" style="font-size: 13px;">No se han generado recetas para esta consulta.</div>
                    @endif
                </div>
                
            </div>
        </div>
    </div>
</div>
@endsection
