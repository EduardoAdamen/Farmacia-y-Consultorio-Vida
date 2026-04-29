@extends('layouts.app')
@section('title', 'Detalle de Producto')

@section('content')
<div class="mb-4">
    <a href="{{ route('productos.index') }}" class="text-decoration-none" style="font-size:13px;color:var(--color-text-muted);display:inline-flex;align-items:center;gap:6px;font-weight:500;transition:all 0.2s;">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Volver a productos
    </a>
</div>

{{-- ── Mensajes de éxito/error ─────────────────────────── --}}
@if(session('success'))
<div class="alert alert-success d-flex align-items-center gap-2 mb-4" style="border-radius:8px;font-size:13px;">
    <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger d-flex align-items-center gap-2 mb-4" style="border-radius:8px;font-size:13px;">
    <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
    {{ session('error') }}
</div>
@endif

{{-- ── Tarjeta unificada de información del producto ────── --}}
<div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;">
    <div class="card-body p-4">
        <div class="d-flex align-items-start gap-4 flex-wrap">

            {{-- Ícono / avatar --}}
            <div style="width:72px;height:72px;border-radius:16px;background:#F1F5F9;display:flex;align-items:center;justify-content:center;color:var(--color-accent);flex-shrink:0;">
                <i data-lucide="package" style="width:36px;height:36px;"></i>
            </div>

            {{-- Info principal --}}
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;font-size:18px;">{{ $producto->nombre }}</h5>
                    @if($producto->estado === 'activo')
                        <span style="font-size:11px;color:var(--color-accent);background:#E0F2FE;padding:3px 10px;border-radius:20px;font-weight:600;">Activo</span>
                    @else
                        <span style="font-size:11px;color:var(--color-text-muted);background:#F1F5F9;padding:3px 10px;border-radius:20px;font-weight:600;">Inactivo</span>
                    @endif
                    @if($producto->requiere_receta)
                        <span style="font-size:11px;color:#D97706;background:#FEF3C7;padding:3px 10px;border-radius:20px;font-weight:600;">Rx</span>
                    @endif
                </div>
                <div class="d-flex gap-3 mb-3 flex-wrap">
                    <span style="font-size:12px;color:var(--color-text-muted);">
                        SKU: <code style="font-size:12px;">{{ $producto->sku ?? 'Sin SKU' }}</code>
                    </span>
                    @if($producto->codigo_barras)
                    <span style="font-size:12px;color:var(--color-text-muted);">
                        Barras: <code style="font-size:12px;">{{ $producto->codigo_barras }}</code>
                    </span>
                    @endif
                </div>

                {{-- Detalles en grid --}}
                <div class="row g-3">
                    <div class="col-sm-6 col-md-3">
                        <div style="font-size:11px;color:var(--color-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:3px;">Categoría</div>
                        <div style="font-size:13px;font-weight:500;">{{ $producto->categoria?->nombre ?? '—' }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div style="font-size:11px;color:var(--color-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:3px;">Proveedor</div>
                        <div style="font-size:13px;font-weight:500;">{{ $producto->proveedor?->nombre_empresa ?? '—' }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div style="font-size:11px;color:var(--color-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:3px;">Precio de Compra</div>
                        <div style="font-size:16px;font-weight:700;font-family:'Outfit',sans-serif;">${{ number_format($producto->precio_compra, 2) }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div style="font-size:11px;color:var(--color-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:3px;">Precio de Venta</div>
                        <div style="font-size:16px;font-weight:700;font-family:'Outfit',sans-serif;color:var(--color-accent);">${{ number_format($producto->precio_venta, 2) }}</div>
                    </div>
                </div>
            </div>

            {{-- Botón editar (solo dueño) --}}
            @if(auth()->user()->rol === 'dueno')
            <div style="flex-shrink:0;">
                <a href="{{ route('productos.edit', $producto->id) }}"
                   class="btn btn-accent d-flex align-items-center gap-2"
                   style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;white-space:nowrap;">
                    <i data-lucide="pencil" style="width:15px;height:15px;"></i> Editar
                </a>
            </div>
            @endif
        </div>
    </div>
</div>


{{-- ── Tabla de Lotes Activos ──────────────────────────── --}}
<div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;overflow:hidden;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--color-border);padding:16px 24px;">
        <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;font-size:15px;">
            Lotes Activos
           
        </h5>
        @if(auth()->user()->rol === 'dueno')
        <button class="btn btn-accent btn-sm d-flex align-items-center gap-1"
                style="font-size:12px;border-radius:6px;padding:6px 12px;font-weight:600;"
                data-bs-toggle="collapse" data-bs-target="#formAgregarLote">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Agregar Lote
        </button>
        @endif
    </div>

    {{-- Formulario colapsable para agregar lote --}}
    @if(auth()->user()->rol === 'dueno')
    <div class="collapse" id="formAgregarLote">
        <div style="background:var(--color-secondary);border-bottom:1px solid var(--color-border);padding:20px 24px;">
            <form action="{{ route('productos.lotes.store', $producto->id) }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Número de Lote <span class="text-danger">*</span></label>
                        <input type="text" name="numero_lote" class="form-control" required
                               placeholder="Ej: LOT-2025-002"
                               style="font-size:13px;border-radius:8px;padding:8px 12px;">
                    </div>
                    <div class="col-md-3">
                        <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Cantidad <span class="text-danger">*</span></label>
                        <input type="number" name="cantidad" min="1" class="form-control" required
                               placeholder="Ej: 100"
                               style="font-size:13px;border-radius:8px;padding:8px 12px;">
                    </div>
                    <div class="col-md-3">
                        <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Fecha de Vencimiento <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_vencimiento" class="form-control" required
                               min="{{ now()->addDay()->format('Y-m-d') }}"
                               style="font-size:13px;border-radius:8px;padding:8px 12px;">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-accent w-100 d-flex align-items-center justify-content-center gap-2"
                                style="font-size:13px;font-weight:600;border-radius:8px;padding:9px;">
                            <i data-lucide="save" style="width:14px;height:14px;"></i> Guardar Lote
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background:#F8FAFC;border-bottom:1px solid var(--color-border);font-size:12px;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.5px;">
                <tr>
                    <th class="px-4 py-3" style="font-weight:600;border:none;">N° Lote</th>
                    <th class="py-3" style="font-weight:600;border:none;">Fecha Ingreso</th>
                    <th class="py-3" style="font-weight:600;border:none;">Fecha Vencimiento</th>
                    <th class="py-3 text-center" style="font-weight:600;border:none;">Cantidad</th>
                    <th class="py-3 text-center" style="font-weight:600;border:none;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($producto->lotes as $lote)
                @php
                    $fechaVenc     = \Carbon\Carbon::parse($lote->fecha_vencimiento)->startOfDay();
                    $hoy           = \Carbon\Carbon::now()->startOfDay();
                    $vencido       = $fechaVenc->lt($hoy);
                    $diasRestantes = (int) $hoy->diffInDays($fechaVenc, false);
                    $proximoVencer = !$vencido && $diasRestantes <= 30;
                @endphp
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td class="px-4 py-3" style="border:none;">
                        <code style="font-size:12px;background:#F1F5F9;padding:3px 7px;border-radius:5px;color:var(--color-primary);">
                            {{ $lote->numero_lote }}
                        </code>
                    </td>
                    <td class="py-3" style="font-size:13px;color:var(--color-text-muted);border:none;">
                        {{ \Carbon\Carbon::parse($lote->fecha_ingreso)->format('d/m/Y H:i') }}
                    </td>
                    <td class="py-3" style="font-size:13px;border:none;color:{{ $vencido ? 'var(--color-danger)' : ($proximoVencer ? 'var(--color-warning)' : 'inherit') }};font-weight:{{ $vencido || $proximoVencer ? '600' : 'normal' }};">
                        {{ $fechaVenc->format('d/m/Y') }}
                        @if($vencido)
                            <span style="font-size:10px;background:#FEE2E2;color:var(--color-danger);padding:2px 6px;border-radius:4px;margin-left:4px;">VENCIDO</span>
                        @elseif($proximoVencer)
                            <span style="font-size:10px;background:#FEF3C7;color:#D97706;padding:2px 6px;border-radius:4px;margin-left:4px;">{{ $diasRestantes }}d restantes</span>
                        @endif
                    </td>
                    <td class="py-3 text-center" style="border:none;">
                        <span style="background:{{ $lote->cantidad > 0 ? '#DCFCE7' : '#F1F5F9' }};color:{{ $lote->cantidad > 0 ? 'var(--color-success)' : 'var(--color-text-muted)' }};padding:4px 12px;border-radius:20px;font-size:13px;font-weight:700;">
                            {{ $lote->cantidad }}
                        </span>
                    </td>
                    <td class="py-3 text-center" style="border:none;">
                        @if($vencido)
                            <span style="font-size:11px;color:var(--color-danger);font-weight:600;">Vencido</span>
                        @elseif($lote->cantidad == 0)
                            <span style="font-size:11px;color:var(--color-text-muted);">Agotado</span>
                        @elseif($proximoVencer)
                            <span style="font-size:11px;color:var(--color-warning);font-weight:600;">Próximo a vencer</span>
                        @else
                            <span style="font-size:11px;color:var(--color-success);font-weight:600;">Vigente</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5" style="color:var(--color-text-muted);font-size:13px;border:none;">
                        <i data-lucide="inbox" style="width:32px;height:32px;display:block;margin:0 auto 8px;opacity:0.4;"></i>
                        Este producto no tiene lotes registrados.
                        @if(auth()->user()->rol === 'dueno')
                            <br><a href="#" onclick="document.getElementById('formAgregarLote').classList.add('show')" style="color:var(--color-accent);font-size:12px;">Agregar el primer lote</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Kardex (Movimientos) ────────────────────────────── --}}
<div class="card" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;overflow:hidden;">
    <div class="card-header bg-white" style="border-bottom:1px solid var(--color-border);padding:16px 24px;">
        <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;font-size:15px;">
            Historial de Movimientos
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background:#F8FAFC;border-bottom:1px solid var(--color-border);font-size:12px;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.5px;">
                <tr>
                    <th class="px-4 py-3" style="font-weight:600;border:none;">Fecha y Hora</th>
                    <th class="py-3" style="font-weight:600;border:none;">Tipo</th>
                    <th class="py-3 text-center" style="font-weight:600;border:none;">Cantidad</th>
                    <th class="py-3" style="font-weight:600;border:none;">Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kardex as $mov)
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td class="px-4 py-3" style="font-size:13px;border:none;color:var(--color-text-muted);">
                        {{ \Carbon\Carbon::parse($mov->fecha_hora)->format('d/m/Y H:i') }}
                    </td>
                    <td class="py-3" style="border:none;">
                        @php
                            $tipoConfig = [
                                'entrada'    => ['bg'=>'#DCFCE7','color'=>'var(--color-success)','label'=>'Entrada'],
                                'venta'      => ['bg'=>'#FEE2E2','color'=>'var(--color-danger)','label'=>'Venta'],
                                'devolucion' => ['bg'=>'#E0F2FE','color'=>'var(--color-info)','label'=>'Devolución'],
                                'ajuste'     => ['bg'=>'#FEF3C7','color'=>'var(--color-warning)','label'=>'Ajuste'],
                                'salida'     => ['bg'=>'#F3E8FF','color'=>'#9333EA','label'=>'Salida'],
                            ];
                            $cfg = $tipoConfig[$mov->tipo] ?? ['bg'=>'#F1F5F9','color'=>'var(--color-text-muted)','label'=>ucfirst($mov->tipo)];
                        @endphp
                        <span style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                            {{ $cfg['label'] }}
                        </span>
                    </td>
                    <td class="py-3 text-center" style="border:none;">
                        <span style="font-size:14px;font-weight:700;color:{{ $mov->cantidad > 0 ? 'var(--color-success)' : 'var(--color-danger)' }};">
                            {{ $mov->cantidad > 0 ? '+' : '' }}{{ $mov->cantidad }}
                        </span>
                    </td>
                    <td class="py-3" style="font-size:13px;border:none;">
                        {{ $mov->usuario?->nombre_completo ?? 'Sistema' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4" style="color:var(--color-text-muted);font-size:13px;border:none;">
                        Sin movimientos registrados aún.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($kardex->hasPages())
    <div class="px-4 py-3 border-top" style="border-color:var(--color-border)!important;">
        {{ $kardex->links() }}
    </div>
    @endif
</div>
@endsection