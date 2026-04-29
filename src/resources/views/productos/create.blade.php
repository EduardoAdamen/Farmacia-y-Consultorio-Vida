@extends('layouts.app')
@section('title', 'Nuevo Producto')

@section('content')
<div class="mb-4">
    <a href="{{ route('productos.index') }}" class="text-decoration-none" style="font-size:13px;color:var(--color-text-muted);display:inline-flex;align-items:center;gap:6px;font-weight:500;transition:all 0.2s;">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Volver a productos
    </a>
</div>

<div class="card" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;">
    <div class="card-body p-4">
        <h5 class="mb-4" style="font-family:'Outfit',sans-serif;font-weight:700;">Registrar Producto</h5>
        
        <form action="{{ route('productos.store') }}" method="POST">
            @csrf
            
            {{-- ── Datos del Producto ───────────────────────────────── --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Nombre del Producto <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" required
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">SKU / Código <span class="text-danger">*</span></label>
                    <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                           value="{{ old('sku') }}" required
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Código de Barras</label>
                    <input type="text" name="codigo_barras" class="form-control @error('codigo_barras') is-invalid @enderror"
                           value="{{ old('codigo_barras') }}"
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('codigo_barras')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Categoría <span class="text-danger">*</span></label>
                    <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror"
                            style="font-size:14px;border-radius:8px;padding:10px 14px;">
                        <option value="">Seleccione una categoría...</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Proveedor <span class="text-danger">*</span></label>
                    <select name="proveedor_id" class="form-select @error('proveedor_id') is-invalid @enderror"
                            style="font-size:14px;border-radius:8px;padding:10px 14px;">
                        <option value="">Seleccione un proveedor...</option>
                        @foreach($proveedores ?? [] as $prov)
                            <option value="{{ $prov->id }}" {{ old('proveedor_id') == $prov->id ? 'selected' : '' }}>{{ $prov->nombre_empresa }}</option>
                        @endforeach
                    </select>
                    @error('proveedor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Precio de Compra <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:#F8FAFC;border-color:var(--color-border);border-right:none;">$</span>
                        <input type="number" step="0.01" min="0" name="precio_compra"
                               class="form-control @error('precio_compra') is-invalid @enderror"
                               value="{{ old('precio_compra') }}"
                               style="font-size:14px;border-radius:0 8px 8px 0;padding:10px 14px;border-left:none;">
                        @error('precio_compra')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Precio de Venta <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:#F8FAFC;border-color:var(--color-border);border-right:none;">$</span>
                        <input type="number" step="0.01" min="0" name="precio_venta"
                               class="form-control @error('precio_venta') is-invalid @enderror"
                               value="{{ old('precio_venta') }}"
                               style="font-size:14px;border-radius:0 8px 8px 0;padding:10px 14px;border-left:none;">
                        @error('precio_venta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Stock Mínimo <span class="text-danger">*</span></label>
                    <input type="number" name="stock_minimo" min="0"
                           class="form-control @error('stock_minimo') is-invalid @enderror"
                           value="{{ old('stock_minimo', 5) }}" required
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('stock_minimo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12 mb-2 mt-1">
                    <div class="form-check form-switch d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="requiere_receta"
                               name="requiere_receta" value="1"
                               {{ old('requiere_receta') ? 'checked' : '' }}
                               style="width:40px;height:20px;cursor:pointer;">
                        <label class="form-check-label mb-0" for="requiere_receta"
                               style="font-size:14px;font-weight:600;cursor:pointer;padding-top:2px;">
                            Requiere Receta Médica (Rx)
                        </label>
                    </div>
                </div>
            </div>

            {{-- ── Lote Inicial (Opcional) ──────────────────────────── --}}
            <hr style="border-color:var(--color-border);margin:20px 0;">

            <div class="mb-3 d-flex align-items-center gap-2">
                <span style="font-family:'Outfit',sans-serif;font-weight:700;font-size:15px;">Lote Inicial</span>
                <span class="badge" style="background:var(--color-secondary);color:var(--color-text-muted);font-size:11px;font-weight:500;">Opcional</span>
            </div>
            <p style="font-size:12px;color:var(--color-text-muted);margin-top:-8px;margin-bottom:16px;">
                Si ya cuentas con mercancía disponible al registrar este producto, llena los campos de abajo.
                De lo contrario, podrás agregar lotes después desde el detalle del producto.
            </p>

            <div class="row" style="background:var(--color-secondary);border-radius:10px;padding:16px 12px;margin:0 0 8px;">
                <div class="col-md-4 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Número de Lote</label>
                    <input type="text" name="numero_lote"
                           class="form-control @error('numero_lote') is-invalid @enderror"
                           value="{{ old('numero_lote') }}"
                           placeholder="Ej: LOT-2025-001"
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('numero_lote')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Cantidad Inicial</label>
                    <input type="number" name="cantidad_inicial" min="0"
                           class="form-control @error('cantidad_inicial') is-invalid @enderror"
                           value="{{ old('cantidad_inicial') }}"
                           placeholder="Ej: 50"
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('cantidad_inicial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento"
                           class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                           value="{{ old('fecha_vencimiento') }}"
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('fecha_vencimiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr style="border-color:var(--color-border);margin:20px 0 24px;">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('productos.index') }}" class="btn btn-ghost"
                   style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-accent d-flex align-items-center gap-2"
                        style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
                    <i data-lucide="save" style="width:16px;height:16px;"></i> Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection