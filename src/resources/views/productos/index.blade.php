@extends('layouts.app')
@section('title', 'Inventario de Productos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1" style="font-family:'Outfit',sans-serif;font-weight:700;">Catálogo de Productos</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">Administra los productos, stocks y lotes.</p>
    </div>
    @if(auth()->user()->rol === 'dueno')
    <a href="{{ route('productos.create') }}" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        Nuevo Producto
    </a>
    @endif
</div>

<form method="GET" action="{{ route('productos.index') }}" class="d-flex gap-2 align-items-center flex-wrap mb-4">
    {{-- Buscador --}}
    <div class="d-flex align-items-center gap-2 flex-grow-1"
         style="background:#fff;border:1px solid var(--color-border);border-radius:10px;padding:9px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.04);min-width:220px;">
        <i data-lucide="search" style="width:16px;height:16px;color:var(--color-text-muted);flex-shrink:0;"></i>
        <input type="text" name="buscar" placeholder="Buscar por nombre, categoría o proveedor..."
               value="{{ request('buscar') }}"
               style="border:none;outline:none;font-size:13.5px;width:100%;background:transparent;color:inherit;">
    </div>

    {{-- Filtro categoría --}}
    <select name="categoria_id" class="form-select w-auto"
            style="background-color:#fff;border:1px solid var(--color-border);border-radius:10px;font-size:13px;padding:9px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <option value="">Categorías</option>
        @foreach($categorias as $cat)
            <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                {{ $cat->nombre }}
            </option>
        @endforeach
    </select>

    {{-- Filtro estado --}}
    <select name="filtro" class="form-select w-auto"
            style="background-color:#fff;border:1px solid var(--color-border);border-radius:10px;font-size:13px;padding:9px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <option value="">Todos</option>
        <option value="critico" {{ request('filtro') == 'critico' ? 'selected' : '' }}>Stock Crítico</option>
        <option value="vencer"  {{ request('filtro') == 'vencer'  ? 'selected' : '' }}>Próximos a Vencer (≤30 días)</option>
    </select>

    {{-- Botón filtrar --}}
    <button type="submit" class="btn btn-accent"
            style="font-size:13px;border-radius:10px;padding:9px 18px;font-weight:600;">
        Filtrar
    </button>

    {{-- Limpiar filtros --}}
    @if(request('buscar') || request('categoria_id') || request('filtro'))
    <a href="{{ route('productos.index') }}" class="btn btn-ghost"
       style="border-radius:10px;padding:9px 14px;font-size:13px;">
        Limpiar
    </a>
    @endif
</form>

<div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="margin-bottom:0;table-layout:fixed;width:100%;">
            <colgroup>
                <col style="width:22%">  {{-- Nombre --}}
                <col style="width:13%">  {{-- Categoría --}}
                <col style="width:15%">  {{-- Proveedor --}}
                <col style="width:9%">   {{-- Precio Venta --}}
                <col style="width:9%">   {{-- Stock --}}
                <col style="width:10%">  {{-- Estado --}}
                <col style="width:22%">  {{-- Acciones --}}
            </colgroup>
            <thead style="background:#F8FAFC;border-bottom:1px solid var(--color-border);font-size:12.5px;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.5px;">
                <tr>
                    <th class="px-4 py-3" style="font-weight:600;border:none;">Nombre</th>
                    <th class="py-3" style="font-weight:600;border:none;">Categoría</th>
                    <th class="py-3" style="font-weight:600;border:none;">Proveedor</th>
                    <th class="py-3 text-center" style="font-weight:600;border:none;">Precio</th>
                    <th class="py-3 text-center" style="font-weight:600;border:none;">Stock</th>
                    <th class="py-3 text-center" style="font-weight:600;border:none;">Estado</th>
                    <th class="py-3 text-end px-4" style="font-weight:600;border:none;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $prod)
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td class="px-4 py-3" style="border:none;">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <div style="font-weight:600;font-size:13.5px;" class="d-flex align-items-center gap-2">
                                    {{ $prod->nombre }}
                                    @if($prod->requiere_receta)
                                        <span style="font-size:10px;background:#FEF3C7;color:#D97706;padding:2px 6px;border-radius:4px;font-weight:700;">Rx</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3" style="border:none;">
                        <div style="font-size:13px;font-weight:500;">{{ $prod->categoria->nombre ?? '—' }}</div>
                    </td>
                    <td class="py-3" style="border:none;">
                        <div style="font-size:13px;color:var(--color-text-muted);">{{ $prod->proveedor->nombre_empresa ?? '—' }}</div>
                    </td>
                    <td class="py-3 text-center" style="border:none;">
                        <div style="font-size:14px;font-weight:700;font-family:'Outfit',sans-serif;color:var(--color-accent);">${{ number_format($prod->precio_venta, 2) }}</div>
                    </td>
                    <td class="py-3 text-center" style="border:none;">
                        @if($prod->stock_total <= $prod->stock_minimo)
                            <span style="background:#FEE2E2;color:var(--color-danger);padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;min-width:40px;">
                                {{ $prod->stock_total }}
                            </span>
                        @else
                            <span style="background:#DCFCE7;color:var(--color-success);padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;min-width:40px;">
                                {{ $prod->stock_total }}
                            </span>
                        @endif
                        <div style="font-size:11px;color:var(--color-text-muted);margin-top:4px;">Min: {{ $prod->stock_minimo }}</div>
                    </td>
                    <td class="py-3 text-center" style="border:none;">
                        @if($prod->estado === 'activo')
                            <span style="font-size:12px;color:var(--color-accent);background:#E0F2FE;padding:4px 10px;border-radius:20px;font-weight:600;">Activo</span>
                        @else
                            <span style="font-size:12px;color:var(--color-text-muted);background:#F1F5F9;padding:4px 10px;border-radius:20px;font-weight:600;">Inactivo</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-end" style="border:none;">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('productos.show', $prod->id) }}"
                               class="btn btn-ghost btn-sm d-flex align-items-center gap-1"
                               style="font-size:12px;border-radius:6px;padding:5px 10px;">
                                <i data-lucide="eye" style="width:14px;height:14px;"></i> Detalle
                            </a>
                            @if(auth()->user()->rol === 'dueno')
                                <a href="{{ route('productos.edit', $prod->id) }}"
                                   class="btn btn-ghost btn-sm d-flex align-items-center gap-1"
                                   style="font-size:12px;border-radius:6px;padding:5px 10px;">
                                    <i data-lucide="pencil" style="width:14px;height:14px;"></i> Editar
                                </a>
                                @if($prod->estado === 'activo')
                                    <form action="{{ route('productos.destroy', $prod->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas dar de baja este producto?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm d-flex align-items-center gap-1"
                                                style="font-size:12px;border-radius:6px;padding:5px 10px;">
                                            <i data-lucide="trash-2" style="width:14px;height:14px;color:var(--color-danger);"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5" style="color:var(--color-text-muted);font-size:13px;border:none;">
                        <i data-lucide="package-search" style="width:32px;height:32px;display:block;margin:0 auto 8px;opacity:0.5;"></i>
                        No se encontraron productos.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-end mt-3">
    {{ $productos->links() }}
</div>
@endsection
