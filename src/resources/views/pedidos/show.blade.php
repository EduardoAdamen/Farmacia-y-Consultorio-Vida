@extends('layouts.app')
@section('title', 'Detalle de Pedido')

@section('content')
<div class="mb-4 mt-2">
    <h5 class="mb-0 fw-bold" style="font-family: 'Outfit', sans-serif;">Detalle de Pedido: {{ $pedido->folio }}</h5>
</div>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('pedidos.index') }}" class="text-decoration-none" style="font-size:13px;color:var(--color-text-muted);display:inline-flex;align-items:center;gap:6px;font-weight:500;">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Volver a pedidos
    </a>
    <div>
        @if($pedido->estado == 'pendiente')
            <span class="badge badge-pendiente px-3 py-2" style="font-size:13px;">ESTADO: PENDIENTE</span>
        @elseif($pedido->estado == 'recibido')
            <span class="badge badge-recibido px-3 py-2" style="font-size:13px;">ESTADO: RECIBIDO (PENDIENTE DE PAGO)</span>
        @elseif($pedido->estado == 'pagado')
            <span class="badge badge-pagado px-3 py-2" style="font-size:13px;">ESTADO: PAGADO</span>
        @elseif($pedido->estado == 'cancelado')
            <span class="badge badge-cancelado px-3 py-2" style="font-size:13px;">ESTADO: CANCELADO</span>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Información General -->
    <div class="col-md-4">
        <div class="card h-100" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
                <h6 style="font-family:'Outfit',sans-serif;font-weight:700;margin:0;">Información del Pedido</h6>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <ul class="list-unstyled mb-0" style="font-size:13.5px;">
                    <li class="mb-3">
                        <div style="color:var(--color-text-muted);font-size:12px;font-weight:600;text-transform:uppercase;">Proveedor</div>
                        <div style="font-weight:600;">{{ $pedido->proveedor->nombre_empresa }}</div>
                    </li>
                    <li class="mb-3">
                        <div style="color:var(--color-text-muted);font-size:12px;font-weight:600;text-transform:uppercase;">Fecha de Creación</div>
                        <div>{{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}</div>
                    </li>
                    <li class="mb-3">
                        <div style="color:var(--color-text-muted);font-size:12px;font-weight:600;text-transform:uppercase;">Fecha Estimada de Entrega</div>
                        <div>{{ $pedido->fecha_estimada ? \Carbon\Carbon::parse($pedido->fecha_estimada)->format('d/m/Y') : 'No especificada' }}</div>
                    </li>
                    <li class="mb-3">
                        <div style="color:var(--color-text-muted);font-size:12px;font-weight:600;text-transform:uppercase;">Monto Total Estimado</div>
                        <div style="font-weight:700;font-size:16px;color:var(--color-accent);">${{ number_format($pedido->monto_total, 2) }}</div>
                    </li>
                    @if($pedido->fecha_pago)
                    <li>
                        <div style="color:var(--color-text-muted);font-size:12px;font-weight:600;text-transform:uppercase;">Fecha de Pago</div>
                        <div style="color:var(--color-secondary);font-weight:600;">{{ \Carbon\Carbon::parse($pedido->fecha_pago)->format('d/m/Y') }}</div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- Acciones según estado y Formulario de Recepción -->
    <div class="col-md-8">
        <div class="card h-100" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <h6 style="font-family:'Outfit',sans-serif;font-weight:700;margin:0;">Detalles del Pedido</h6>
                
                @if($pedido->estado == 'pendiente')
                <form action="{{ route('pedidos.cancelar', $pedido->id) }}" method="POST" onsubmit="return confirm('¿Confirma la cancelación de este pedido?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                        <i data-lucide="x-circle" style="width:14px;height:14px;"></i> Cancelar Pedido
                    </button>
                </form>
                @endif
            </div>

            <div class="card-body px-4 pb-4">
                @if($pedido->estado == 'pendiente')
                <form action="{{ route('pedidos.recibir', $pedido->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" style="font-size:13px;">
                            <thead class="bg-light text-muted uppercase">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cant. Solicitada</th>
                                    <th>Cant. Recibida</th>
                                    <th>Costo Real / u</th>
                                    <th>Lote y Vencimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedido->detalles as $detalle)
                                <tr>
                                    <td>{{ $detalle->producto->nombre }}</td>
                                    <td class="text-center">{{ $detalle->cantidad_solicitada }}</td>
                                    <td>
                                        <input type="number" name="detalles[{{ $detalle->id }}][cantidad_recibida]" class="form-control form-control-sm text-center" min="0" value="{{ $detalle->cantidad_solicitada }}" required>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="detalles[{{ $detalle->id }}][precio_compra_real]" class="form-control text-end" min="0" step="0.01" value="{{ $detalle->producto->precio_compra }}" required>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <input type="text" name="detalles[{{ $detalle->id }}][numero_lote]" class="form-control form-control-sm text-uppercase" placeholder="Lote N°" required>
                                            <input type="date" name="detalles[{{ $detalle->id }}][fecha_vencimiento]" class="form-control form-control-sm" required>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-accent px-4 d-flex align-items-center gap-2" onclick="return confirm('¿Confirmar recepción e ingreso a inventario?');">
                            <i data-lucide="package-check" style="width:16px;height:16px;"></i> Registrar Recepción
                        </button>
                    </div>
                </form>

                @elseif($pedido->estado == 'recibido')
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" style="font-size:13px;">
                        <thead class="bg-light text-muted uppercase">
                            <tr>
                                <th>Producto</th>
                                <th>Solicitada</th>
                                <th class="text-success fw-bold">Recibida</th>
                                <th>Costo Ingresado</th>
                                <th>Subtotal Real</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalReal = 0; @endphp
                            @foreach($pedido->detalles as $detalle)
                            @php $st = $detalle->cantidad_recibida * $detalle->precio_compra_real; $totalReal += $st; @endphp
                            <tr>
                                <td>{{ $detalle->producto->nombre }}</td>
                                <td class="text-center">{{ $detalle->cantidad_solicitada }}</td>
                                <td class="text-center font-weight-bold">{{ $detalle->cantidad_recibida }}</td>
                                <td class="text-end">${{ number_format($detalle->precio_compra_real, 2) }}</td>
                                <td class="text-end">${{ number_format($st, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="text-end font-weight-bold">TOTAL REAL RECIBIDO:</td>
                                <td class="text-end font-weight-bold">${{ number_format($totalReal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4 p-3 bg-light rounded" style="border:1px solid var(--color-border);">
                    <form action="{{ route('pedidos.pagar', $pedido->id) }}" method="POST" class="d-flex align-items-end gap-3">
                        @csrf
                        @method('PATCH')
                        <div class="flex-grow-1">
                            <label class="form-label" style="font-size:12px;font-weight:600;text-transform:uppercase;">Fecha en la que se realizó el pago</label>
                            <input type="date" name="fecha_pago" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <button type="submit" class="btn btn-success d-flex align-items-center gap-2" style="font-weight:600;" onclick="return confirm('¿Marcar pedido como pagado al proveedor?');">
                            <i data-lucide="check-square" style="width:16px;height:16px;"></i> Registrar Pago
                        </button>
                    </form>
                </div>

                @else
                <!-- CASO PAGADO O CANCELADO - SOLO LECTURA -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" style="font-size:13px;">
                        <thead class="bg-light text-muted uppercase">
                            <tr>
                                <th>Producto</th>
                                <th>Solicitada</th>
                                @if($pedido->estado == 'pagado')
                                <th class="text-success fw-bold">Recibida</th>
                                <th>Costo Ingresado</th>
                                <th>Subtotal Real</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalReal = 0; @endphp
                            @foreach($pedido->detalles as $detalle)
                            @php $st = $detalle->cantidad_recibida * $detalle->precio_compra_real; $totalReal += $st; @endphp
                            <tr>
                                <td>{{ $detalle->producto->nombre }}</td>
                                <td class="text-center">{{ $detalle->cantidad_solicitada }}</td>
                                @if($pedido->estado == 'pagado')
                                <td class="text-center font-weight-bold">{{ $detalle->cantidad_recibida }}</td>
                                <td class="text-end">${{ number_format($detalle->precio_compra_real, 2) }}</td>
                                <td class="text-end">${{ number_format($st, 2) }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        @if($pedido->estado == 'pagado')
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="text-end font-weight-bold">TOTAL FINAL:</td>
                                <td class="text-end font-weight-bold">${{ number_format($totalReal, 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                @if($pedido->estado == 'cancelado')
                <div class="alert alert-danger mt-3 mb-0" style="font-size:13px;">
                    Este pedido fue cancelado y no afectó el inventario.
                </div>
                @endif
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
