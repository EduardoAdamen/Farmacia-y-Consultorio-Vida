@extends('layouts.app')
@section('title', 'Reporte de Ventas')
@section('page-title', 'Reporte de Ventas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;font-weight:700;">Reporte de Ventas</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">
            Mostrando resultados del {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Volver
        </a>
        <a href="{{ route('reportes.ventas', array_merge(request()->all(), ['descargar' => 1])) }}" target="_blank" class="btn btn-danger d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;">
            <i data-lucide="file-down" style="width:16px;height:16px;"></i> Descargar PDF
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4 border-0" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div class="card-body p-3">
        <form action="{{ route('reportes.ventas') }}" method="GET" class="row g-2 align-items-end">
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
                    <i data-lucide="dollar-sign" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600; letter-spacing: 0.05em;">Ingresos Totales</div>
                    <div class="fw-bold" style="font-size: 20px;">${{ number_format($ingresosTotales, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 h-100" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfdf5; color: var(--color-success); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="shopping-cart" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600; letter-spacing: 0.05em;">Transacciones</div>
                    <div class="fw-bold" style="font-size: 20px;">{{ number_format($numTransacciones) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 h-100" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #f3e8ff; color: #9333ea; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="trending-up" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600; letter-spacing: 0.05em;">Ticket Promedio</div>
                    <div class="fw-bold" style="font-size: 20px;">${{ number_format($promedioPorVenta, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 h-100" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #fef3c7; color: var(--color-warning); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="calendar-days" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600; letter-spacing: 0.05em;">Mejor Día</div>
                    <div class="fw-bold" style="font-size: 18px; line-height: 1.2;">
                        @if($mejorDia)
                            {{ ucfirst($mejorDia->dia) }} <br><span style="font-size: 13px; color: var(--color-text-muted); font-weight: 500;">${{ number_format($mejorDia->total, 2) }}</span>
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Top 5 Productos --}}
<div class="card border-0" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <h6 class="mb-0 fw-bold">Top 5 Productos Más Vendidos</h6>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead style="background:#F8FAFC;font-size:12px;color:var(--color-text-muted);text-transform:uppercase;">
                <tr>
                    <th class="px-4 py-3">Producto</th>
                    <th class="py-3">Categoría</th>
                    <th class="py-3 text-center">Unidades</th>
                    <th class="py-3 text-end px-4">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                @forelse($top5Productos as $prod)
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td class="px-4 py-3 align-middle fw-bold" style="font-size: 13px;">{{ $prod->producto->nombre }}</td>
                    <td class="py-3 align-middle" style="font-size: 13px;">{{ $prod->producto->categoria->nombre }}</td>
                    <td class="py-3 align-middle text-center" style="font-size: 13px;">{{ $prod->unidades_vendidas }}</td>
                    <td class="py-3 align-middle text-end px-4 fw-bold" style="font-size: 13px; color: var(--color-success);">${{ number_format($prod->ingresos, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted" style="font-size: 13px;">No hay ventas registradas en este período.</td>
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
