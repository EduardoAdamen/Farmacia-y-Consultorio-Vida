@extends('layouts.app')
@section('title', 'Editar Pedido')

@section('content')
<div class="mb-4">
    <a href="{{ route('pedidos.show', $pedido->id) }}" class="text-decoration-none" style="font-size:13px;color:var(--color-text-muted);display:inline-flex;align-items:center;gap:6px;font-weight:500;">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Volver al pedido
    </a>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1" style="font-family:'Outfit',sans-serif;font-weight:700;">Editar Pedido</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">
            Folio: <code style="font-size:13px;">{{ $pedido->folio }}</code>
            <span style="font-size:11px;color:#D97706;background:#FEF3C7;padding:2px 8px;border-radius:20px;font-weight:600;margin-left:6px;">Pendiente</span>
        </p>
    </div>
</div>

<div class="card" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;max-width:1000px;" id="pedidoFormApp">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('pedidos.update', $pedido->id) }}">
            @csrf
            @method('PUT')

            {{-- Cabecera del pedido --}}
            <div class="row align-items-center mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Proveedor <span class="text-danger">*</span></label>
                    <select name="proveedor_id" class="form-select" style="font-size:14px;border-radius:8px;padding:9px 12px;" required>
                        <option value="" disabled>Seleccione un proveedor...</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}" {{ $pedido->proveedor_id == $prov->id ? 'selected' : '' }}>
                                {{ $prov->nombre_empresa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Fecha Estimada de Entrega</label>
                    <input type="date" name="fecha_estimada"
                           value="{{ $pedido->fecha_estimada ? \Carbon\Carbon::parse($pedido->fecha_estimada)->format('Y-m-d') : '' }}"
                           class="form-control" style="font-size:14px;border-radius:8px;padding:9px 12px;">
                </div>
            </div>

            {{-- Listado de Productos --}}
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <h6 style="font-weight:700;font-family:'Outfit',sans-serif;margin:0;font-size:14px;">Productos del Pedido</h6>
                <button type="button" class="btn btn-accent btn-sm d-flex align-items-center gap-1"
                        style="font-size:12px;border-radius:6px;padding:6px 12px;font-weight:600;" onclick="addRow()">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Agregar Producto
                </button>
            </div>

            <div class="table-responsive" style="background:#F8FAFC;border:1px solid var(--color-border);border-radius:10px;padding:12px;">
                <table class="table table-borderless table-sm mb-0" id="productosTable">
                    <thead style="border-bottom:1px solid var(--color-border);font-size:12px;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.5px;">
                        <tr>
                            <th style="width:45%;font-weight:600;">Producto</th>
                            <th style="width:17%;font-weight:600;">Precio Ref.</th>
                            <th style="width:15%;font-weight:600;">Cantidad</th>
                            <th style="width:15%;font-weight:600;">Subtotal</th>
                            <th style="width:8%;"></th>
                        </tr>
                    </thead>
                    <tbody id="productosBody">
                        {{-- Fila plantilla (oculta) --}}
                        <tr id="rowTemplate" style="display:none;">
                            <td class="align-middle">
                                <select class="form-select form-select-sm pr-select" onchange="actualizarPrecio(this)">
                                    <option value="" disabled selected>Seleccione producto...</option>
                                    @foreach($productos as $prod)
                                        <option value="{{ $prod->id }}" data-precio="{{ $prod->precio_compra }}">
                                            {{ $prod->nombre }} (Stock: {{ $prod->stock_total }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="align-middle">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control pr-price" min="0" step="0.01" value="0.00" oninput="calcularFila(this)">
                                </div>
                            </td>
                            <td class="align-middle">
                                <input type="number" class="form-control form-select-sm pr-qty" min="1" step="1" value="1" oninput="calcularFila(this)">
                            </td>
                            <td class="align-middle">
                                <input type="text" class="form-control form-select-sm pr-subtotal text-end" value="$0.00" readonly
                                       style="background:transparent;border:none;font-weight:600;">
                            </td>
                            <td class="align-middle text-end">
                                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(this)">
                                    <i data-lucide="trash-2" style="width:15px;height:15px;"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot style="border-top:1px solid var(--color-border);">
                        <tr>
                            <td colspan="3" class="text-end py-3" style="font-weight:600;font-size:14px;">TOTAL DEL PEDIDO:</td>
                            <td class="py-3">
                                <input type="text" id="totalPedido" class="form-control fw-bold text-end p-0" value="$0.00" readonly
                                       style="background:transparent;border:none;font-size:18px;color:var(--color-accent);">
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-3 p-3 rounded d-none" id="errorDiv" style="background:#FEE2E2;color:var(--color-danger);font-size:13px;">
                <i data-lucide="alert-circle" style="width:15px;height:15px;"></i> <span id="errorMsg"></span>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn btn-ghost"
                   style="border-radius:8px;padding:9px 16px;font-size:13px;">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-accent d-flex align-items-center gap-2"
                        style="border-radius:8px;padding:9px 20px;font-size:13px;font-weight:600;"
                        onclick="return prepararEnvio()">
                    <i data-lucide="save" style="width:16px;height:16px;"></i> Guardar Cambios
                </button>
            </div>

            {{-- Inputs ocultos generados por JS --}}
            <div id="hiddenInputs"></div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Datos de los detalles existentes para pre-cargar las filas
    const detallesExistentes = @json($detallesExistentes);

    let rowCount = 0;

    function addRow(productoId = null, cantidad = 1, precio = 0) {
        const body     = document.getElementById('productosBody');
        const template = document.getElementById('rowTemplate');
        const clone    = template.cloneNode(true);

        clone.id           = 'row_' + rowCount;
        clone.style.display = '';
        body.appendChild(clone);

        const select = clone.querySelector('.pr-select');
        const priceInput = clone.querySelector('.pr-price');
        const qtyInput   = clone.querySelector('.pr-qty');

        if (productoId) {
            select.value = productoId;
            priceInput.value = parseFloat(precio).toFixed(2);
            qtyInput.value   = cantidad;
            calcularFila(select);
        }

        lucide.createIcons();
        rowCount++;
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calcularTotal();
    }

    function actualizarPrecio(select) {
        const row    = select.closest('tr');
        const option = select.options[select.selectedIndex];
        row.querySelector('.pr-price').value = parseFloat(option.getAttribute('data-precio') || 0).toFixed(2);
        calcularFila(select);
    }

    function calcularFila(elem) {
        const row   = elem.closest('tr');
        const price = parseFloat(row.querySelector('.pr-price').value) || 0;
        const qty   = parseInt(row.querySelector('.pr-qty').value) || 0;
        row.querySelector('.pr-subtotal').value = '$' + (price * qty).toFixed(2);
        calcularTotal();
    }

    function calcularTotal() {
        const rows = document.querySelectorAll('#productosBody tr:not(#rowTemplate)');
        let total  = 0;
        rows.forEach(row => {
            total += (parseFloat(row.querySelector('.pr-price').value) || 0)
                   * (parseInt(row.querySelector('.pr-qty').value) || 0);
        });
        document.getElementById('totalPedido').value = '$' + total.toFixed(2);
    }

    function prepararEnvio() {
        const rows        = document.querySelectorAll('#productosBody tr:not(#rowTemplate)');
        const hiddenInputs = document.getElementById('hiddenInputs');
        const errorDiv    = document.getElementById('errorDiv');
        const errorMsg    = document.getElementById('errorMsg');

        hiddenInputs.innerHTML = '';

        if (rows.length === 0) {
            errorMsg.textContent = 'Debe agregar al menos un producto al pedido.';
            errorDiv.classList.remove('d-none');
            return false;
        }

        let valid = true, c = 0;

        rows.forEach(row => {
            const prodId = row.querySelector('.pr-select').value;
            const price  = parseFloat(row.querySelector('.pr-price').value);
            const qty    = parseInt(row.querySelector('.pr-qty').value);

            if (!prodId) { valid = false; errorMsg.textContent = 'Seleccione un producto en todas las filas.'; }
            if (qty < 1) { valid = false; errorMsg.textContent = 'La cantidad debe ser mayor a 0.'; }

            if (valid) {
                hiddenInputs.innerHTML += `<input type="hidden" name="productos[${c}][id]"       value="${prodId}">`;
                hiddenInputs.innerHTML += `<input type="hidden" name="productos[${c}][cantidad]" value="${qty}">`;
                hiddenInputs.innerHTML += `<input type="hidden" name="productos[${c}][precio]"   value="${price}">`;
                c++;
            }
        });

        if (!valid) { errorDiv.classList.remove('d-none'); }
        else        { errorDiv.classList.add('d-none'); }

        return valid;
    }

    // Pre-cargar filas con los detalles existentes
    document.addEventListener('DOMContentLoaded', () => {
        if (detallesExistentes.length > 0) {
            detallesExistentes.forEach(d => addRow(d.producto_id, d.cantidad, d.precio));
        } else {
            addRow();
        }
    });
</script>
@endpush
@endsection
