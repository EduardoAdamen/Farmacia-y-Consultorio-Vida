@extends('layouts.app')
@section('title', 'Pedidos a Proveedores')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;">Pedidos a Proveedores</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">Administre las órdenes de compra de inventario.</p>
    </div>
    <a href="{{ route('pedidos.create') }}" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
        <i data-lucide="plus" style="width:16px;height:16px;"></i> Nuevo Pedido
    </a>
</div>

<form method="GET" action="{{ route('pedidos.index') }}" class="d-flex gap-2 align-items-center flex-wrap justify-content-end mb-4">

    {{-- Filtro estado --}}
    <select name="estado" class="form-select w-auto"
            style="background-color:#fff;border:1px solid var(--color-border);border-radius:10px;font-size:13px;padding:9px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <option value="">Todos los Estados</option>
        <option value="pendiente"  {{ request('estado') == 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
        <option value="recibido"   {{ request('estado') == 'recibido'   ? 'selected' : '' }}>Recibido (Pendiente Pago)</option>
        <option value="pagado"     {{ request('estado') == 'pagado'     ? 'selected' : '' }}>Pagado</option>
        <option value="cancelado"  {{ request('estado') == 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
    </select>

    {{-- Filtro proveedor --}}
    <select name="proveedor_id" class="form-select w-auto"
            style="background-color:#fff;border:1px solid var(--color-border);border-radius:10px;font-size:13px;padding:9px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.04);min-width:220px;">
        <option value="">Todos los Proveedores</option>
        @foreach($proveedores as $prov)
        <option value="{{ $prov->id }}" {{ request('proveedor_id') == $prov->id ? 'selected' : '' }}>
            {{ $prov->nombre_empresa }}
        </option>
        @endforeach
    </select>

    {{-- Botón filtrar --}}
    <button type="submit" class="btn btn-accent"
            style="font-size:13px;border-radius:10px;padding:9px 18px;font-weight:600;">
        Filtrar
    </button>

    {{-- Limpiar filtros --}}
    @if(request('estado') || request('proveedor_id'))
    <a href="{{ route('pedidos.index') }}" class="btn btn-ghost"
       style="border-radius:10px;padding:9px 14px;font-size:13px;">
        Limpiar
    </a>
    @endif
</form>


{{-- ── Tabla de Pedidos ───────────────────────────────────── --}}
<div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="margin-bottom:0;table-layout:fixed;width:100%;">
            <colgroup>
                <col style="width:18%">  {{-- Folio --}}
                <col style="width:22%">  {{-- Proveedor --}}
                <col style="width:13%">  {{-- Total --}}
                <col style="width:14%">  {{-- F. Estimada --}}
                <col style="width:13%">  {{-- Estado --}}
                <col style="width:20%">  {{-- Acciones --}}
            </colgroup>
            <thead style="background:#F8FAFC;border-bottom:1px solid var(--color-border);font-size:12.5px;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.5px;">
                <tr>
                    <th class="px-4 py-3" style="font-weight:600;border:none;">Folio</th>
                    <th class="py-3" style="font-weight:600;border:none;">Proveedor</th>
                    <th class="py-3 text-center" style="font-weight:600;border:none;">Total</th>
                    <th class="py-3 text-center" style="font-weight:600;border:none;">F. Estimada</th>
                    <th class="py-3 text-center" style="font-weight:600;border:none;">Estado</th>
                    <th class="py-3 text-end px-4" style="font-weight:600;border:none;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                <tr style="border-bottom:1px solid var(--color-border);">
                    {{-- Folio + fecha --}}
                    <td class="px-4 py-3" style="border:none;">
                        <div style="font-weight:600;font-size:13.5px;">{{ $pedido->folio }}</div>
                        <div style="font-size:11px;color:var(--color-text-muted);">
                            {{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}
                        </div>
                    </td>

                    {{-- Proveedor --}}
                    <td class="py-3" style="border:none;">
                        <div style="font-size:13px;font-weight:500;">{{ $pedido->proveedor->nombre_empresa }}</div>
                    </td>

                    {{-- Total --}}
                    <td class="py-3 text-center" style="border:none;">
                        <div style="font-size:14px;font-weight:700;font-family:'Outfit',sans-serif;color:var(--color-accent);">
                            ${{ number_format($pedido->monto_total, 2) }}
                        </div>
                    </td>

                    {{-- Fecha estimada --}}
                    <td class="py-3 text-center" style="border:none;font-size:13px;color:var(--color-text-muted);">
                        {{ $pedido->fecha_estimada ? \Carbon\Carbon::parse($pedido->fecha_estimada)->format('d/m/Y') : '—' }}
                    </td>

                    {{-- Estado --}}
                    <td class="py-3 text-center" style="border:none;">
                        @if($pedido->estado == 'pendiente')
                            <span style="font-size:12px;color:#D97706;background:#FEF3C7;padding:4px 10px;border-radius:20px;font-weight:600;">Pendiente</span>
                        @elseif($pedido->estado == 'recibido')
                            <span style="font-size:12px;color:var(--color-info);background:#E0F2FE;padding:4px 10px;border-radius:20px;font-weight:600;">Recibido</span>
                        @elseif($pedido->estado == 'pagado')
                            <span style="font-size:12px;color:var(--color-success);background:#DCFCE7;padding:4px 10px;border-radius:20px;font-weight:600;">Pagado</span>
                        @elseif($pedido->estado == 'cancelado')
                            <span style="font-size:12px;color:var(--color-danger);background:#FEE2E2;padding:4px 10px;border-radius:20px;font-weight:600;">Cancelado</span>
                        @endif
                    </td>

                    {{-- Acciones --}}
                    <td class="py-3 px-4 text-end" style="border:none;">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('pedidos.show', $pedido->id) }}"
                               class="btn btn-ghost btn-sm d-flex align-items-center gap-1"
                               style="font-size:12px;border-radius:6px;padding:5px 10px;">
                                <i data-lucide="eye" style="width:14px;height:14px;"></i> Detalle
                            </a>
                            @if($pedido->estado === 'pendiente')
                            <a href="{{ route('pedidos.edit', $pedido->id) }}"
                               class="btn btn-ghost btn-sm d-flex align-items-center gap-1"
                               style="font-size:12px;border-radius:6px;padding:5px 10px;">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i> Editar
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5" style="color:var(--color-text-muted);font-size:13px;border:none;">
                        <i data-lucide="inbox" style="width:32px;height:32px;display:block;margin:0 auto 8px;opacity:0.4;"></i>
                        No hay pedidos acordes a su búsqueda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 d-flex justify-content-center border-top" style="border-color:var(--color-border)!important;">
            {{ $pedidos->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
