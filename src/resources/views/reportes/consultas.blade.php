@extends('layouts.app')
@section('title', 'Reporte de Consultas Médicas')
@section('page-title', 'Reporte de Consultas Médicas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;font-weight:700;">Consultas Médicas</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">
            Mostrando resultados del {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Volver
        </a>
        <a href="{{ route('reportes.consultas', array_merge(request()->all(), ['descargar' => 1])) }}" target="_blank" class="btn btn-danger d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;">
            <i data-lucide="file-down" style="width:16px;height:16px;"></i> Descargar PDF
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4 border-0" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div class="card-body p-3">
        <form action="{{ route('reportes.consultas') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" style="font-size: 12px; font-weight: 600;">Período</label>
                <select name="periodo" id="periodo" class="form-select form-select-sm">
                    <option value="dia" {{ request('periodo') == 'dia' ? 'selected' : '' }}>Hoy</option>
                    <option value="semana" {{ request('periodo') == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                    <option value="mes" {{ request('periodo') == 'mes' ? 'selected' : '' }}>Este Mes</option>
                    <option value="rango" {{ request('periodo') == 'rango' ? 'selected' : '' }}>Rango Personalizado</option>
                </select>
            </div>
            <div class="col-md-3 div-rango" style="display: {{ request('periodo') == 'rango' ? 'block' : 'none' }};">
                <label class="form-label" style="font-size: 12px; font-weight: 600;">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="{{ request('fecha_inicio') }}">
            </div>
            <div class="col-md-3 div-rango" style="display: {{ request('periodo') == 'rango' ? 'block' : 'none' }};">
                <label class="form-label" style="font-size: 12px; font-weight: 600;">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm" value="{{ request('fecha_fin') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Generar</button>
            </div>
        </form>
    </div>
</div>

{{-- Métricas --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 h-100" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: var(--color-primary); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="users" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600; letter-spacing: 0.05em;">Total Atendidos</div>
                    <div class="fw-bold" style="font-size: 20px;">{{ number_format($totalPacientes) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 h-100" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfdf5; color: var(--color-success); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="user-plus" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600; letter-spacing: 0.05em;">Primera Vez</div>
                    <div class="fw-bold" style="font-size: 20px;">{{ number_format($primeraVez) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 h-100" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #fef3c7; color: var(--color-warning); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="repeat" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600; letter-spacing: 0.05em;">Seguimiento</div>
                    <div class="fw-bold" style="font-size: 20px;">{{ number_format($seguimiento) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 h-100" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="alert-circle" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600; letter-spacing: 0.05em;">Urgencias / Ingresos</div>
                    <div class="fw-bold" style="font-size: 16px; line-height: 1.2;">
                        {{ $urgencias }} <br>
                        <span style="font-size: 13px; color: var(--color-success);">${{ number_format($ingresosTotales, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Historial de Consultas --}}
<div class="card border-0" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <h6 class="mb-0 fw-bold">Listado de Consultas Médicas</h6>
    </div>
    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="table mb-0">
            <thead style="background:#F8FAFC;font-size:12px;color:var(--color-text-muted);text-transform:uppercase; position: sticky; top: 0; z-index: 1;">
                <tr>
                    <th class="px-4 py-3">Fecha y Hora</th>
                    <th class="py-3">Paciente</th>
                    <th class="py-3">Médico</th>
                    <th class="py-3 text-center">Tipo</th>
                    <th class="py-3 text-center">Estado Pago</th>
                    <th class="py-3 text-end px-4">Costo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($listadoConsultas as $c)
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td class="px-4 py-3 align-middle" style="font-size: 13px;">{{ $c->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td class="py-3 align-middle fw-bold" style="font-size: 13px;">{{ $c->expediente->nombre_completo }}</td>
                    <td class="py-3 align-middle text-muted" style="font-size: 13px;">{{ $c->medico->nombre_completo }}</td>
                    <td class="py-3 align-middle text-center" style="font-size: 12px;">
                        @if($c->tipo_consulta === 'primera_vez')
                            <span class="badge bg-success bg-opacity-10 text-success">Primera Vez</span>
                        @elseif($c->tipo_consulta === 'seguimiento')
                            <span class="badge bg-warning bg-opacity-10 text-warning">Seguimiento</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger">Urgencia</span>
                        @endif
                    </td>
                    <td class="py-3 align-middle text-center" style="font-size: 12px;">
                        <span class="badge bg-{{ $c->estado_pago == 'pagado' ? 'success' : ($c->estado_pago == 'pendiente' ? 'warning text-dark' : 'info') }}">{{ ucfirst($c->estado_pago) }}</span>
                    </td>
                    <td class="py-3 align-middle text-end px-4 fw-bold" style="font-size: 13px;">${{ number_format($c->costo, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted" style="font-size: 13px;">No hay consultas registradas en este período.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('periodo').addEventListener('change', function() {
        const divRango = document.querySelectorAll('.div-rango');
        if (this.value === 'rango') {
            divRango.forEach(el => el.style.display = 'block');
        } else {
            divRango.forEach(el => el.style.display = 'none');
        }
    });
</script>
@endpush
@endsection
