@extends('layouts.app')
@section('title', 'Detalle de Expediente')
@section('page-title', 'Expediente Clínico')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0 fw-bold">{{ $expediente->nombre_completo }}</h5>
        <div style="font-size: 13px; color: var(--color-text-muted);">
            ID: {{ str_pad($expediente->id, 5, '0', STR_PAD_LEFT) }} | 
            Edad: {{ $expediente->edad }} años
            @if($expediente->estado === 'activo')
                <span class="badge badge-activo ms-2">Activo</span>
            @else
                <span class="badge badge-inactivo ms-2">Archivado</span>
            @endif
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('expedientes.index') }}" class="btn btn-ghost d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Volver
        </a>
        @unless(auth()->user()->rol === 'dueno')
        <a href="{{ route('expedientes.edit', $expediente->id) }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
            <i data-lucide="edit" style="width:16px;height:16px;"></i> Editar
        </a>
        
        @if($expediente->estado === 'activo')
        <form action="{{ route('expedientes.archivar', $expediente->id) }}" method="POST" class="mb-0" id="formArchivar">
            @csrf
            @method('PATCH')
            <button type="button" class="btn btn-outline-danger d-flex align-items-center gap-2 btn-archivar" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
                <i data-lucide="archive" style="width:16px;height:16px;"></i> Archivar
            </button>
        </form>
        @else
        <form action="{{ route('expedientes.desarchivar', $expediente->id) }}" method="POST" class="mb-0" id="formDesarchivar">
            @csrf
            @method('PATCH')
            <button type="button" class="btn btn-outline-success d-flex align-items-center gap-2 btn-desarchivar" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
                <i data-lucide="archive-restore" style="width:16px;height:16px;"></i> Desarchivar
            </button>
        </form>
        @endif
        @endunless
        
        @if(auth()->user()->rol === 'medico')
        <a href="{{ route('consultas.create', ['expediente_id' => $expediente->id]) }}" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
            <i data-lucide="stethoscope" style="width:16px;height:16px;"></i> Registrar Consulta
        </a>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        {{-- Datos Personales --}}
        <div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold" style="color: var(--color-accent);"><i data-lucide="user" style="width:16px;height:16px;margin-right:8px;"></i>Datos Personales</h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600;">Fecha de Nacimiento</div>
                    <div>{{ $expediente->fecha_nacimiento->format('d/m/Y') }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600;">Sexo / Tipo Sangre</div>
                    <div>{{ $expediente->sexo }} / {{ $expediente->tipo_sangre ?: 'N/E' }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600;">Teléfono</div>
                    <div>{{ $expediente->telefono ?: 'No registrado' }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600;">Correo Electrónico</div>
                    <div>{{ $expediente->correo ?: 'No registrado' }}</div>
                </div>
            </div>
        </div>

        {{-- Antecedentes Médicos --}}
        <div class="card" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold" style="color: var(--color-warning);"><i data-lucide="clipboard-list" style="width:16px;height:16px;margin-right:8px;"></i>Antecedentes Médicos</h6>
            </div>
            <div class="card-body p-4" style="font-size: 13px;">
                <div class="mb-3">
                    <div style="font-weight: 600;">Alergias:</div>
                    <div class="text-muted">{{ $expediente->alergias ?: 'Ninguna registrada' }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-weight: 600;">Enfermedades Crónicas:</div>
                    <div class="text-muted">{{ $expediente->enfermedades_cronicas ?: 'Ninguna registrada' }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-weight: 600;">Medicamentos Actuales:</div>
                    <div class="text-muted">{{ $expediente->medicamentos_actuales ?: 'Ninguno registrado' }}</div>
                </div>
                <div>
                    <div style="font-weight: 600;">Antecedentes Familiares:</div>
                    <div class="text-muted">{{ $expediente->antecedentes_familiares ?: 'Ninguno registrado' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Historial de Consultas --}}
        <div class="card h-100" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i data-lucide="history" style="width:16px;height:16px;margin-right:8px;color:var(--color-info);"></i>Historial de Consultas</h6>
                <span class="badge bg-secondary">{{ $consultas->total() }} consultas</span>
            </div>
            <div class="card-body p-0">
                @forelse($consultas as $consulta)
                    <div class="p-4 border-bottom position-relative hover-bg-light" style="transition: background 0.2s;">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width: 40px; height: 40px; background: #e0f2fe; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--color-info);">
                                    <i data-lucide="stethoscope" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size: 15px;">{{ $consulta->fecha_hora->format('d/m/Y - H:i') }} hrs</div>
                                    <div style="font-size: 12px; color: var(--color-text-muted);">
                                        Dr/a. {{ $consulta->medico->nombre_completo }} | 
                                        {{ ucfirst(str_replace('_', ' ', $consulta->tipo_consulta)) }}
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('consultas.show', $consulta->id) }}" class="btn btn-sm btn-outline-primary h-100 mt-2">Ver Detalle</a>
                        </div>
                        <div class="mt-3">
                            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--color-text-muted);">Motivo:</div>
                            <div style="font-size: 14px;">{{ $consulta->motivo }}</div>
                        </div>
                        <div class="mt-2">
                            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--color-text-muted);">Diagnóstico:</div>
                            <div style="font-size: 14px; font-weight: 500;">{{ $consulta->diagnostico }}</div>
                        </div>
                        @if($consulta->recetas && $consulta->recetas->count() > 0)
                        <div class="mt-3">
                            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--color-text-muted); mb-1">Recetas Emitidas:</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($consulta->recetas as $receta)
                                <a href="{{ route('recetas.imprimir', $receta->id) }}" target="_blank" class="badge bg-light text-dark border text-decoration-none p-2 d-flex align-items-center gap-1">
                                    <i data-lucide="file-text" style="width:14px;height:14px;color:var(--color-accent);"></i>
                                    {{ $receta->folio }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center p-5 text-muted">
                        <i data-lucide="inbox" style="width:48px;height:48px;margin-bottom:16px;color:#CBD5E1;"></i>
                        <p>Este paciente no tiene consultas registradas.</p>
                        @if(auth()->user()->rol === 'medico')
                        <a href="{{ route('consultas.create', ['expediente_id' => $expediente->id]) }}" class="btn btn-outline-primary mt-2">Registrar su primera consulta</a>
                        @endif
                    </div>
                @endforelse
            </div>
            @if($consultas->hasPages())
                <div class="card-footer bg-white border-top px-4 py-3">
                    {{ $consultas->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnArchivar = document.querySelector('.btn-archivar');
        if(btnArchivar) {
            btnArchivar.addEventListener('click', function() {
                Swal.fire({
                    title: '¿Archivar Expediente?',
                    text: 'El expediente dejará de aparecer en la lista principal, pero conservará todo su historial clínico.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: 'Sí, archivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formArchivar').submit();
                    }
                });
            });
        }

        const btnDesarchivar = document.querySelector('.btn-desarchivar');
        if(btnDesarchivar) {
            btnDesarchivar.addEventListener('click', function() {
                Swal.fire({
                    title: '¿Desarchivar Expediente?',
                    text: 'El paciente volverá a aparecer como activo en el sistema.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#22C55E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: 'Sí, restaurar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formDesarchivar').submit();
                    }
                });
            });
        }
    });
</script>
@endpush
