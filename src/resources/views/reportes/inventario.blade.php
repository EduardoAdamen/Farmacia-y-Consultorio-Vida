@extends('layouts.app')
@section('title', 'Reporte de Inventario')
@section('page-title', 'Reporte de Inventario')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;font-weight:700;">Estado del Inventario</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">
            Valoración y rotación de productos al {{ now()->format('d/m/Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Volver
        </a>
        <a href="{{ route('reportes.inventario', array_merge(request()->all(), ['descargar' => 1])) }}" target="_blank" class="btn btn-danger d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;">
            <i data-lucide="file-down" style="width:16px;height:16px;"></i> Descargar PDF
        </a>
    </div>
</div>

{{-- Métricas Generales --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 h-100" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div style="width: 56px; height: 56px; border-radius: 12px; background: #ecfdf5; color: var(--color-success); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="wallet" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase" style="font-size: 12px; font-weight: 600; letter-spacing: 0.05em;">Valoración Total del Stock (Costo)</div>
                    <div class="fw-bold" style="font-size: 24px; color: var(--color-success);">${{ number_format($valoracionTotal, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 h-100 bg-primary text-white" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Rotación de Inventario</h5>
                        <p class="mb-0 text-white-50" style="font-size: 13px;">Revisa constantemente los productos de baja rotación para evitar mermas por caducidad.</p>
                    </div>
                    <i data-lucide="arrow-right-left" style="width: 40px; height: 40px; opacity: 0.8;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Top 10 Más Vendidos --}}
    <div class="col-md-6">
        <div class="card border-0 h-100" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold" style="color: var(--color-success);"><i data-lucide="trending-up" style="width:16px;height:16px;margin-right:8px;"></i>Top 10 Productos Más Vendidos</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead style="background:#F8FAFC;font-size:12px;color:var(--color-text-muted);text-transform:uppercase;">
                        <tr>
                            <th class="px-4 py-3">Producto</th>
                            <th class="py-3 text-center">Stock Actual</th>
                            <th class="py-3 text-end px-4">Uds. Vendidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($masVendidos as $prod)
                        <tr style="border-bottom:1px solid var(--color-border);">
                            <td class="px-4 py-3 align-middle" style="font-size: 13px;">
                                <div class="fw-bold">{{ $prod->producto->nombre }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $prod->producto->categoria->nombre }}</div>
                            </td>
                            <td class="py-3 align-middle text-center" style="font-size: 13px;">
                                <span class="badge bg-{{ $prod->producto->stock_total <= $prod->producto->stock_minimo ? 'danger' : 'secondary' }}">
                                    {{ $prod->producto->stock_total }}
                                </span>
                            </td>
                            <td class="py-3 align-middle text-end px-4 fw-bold" style="font-size: 13px; color: var(--color-success);">{{ $prod->total_vendido }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted" style="font-size: 13px;">No hay datos de ventas registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top 10 Menos Vendidos --}}
    <div class="col-md-6">
        <div class="card border-0 h-100" style="border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold" style="color: var(--color-danger);"><i data-lucide="trending-down" style="width:16px;height:16px;margin-right:8px;"></i>Productos de Baja Rotación</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead style="background:#F8FAFC;font-size:12px;color:var(--color-text-muted);text-transform:uppercase;">
                        <tr>
                            <th class="px-4 py-3">Producto</th>
                            <th class="py-3 text-center">Stock Actual</th>
                            <th class="py-3 text-end px-4">Valuación ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menosVendidos as $prod)
                        <tr style="border-bottom:1px solid var(--color-border);">
                            <td class="px-4 py-3 align-middle" style="font-size: 13px;">
                                <div class="fw-bold">{{ $prod->nombre }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $prod->categoria->nombre }}</div>
                            </td>
                            <td class="py-3 align-middle text-center" style="font-size: 13px;">
                                <span class="badge bg-secondary">{{ $prod->stock_total }}</span>
                            </td>
                            <td class="py-3 align-middle text-end px-4 fw-bold" style="font-size: 13px;">
                                ${{ number_format($prod->stock_total * $prod->precio_compra, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted" style="font-size: 13px;">No hay productos registrados en el inventario.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
