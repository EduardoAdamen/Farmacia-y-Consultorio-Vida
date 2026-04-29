@extends('layouts.app')
@section('title', 'Comprobante de Venta')

@push('styles')
<style>
    @media print {
        body * { visibility: hidden; }
        #comprobante, #comprobante * { visibility: visible; }
        #comprobante { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
        #sidebar { display: none !important; }
        #topbar { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;">Detalle de Venta</h5>
    </div>
</div>

<div class="row">
    <div class="col-md-3 d-grid gap-2 mb-3 no-print">
        <button type="button" class="btn btn-accent d-flex justify-content-center align-items-center gap-2 mb-2" onclick="window.print()">
            <i data-lucide="printer" style="width:18px;height:18px;"></i>
            Imprimir Comprobante
        </button>

        @if(auth()->user()->rol === 'dueno' && $venta->estado === 'completada')
        <form method="POST" action="{{ route('ventas.cancelar', $venta->id) }}" onsubmit="return confirm('¿Está seguro de querer cancelar esta venta? El stock se revertirá de forma permanente.');">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-outline-danger w-100 d-flex justify-content-center align-items-center gap-2">
                <i data-lucide="x-circle" style="width:18px;height:18px;"></i>
                Cancelar Venta (Revertir Stock)
            </button>
        </form>
        @endif

        <a href="{{ route('ventas.index') }}" class="btn btn-link text-muted mt-3">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Nueva Venta
        </a>
    </div>

    <div class="col-md-9 pb-5">
        <div class="card p-5" id="comprobante" style="max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
            <div class="text-center mb-4">
                <div class="mb-2">
                    <i data-lucide="cross" style="color:var(--color-accent);width:32px;height:32px;"></i>
                </div>
                <h3 class="fw-bold m-0" style="color:var(--color-primary);">FARMACIA VIDA</h3>
                <div class="text-muted" style="font-size:12px;">Comprobante de Venta</div>
            </div>

            <div class="row mb-4 bg-light p-3 rounded mx-1">
                <div class="col-6">
                    <div style="font-size:11px;color:var(--color-text-muted);text-transform:uppercase;">Folio de Venta</div>
                    <code class="folio" style="font-size:14px;color:var(--color-text-main);">{{ $venta->folio }}</code>
                </div>
                <div class="col-6 text-end">
                    <div style="font-size:11px;color:var(--color-text-muted);text-transform:uppercase;">Fecha y Hora</div>
                    <div style="font-size:13px;font-weight:600;">{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y h:i A') }}</div>
                </div>
            </div>

            <div class="mb-4">
                <div style="font-size:11px;color:var(--color-text-muted);text-transform:uppercase;" class="mb-1 border-bottom pb-1">Vendedor</div>
                <div style="font-size:14px;font-weight:500;">
                    <i data-lucide="user" style="width:14px;height:14px;color:var(--color-accent);margin-right:4px;"></i>
                    {{ $venta->vendedor->nombre_completo }}
                </div>
            </div>

            @if($venta->estado === 'cancelada')
            <div class="alert flex-col mb-4 bg-danger text-white text-center p-2 rounded">
                <i data-lucide="ban" style="width:20px;height:20px;margin-bottom:4px;"></i>
                <div class="fw-bold" style="letter-spacing:1px;text-transform:uppercase;">VENTA CANCELADA</div>
            </div>
            @endif

            <table class="table mb-4 border" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 p-2 border-bottom-0">Cant.</th>
                        <th class="p-2 border-bottom-0">Producto</th>
                        <th class="p-2 border-bottom-0 text-end pe-3">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->detalles as $detalle)
                    <tr>
                        <td class="ps-3">{{ $detalle->cantidad }}</td>
                        <td>
                            <div class="fw-bold" style="color:var(--color-text-main);">{{ $detalle->producto->nombre }}</div>
                            @if($detalle->descuento_manual > 0)
                                <div style="font-size:11px;color:var(--color-accent);">Incluye {{ $detalle->descuento_manual }}% descuento</div>
                            @endif
                            @if($detalle->receta_id)
                                <div style="font-size:11px;color:var(--color-text-muted);">
                                    <i data-lucide="file-text" style="width:10px;height:10px;margin-right:2px;color:var(--color-warning);"></i>
                                    Receta: <code style="font-size:10px;">{{ $detalle->receta->folio }}</code>
                                </div>
                            @endif
                        </td>
                        <td class="text-end pe-3 font-monospace" style="vertical-align:bottom;">
                            @php
                                $sub = ($detalle->precio_unitario * $detalle->cantidad) * (1 - $detalle->descuento_manual/100);
                            @endphp
                            ${{ number_format($sub, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row m-0 mb-4 justify-content-end p-0">
                <div class="col-8 p-0">
                    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border">
                        <span style="font-size:13px;color:var(--color-text-muted);text-transform:uppercase;font-weight:600;">Total a Pagar</span>
                        <span class="fs-4 fw-bold font-monospace" style="font-family:'Outfit',sans-serif;color:var(--color-primary);">
                            ${{ number_format($venta->total, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="text-center text-muted mt-5 pt-3 border-top" style="font-size:11px;">
                <i data-lucide="heart" style="width:14px;height:14px;color:var(--color-danger);margin-right:4px;"></i>
                Gracias por su preferencia<br>
                Conserve este ticket para cualquier aclaración o devolución.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
@endpush