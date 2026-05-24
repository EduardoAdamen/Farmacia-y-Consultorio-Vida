@extends('layouts.app')
@section('title', 'Nuevo Pedido')

@section('content')
<div class="mx-auto" style="max-width: 1000px;" id="pedidoFormApp">
    <div class="mb-4">
        <a href="{{ route('pedidos.index') }}" class="text-decoration-none" style="font-size:13px;color:var(--color-text-muted);display:inline-flex;align-items:center;gap:6px;font-weight:500;transition:all 0.2s;">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Volver a pedidos
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:12px;background:#fff;">
        <div class="card-body p-4">
            <h5 class="mb-4" style="font-family:'Outfit',sans-serif;font-weight:700;">Registrar Pedido</h5>

            <form method="POST" action="{{ route('pedidos.store') }}">
                @csrf

                <!-- Cabecera del pedido -->
                <div class="row align-items-center mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label" style="font-weight:600;color:var(--color-text-muted);">Proveedor <span class="text-danger">*</span></label>
                        <select name="proveedor_id" class="form-select border-start-0 border-end-0 border-top-0 border-bottom-1" style="border-radius:0;background:transparent;font-weight:600;font-size:15px;padding-left:0;box-shadow:none;" required>
                            <option value="" disabled selected>Seleccione un proveedor...</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->nombre_empresa }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-weight:600;color:var(--color-text-muted);">Fecha Estimada de Entrega</label>
                        <input type="date" name="fecha_estimada" class="form-control border-start-0 border-end-0 border-top-0 border-bottom-1 text-end" style="border-radius:0;background:transparent;font-weight:600;font-size:15px;padding-right:0;box-shadow:none;">
                    </div>
                </div>

                <!-- Listado de Productos -->
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <h6 style="font-weight:700;font-family:'Outfit',sans-serif;margin:0;">Productos del Pedido</h6>
                    <button type="button" class="btn btn-sm btn-outline-accent d-flex align-items-center gap-1" style="border-radius:6px;" onclick="addRow()">
                        <i data-lucide="plus" style="width:14px;height:14px;"></i> Lote de Producto
                    </button>
                </div>

                <div class="table-responsive bg-light p-3 rounded" style="border:1px solid var(--color-border);">
                    <table class="table table-borderless table-sm mb-0" id="productosTable">
                        <thead style="border-bottom:1px solid #E2E8F0;font-size:12px;color:var(--color-text-muted);text-transform:uppercase;">
                            <tr>
                                <th style="width: 45%;">Producto</th>
                                <th style="width: 15%;">Precio Ref.</th>
                                <th style="width: 15%;">Cantidad</th>
                                <th style="width: 15%;">Subtotal</th>
                                <th style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody id="productosBody">
                            <!-- Fila de ejemplo/base que se oculta -->
                            <tr id="rowTemplate" style="display:none; border-bottom:1px dashed #E2E8F0;">
                                <td class="align-middle">
                                    <select class="form-select form-select-sm pr-select" onchange="actualizarPrecio(this)">
                                        <option value="" disabled selected>Seleccione producto...</option>
                                        @foreach($productos as $prod)
                                            <option value="{{ $prod->id }}" data-precio="{{ $prod->precio_compra }}">{{ $prod->nombre }} (Stock: {{ $prod->stock_total }})</option>
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
                                    <input type="text" class="form-control form-select-sm pr-subtotal text-end" value="$0.00" readonly style="background:transparent;border:none;font-weight:600;">
                                </td>
                                <td class="align-middle text-end">
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(this)">
                                        <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot style="border-top:1px solid var(--color-border);">
                            <tr>
                                <td colspan="3" class="text-end py-3" style="font-weight:600;font-size:14px;">TOTAL DEL PEDIDO:</td>
                                <td class="py-3 px-3">
                                    <input type="text" id="totalPedido" class="form-control fw-bold text-end p-0" value="$0.00" readonly style="background:transparent;border:none;font-size:18px;color:var(--color-accent);">
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 mb-2 p-3 bg-opacity-10 bg-warning text-dark rounded d-none" id="errorDiv">
                    <i data-lucide="alert-circle" style="width:16px;height:16px;"></i> <span id="errorMsg"></span>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-accent px-5 py-2 d-flex align-items-center gap-2 font-weight-bold" style="border-radius:8px;" onclick="return prepararEnvio()">
                        <i data-lucide="check-circle" style="width:18px;height:18px;"></i>
                        Generar Orden
                    </button>
                </div>

                <!-- Contenedor oculto para inputs dinámicos -->
                <div id="hiddenInputs"></div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let rowCount = 0;

    function addRow() {
        const body = document.getElementById('productosBody');
        const template = document.getElementById('rowTemplate');
        const clone = template.cloneNode(true);
        
        clone.id = 'row_' + rowCount;
        clone.style.display = '';
        
        body.appendChild(clone);
        lucide.createIcons();
        rowCount++;
    }

    function removeRow(btn) {
        const row = btn.closest('tr');
        row.remove();
        calcularTotal();
    }

    function actualizarPrecio(select) {
        const row = select.closest('tr');
        const option = select.options[select.selectedIndex];
        const precio = option.getAttribute('data-precio') || 0;
        
        const priceInput = row.querySelector('.pr-price');
        priceInput.value = parseFloat(precio).toFixed(2);
        
        calcularFila(select);
    }

    function calcularFila(elem) {
        const row = elem.closest('tr');
        const price = parseFloat(row.querySelector('.pr-price').value) || 0;
        const qty = parseInt(row.querySelector('.pr-qty').value) || 0;
        
        const subtotal = price * qty;
        row.querySelector('.pr-subtotal').value = '$' + subtotal.toFixed(2);
        
        calcularTotal();
    }

    function calcularTotal() {
        const rows = document.querySelectorAll('#productosBody tr:not(#rowTemplate)');
        let total = 0;
        
        rows.forEach(row => {
            const price = parseFloat(row.querySelector('.pr-price').value) || 0;
            const qty = parseInt(row.querySelector('.pr-qty').value) || 0;
            total += price * qty;
        });
        
        document.getElementById('totalPedido').value = '$' + total.toFixed(2);
    }

    function prepararEnvio() {
        const rows = document.querySelectorAll('#productosBody tr:not(#rowTemplate)');
        const hiddenInputs = document.getElementById('hiddenInputs');
        const errorDiv = document.getElementById('errorDiv');
        const errorMsg = document.getElementById('errorMsg');
        
        hiddenInputs.innerHTML = ''; // limpiar
        
        let selectedCount = 0;
        rows.forEach(row => {
            const prodId = row.querySelector('.pr-select').value;
            if (prodId) {
                selectedCount++;
            }
        });

        if (rows.length === 0 || selectedCount === 0) {
            Swal.fire({
                title: 'Atención',
                text: 'Debe seleccionar al menos un producto',
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        let valid = true;
        let c = 0;

        rows.forEach(row => {
            const prodId = row.querySelector('.pr-select').value;
            const price = parseFloat(row.querySelector('.pr-price').value);
            const qty = parseInt(row.querySelector('.pr-qty').value);
            
            if (!prodId) {
                valid = false;
                errorMsg.textContent = 'Asegúrese de seleccionar un producto en todas las filas.';
            }

            if (qty < 1) {
                valid = false;
                errorMsg.textContent = 'La cantidad debe ser mayor a 0.';
            }

            if (valid && prodId) {
                hiddenInputs.innerHTML += `<input type="hidden" name="productos[${c}][id]" value="${prodId}">`;
                hiddenInputs.innerHTML += `<input type="hidden" name="productos[${c}][cantidad]" value="${qty}">`;
                hiddenInputs.innerHTML += `<input type="hidden" name="productos[${c}][precio]" value="${price}">`;
                c++;
            }
        });

        if (!valid) {
            errorDiv.classList.remove('d-none');
        } else {
            errorDiv.classList.add('d-none');
        }

        return valid;
    }

    // Agregar primera fila por defecto
    document.addEventListener('DOMContentLoaded', () => {
        addRow();
    });
</script>
@endpush
@endsection
