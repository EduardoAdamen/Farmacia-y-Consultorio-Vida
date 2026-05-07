@extends('layouts.app')
@section('title', 'Agenda de Citas')
@section('page-title', 'Agenda de Citas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;">Agenda de Citas</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">Gestione los horarios y pacientes programados.</p>
    </div>
    <a href="{{ route('citas.create') }}" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
        <i data-lucide="plus" style="width:16px;height:16px;"></i> Nueva Cita
    </a>
</div>

<div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;overflow:hidden;">
    <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
        <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0 fw-bold">
                Semana del {{ $inicioSemana->format('d/m') }} al {{ $finSemana->format('d/m') }}
            </h5>
            <div class="btn-group">
                <a href="{{ route('citas.index', ['fecha' => $inicioSemana->copy()->subWeek()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary">
                    <i data-lucide="chevron-left" style="width:16px;height:16px;"></i>
                </a>
                <a href="{{ route('citas.index', ['fecha' => now()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary">Hoy</a>
                <a href="{{ route('citas.index', ['fecha' => $inicioSemana->copy()->addWeek()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary">
                    <i data-lucide="chevron-right" style="width:16px;height:16px;"></i>
                </a>
            </div>
            
            @if(auth()->user()->rol !== 'medico')
            <form action="{{ route('citas.index') }}" method="GET" class="d-flex align-items-center ms-3">
                <input type="hidden" name="fecha" value="{{ $fechaReferencia->toDateString() }}">
                <select name="medico_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos los médicos</option>
                    @foreach($medicos as $m)
                        <option value="{{ $m->id }}" {{ request('medico_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->nombre_completo }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>
    </div>
    
    <div class="card-body p-0" style="overflow-x: auto;">
        <table class="table table-bordered mb-0" style="min-width: 900px; font-size: 13px;">
            <thead style="background: var(--color-secondary);">
                <tr>
                    <th style="width: 80px; text-align: center; background: var(--color-secondary);">Hora</th>
                    @foreach($diasSemana as $dia)
                        <th class="text-center" style="background: {{ $dia->isToday() ? '#e0f2fe' : 'var(--color-secondary)' }}">
                            <div style="font-weight: 700; font-family: 'Outfit', sans-serif;">{{ ucfirst($dia->isoFormat('dddd')) }}</div>
                            <div style="font-size: 11px; color: var(--color-text-muted);">{{ $dia->format('d/m/Y') }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($horas as $hora)
                    <tr>
                        <td class="text-center fw-bold align-middle" style="background: var(--color-secondary); color: var(--color-text-muted);">
                            {{ $hora }}
                        </td>
                        @foreach($diasSemana as $dia)
                            <td class="p-1" style="height: 60px; vertical-align: top; background: {{ $dia->isToday() ? '#f0f9ff' : '#fff' }}">
                                @php
                                    $citasEnCasilla = $citas->filter(function($c) use ($dia, $hora) {
                                        return $c->fecha->toDateString() === $dia->toDateString() &&
                                               \Carbon\Carbon::parse($c->hora)->format('H:i') === $hora;
                                    });
                                @endphp

                                @foreach($citasEnCasilla as $cita)
                                    @php
                                        $bgColor = 'var(--color-accent)';
                                        if($cita->estado === 'completada') $bgColor = 'var(--color-success)';
                                        if($cita->estado === 'reprogramada') $bgColor = 'var(--color-warning)';
                                    @endphp
                                    <a href="{{ route('citas.show', $cita->id) }}" class="d-block rounded p-1 mb-1 text-decoration-none" style="background: {{ $bgColor }}; color: {{ $cita->estado === 'reprogramada' ? '#0F172A' : '#fff' }}; font-size: 11px; line-height: 1.2;">
                                        <div class="fw-bold">{{ $cita->nombre_temporal ?: $cita->expediente->nombre_completo }}</div>
                                        @if(auth()->user()->rol !== 'medico')
                                        <div style="opacity: 0.8; font-size: 10px;">{{ $cita->medico->nombre_completo }}</div>
                                        @endif
                                    </a>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
