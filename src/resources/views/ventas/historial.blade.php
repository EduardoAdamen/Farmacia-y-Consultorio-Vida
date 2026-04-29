@extends('layouts.app')
@section('title', 'Historial de Ventas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;">Historial de Ventas</h5>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header p-3">
        Filtrar Ventas
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('ventas.historial') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="completada" {{ request('estado') === 'completada' ? 'selected' : '' }}>Completada</option>
                    <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-accent w-100"><i data-lucide="filter" style="width:16px;height:16px;"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Folio</th>
                        <th>Fecha y Hora</th>
                        <th>Vendedor</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                    <tr>
                        <td>
                            <a href="{{ route('ventas.show', $venta->id) }}" style="text-decoration:none;">
                                <code class="folio">{{ $venta->folio }}</code>
                            </a>
                        </td>
                        <td style="font-size:13px;">{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y H:i A') }}</td>
                        <td style="font-size:13px;">{{ $venta->vendedor->nombre_completo }}</td>
                        <td class="font-monospace fw-bold" style="font-size:14px;">${{ number_format($venta->total, 2) }}</td>
                        <td>
                            @if($venta->estado === 'completada')
                                <span class="badge bg-success" style="font-size:11px;">Completada</span>
                            @else
                                <span class="badge bg-danger" style="font-size:11px;">Cancelada</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('ventas.show', $venta->id) }}" class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                <i data-lucide="eye" style="width:16px;height:16px;"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i data-lucide="inbox" style="width:32px;height:32px;opacity:0.5;" class="mb-2"></i>
                            <div>No se encontraron ventas para el filtro seleccionado.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ventas->hasPages())
    <div class="card-footer border-top p-3 d-flex justify-content-end">
        {{ $ventas->links() }}
    </div>
    @endif
</div>
@endsection