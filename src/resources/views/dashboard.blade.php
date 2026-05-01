@extends('layouts.app')
@section('title', '¡Hola, ' . auth()->user()->nombre_completo . '!')

@section('content')
<div class="mb-4">
    <h4 class="mb-1" style="font-family: 'Outfit', sans-serif; font-weight: normal;">¡Hola, <span class="fw-bold">{{ auth()->user()->nombre_completo }}</span>!</h4>
    <p style="font-size:13px;color:var(--color-text-muted);margin:0;text-transform:capitalize;">
        {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
    </p>
</div>

{{-- ─── Tarjetas de indicadores por rol (RF-12) ─────────────────────────── --}}
@if(in_array(auth()->user()->rol, ['dueno', 'vendedor']))
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.07em;color:var(--color-text-muted);font-weight:600;margin-bottom:6px;">Ventas Hoy</div>
                    <div style="font-size:26px;font-weight:700;font-family:'Outfit',sans-serif;line-height:1;">
                        ${{ number_format($ventasHoy ?? 0, 2) }}
                    </div>
                </div>
                <div style="width:40px;height:40px;background:#F0FDFA;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="dollar-sign" style="width:20px;height:20px;color:var(--color-accent);"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.07em;color:var(--color-text-muted);font-weight:600;margin-bottom:6px;">Transacciones</div>
                    <div style="font-size:26px;font-weight:700;font-family:'Outfit',sans-serif;line-height:1;">
                        {{ $transaccionesHoy ?? 0 }}
                    </div>
                </div>
                <div style="width:40px;height:40px;background:#F0F9FF;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="receipt" style="width:20px;height:20px;color:var(--color-info);"></i>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->rol === 'dueno')
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.07em;color:var(--color-text-muted);font-weight:600;margin-bottom:6px;">Total Productos</div>
                    <div style="font-size:26px;font-weight:700;font-family:'Outfit',sans-serif;line-height:1;">
                        {{ number_format($totalProductos) }}
                    </div>
                </div>
                <div style="width:40px;height:40px;background:#FFFBEB;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="package" style="width:20px;height:20px;color:var(--color-warning);"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.07em;color:var(--color-text-muted);font-weight:600;margin-bottom:6px;">Alertas Activas</div>
                    <div style="font-size:26px;font-weight:700;font-family:'Outfit',sans-serif;line-height:1;color:{{ $totalAlertas > 0 ? 'var(--color-danger)' : 'var(--color-success)' }};">
                        {{ $totalAlertas }}
                    </div>
                </div>
                <div style="width:40px;height:40px;background:#FFF1F2;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="alert-circle" style="width:20px;height:20px;color:var(--color-danger);"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ─── Indicadores para Médico ─────────────────────────────────────────── --}}
@if(auth()->user()->rol === 'medico')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.07em;color:var(--color-text-muted);font-weight:600;margin-bottom:6px;">Citas Hoy</div>
                    <div style="font-size:26px;font-weight:700;font-family:'Outfit',sans-serif;line-height:1;">{{ $citasHoy ?? 0 }}</div>
                </div>
                <div style="width:40px;height:40px;background:#F0FDFA;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="calendar-days" style="width:20px;height:20px;color:var(--color-accent);"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:0.07em;color:var(--color-text-muted);font-weight:600;margin-bottom:6px;">Consultas Hoy</div>
                    <div style="font-size:26px;font-weight:700;font-family:'Outfit',sans-serif;line-height:1;">{{ $consultasHoy ?? 0 }}</div>
                </div>
                <div style="width:40px;height:40px;background:#F0F9FF;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="stethoscope" style="width:20px;height:20px;color:var(--color-info);"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ─── Paneles de alertas de inventario ───────────────────────────────── --}}
<div class="row g-3">
    {{-- Stock Crítico (RF-13) --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
                <span class="d-flex align-items-center gap-2" style="font-size:14px;">
                    <i data-lucide="alert-triangle" style="width:16px;height:16px;color:var(--color-danger);"></i>
                    Stock Crítico
                </span>
                @if(Route::has('productos.index'))
                <a href="{{ route('productos.index', ['filtro' => 'critico']) }}" style="font-size:12px;color:var(--color-accent);text-decoration:none;font-weight:600;">Ver todos →</a>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($stockCritico as $producto)
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                    <div>
                        <div style="font-size:13px;font-weight:600;">{{ $producto->nombre }}</div>
                        <div style="font-size:12px;color:var(--color-text-muted);">{{ $producto->categoria->nombre }}</div>
                    </div>
                    <span class="badge badge-critico rounded-pill px-3 py-1">{{ $producto->stock_total }} uds.</span>
                </div>
                @empty
                <div class="px-4 py-5 text-center">
                    <i data-lucide="check-circle" style="width:28px;height:28px;color:var(--color-success);"></i>
                    <div style="font-size:13px;color:var(--color-text-muted);margin-top:8px;">Sin alertas de stock crítico</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Próximos a Vencer (RF-14) --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
                <span class="d-flex align-items-center gap-2" style="font-size:14px;">
                    <i data-lucide="calendar-x" style="width:16px;height:16px;color:var(--color-warning);"></i>
                    Próximos a Vencer
                </span>
                @if(Route::has('productos.index'))
                <a href="{{ route('productos.index', ['filtro' => 'vencer']) }}" style="font-size:12px;color:var(--color-accent);text-decoration:none;font-weight:600;">Ver reporte →</a>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($lotesProximosAVencer as $lote)
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                    <div>
                        <div style="font-size:13px;font-weight:600;">{{ $lote->producto->nombre }}</div>
                        <div style="font-size:12px;color:var(--color-text-muted);">Lote: <code>{{ $lote->numero_lote }}</code></div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-pendiente rounded-pill px-2">
                            {{ $lote->fecha_vencimiento->diffInDays(now()) }} días
                        </span>
                        <div style="font-size:11px;color:var(--color-text-muted);margin-top:2px;">
                            {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-4 py-5 text-center">
                    <i data-lucide="check-circle" style="width:28px;height:28px;color:var(--color-success);"></i>
                    <div style="font-size:13px;color:var(--color-text-muted);margin-top:8px;">Sin productos próximos a vencer</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
