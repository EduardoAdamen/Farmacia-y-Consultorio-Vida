@extends('layouts.app')
@section('title', 'Editar Producto')

@section('content')
<div class="mb-4">
    <a href="{{ route('productos.show', $producto->id) }}" class="text-decoration-none" style="font-size:13px;color:var(--color-text-muted);display:inline-flex;align-items:center;gap:6px;font-weight:500;transition:all 0.2s;">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Volver al detalle
    </a>
</div>

<div class="card" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;">
    <div class="card-body p-4">
        <h5 class="mb-4" style="font-family:'Outfit',sans-serif;font-weight:700;">
            Editar Producto &mdash; <span style="color:var(--color-accent);">{{ $producto->nombre }}</span>
        </h5>

        @if($errors->any())
        <div class="alert alert-danger" style="border-radius:8px;font-size:13px;">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('productos.update', $producto->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Nombre del Producto <span class="text-danger">*</span></label>
                    <input type="text" name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $producto->nombre) }}" required
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">SKU / Código <span class="text-danger">*</span></label>
                    <input type="text" name="sku"
                           class="form-control @error('sku') is-invalid @enderror"
                           value="{{ old('sku', $producto->sku) }}" required
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Código de Barras</label>
                    <input type="text" name="codigo_barras"
                           class="form-control @error('codigo_barras') is-invalid @enderror"
                           value="{{ old('codigo_barras', $producto->codigo_barras) }}"
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('codigo_barras')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Categoría <span class="text-danger">*</span></label>
                    <select name="categoria_id"
                            class="form-select @error('categoria_id') is-invalid @enderror"
                            style="font-size:14px;border-radius:8px;padding:10px 14px;">
                        <option value="">Seleccione una categoría...</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Proveedor <span class="text-danger">*</span></label>
                    <select name="proveedor_id"
                            class="form-select @error('proveedor_id') is-invalid @enderror"
                            style="font-size:14px;border-radius:8px;padding:10px 14px;">
                        <option value="">Seleccione un proveedor...</option>
                        @foreach($proveedores ?? [] as $prov)
                            <option value="{{ $prov->id }}" {{ old('proveedor_id', $producto->proveedor_id) == $prov->id ? 'selected' : '' }}>{{ $prov->nombre_empresa }}</option>
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
                               value="{{ old('precio_compra', $producto->precio_compra) }}"
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
                               value="{{ old('precio_venta', $producto->precio_venta) }}"
                               style="font-size:14px;border-radius:0 8px 8px 0;padding:10px 14px;border-left:none;">
                        @error('precio_venta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Stock Mínimo <span class="text-danger">*</span></label>
                    <input type="number" name="stock_minimo" min="0"
                           class="form-control @error('stock_minimo') is-invalid @enderror"
                           value="{{ old('stock_minimo', $producto->stock_minimo) }}" required
                           style="font-size:14px;border-radius:8px;padding:10px 14px;">
                    @error('stock_minimo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12 mb-4 mt-2">
                    <div class="form-check form-switch d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="requiere_receta"
                               name="requiere_receta" value="1"
                               {{ old('requiere_receta', $producto->requiere_receta) ? 'checked' : '' }}
                               style="width:40px;height:20px;cursor:pointer;">
                        <label class="form-check-label mb-0" for="requiere_receta"
                               style="font-size:14px;font-weight:600;cursor:pointer;padding-top:2px;">
                            Requiere Receta Médica (Rx)
                        </label>
                    </div>
                </div>
            </div>

            <hr style="border-color:var(--color-border);margin:10px 0 24px;">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-ghost"
                   style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-accent d-flex align-items-center gap-2"
                        style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
                    <i data-lucide="save" style="width:16px;height:16px;"></i> Actualizar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection