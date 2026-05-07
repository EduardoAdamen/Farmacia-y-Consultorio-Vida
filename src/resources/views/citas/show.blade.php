@extends('layouts.app')
@section('title', 'Detalle de Cita')
@section('page-title', 'Detalle de Cita')

@section('content')
<div class="card max-w-2xl mx-auto" style="max-width: 800px;">
    <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Cita #{{ $cita->id }}</h5>
        <span class="badge badge-{{ $cita->estado }}">{{ ucfirst($cita->estado) }}</span>
    </div>
    <div class="card-body p-4">
        <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Paciente</div>
                <div class="fw-bold" style="font-size: 16px;">{{ $cita->nombre_temporal ?: $cita->expediente->nombre_completo }}</div>
                @if($cita->expediente)
                    <a href="{{ route('expedientes.show', $cita->expediente->id) }}" class="d-inline-flex align-items-center gap-1 mt-1 text-decoration-none" style="font-size: 12px; color: var(--color-accent);">
                        <i data-lucide="folder-open" style="width:14px;height:14px;"></i> Ver expediente
                    </a>
                @else
                    <span class="badge bg-secondary mt-1">Paciente sin expediente</span>
                @endif
            </div>
            <div class="col-md-6">
                <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Médico Asignado</div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:28px;height:28px;border-radius:50%;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;">
                        {{ strtoupper(substr($cita->medico->nombre_completo, 0, 1)) }}
                    </div>
                    <span class="fw-bold">{{ $cita->medico->nombre_completo }}</span>
                </div>
            </div>
        </div>

        <div class="p-3 rounded mb-4" style="background: var(--color-secondary);">
            <div class="row">
                <div class="col-md-6 border-end">
                    <div class="d-flex align-items-center gap-2 mb-1" style="color: var(--color-text-muted); font-size: 12px;">
                        <i data-lucide="calendar" style="width:16px;height:16px;"></i> Fecha
                    </div>
                    <div class="fw-bold" style="font-size: 15px;">{{ $cita->fecha->format('d/m/Y') }}</div>
                </div>
                <div class="col-md-6 ps-md-4">
                    <div class="d-flex align-items-center gap-2 mb-1" style="color: var(--color-text-muted); font-size: 12px;">
                        <i class="lucide-clock" data-lucide="clock" style="width:16px;height:16px;"></i> Hora
                    </div>
                    <div class="fw-bold" style="font-size: 15px;">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }} hrs</div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Motivo o Notas</div>
            <div class="p-3 border rounded" style="background: #fff; min-height: 80px;">
                {{ $cita->motivo }}
            </div>
        </div>
        
        @if($cita->estado === 'programada' && auth()->user()->rol === 'medico')
            <div class="alert alert-info d-flex align-items-center justify-content-between">
                <div>
                    <i data-lucide="stethoscope" style="width:18px;height:18px; margin-right: 8px;"></i>
                    <strong>¿El paciente ya llegó?</strong> Inicia la consulta ahora.
                </div>
                <a href="#" class="btn btn-sm btn-info text-white">Registrar Consulta</a> {{-- Cambiar a la ruta de consulta más adelante --}}
            </div>
        @endif

    </div>
    <div class="card-footer bg-white py-3 px-4 d-flex justify-content-between">
        <a href="{{ route('citas.index', ['fecha' => $cita->fecha->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">
            <i data-lucide="arrow-left" style="width:16px;height:16px;" class="me-1"></i> Volver a la Agenda
        </a>
        
        @if(in_array($cita->estado, ['programada', 'reprogramada']))
            <a href="{{ route('citas.edit', $cita->id) }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                <i data-lucide="edit" style="width:16px;height:16px;"></i> Modificar / Cancelar
            </a>
        @endif
    </div>
</div>
@endsection
