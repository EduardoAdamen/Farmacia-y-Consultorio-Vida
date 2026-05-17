@extends('layouts.app')
@section('title', 'Punto de Venta')

@push('styles')
<style>
    .pos-col { height: calc(100vh - 140px); overflow-y: auto; }
    .search-result-item:hover { background-color: var(--color-secondary); cursor: pointer; }
    .receta-input { font-family: 'DM Mono', monospace; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;">Registrar Venta</h5>
    </div>
</div>

<div class="row g-4" id="pos-app">
    {{-- Columna Izquierda: Búsqueda --}}
    <div class="col-md-5 pos-col mt-3">
        <a href="{{ route('ventas.historial') }}" class="btn btn-sm btn-outline-secondary mb-3">
            <i data-lucide="history" style="width:16px;height:16px;"></i> Ver Historial de Ventas
        </a>
        <div class="card h-100">
            <div class="card-header p-3">
                <div class="position-relative">
                    <i data-lucide="search" class="position-absolute" style="left:12px; top:10px; color:var(--color-text-muted); width:18px; height:18px;"></i>
                    <input type="text" id="searchInput" class="form-control ps-5" placeholder="Buscar por nombre o categoría..." autocomplete="off" autofocus>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="searchResults" class="list-group list-group-flush rounded-0">
                    <div class="p-4 text-center text-muted" id="searchEmptyState">
                        <i data-lucide="package-search" style="width:40px;height:40px;opacity:0.5;" class="mb-2"></i>
                        <div>Busca un producto para empezar</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Columna Derecha: Carrito --}}
    <div class="col-md-7 pos-col d-flex flex-column">
        <div class="card flex-grow-1 d-flex flex-column">
            <div class="card-header p-3 d-flex justify-content-between align-items-center">
                <span>Carrito de Venta</span>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnVaciarCarrito">Vaciar</button>
            </div>
            
            <div class="card-body p-0 flex-grow-1" style="overflow-y: auto;">
                <table class="table table-hover mb-0" id="cartTable">
                    <thead style="position: sticky; top: 0; background: var(--color-surface); z-index: 10;">
                        <tr>
                            <th>Producto</th>
                            <th width="100">Cant.</th>
                            <th width="100">P. Unit ($)</th>
                            <th width="100">Desc. (%)</th>
                            <th width="120" class="text-end">Subtotal</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        <tr id="cartEmptyRow">
                            <td colspan="6" class="text-center text-muted p-4">El carrito está vacío</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer p-3 bg-light border-top">
                <div id="recetaSection" class="mb-3 d-none p-3 border rounded" style="background:#fff; border-color:var(--color-warning)!important;">
                    <div class="d-flex align-items-center gap-2 mb-2 text-warning fw-bold">
                        <i data-lucide="alert-circle" style="width:16px;height:16px;"></i>
                        Requiere Receta Médica
                    </div>
                    <div id="recetaInputsContainer">
                        <!-- Inputs de folio de receta se inyectan acá -->
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label text-muted mb-1" style="font-size:12px;">Monto Recibido ($)</label>
                            <input type="number" id="montoRecibido" class="form-control form-control-lg fw-bold" placeholder="0.00" min="0" step="0.01">
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted" style="font-size:13px;">Cambio:</span>
                            <span id="cambioCalculado" class="fw-bold" style="font-size:16px; font-family:'Outfit'">$0.00</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="mb-1 text-muted" style="font-size:13px;">Total a Cobrar</div>
                        <div id="totalCobrar" class="mb-3 fw-bold" style="font-size:32px; font-family:'Outfit'; color:var(--color-accent)">
                            $0.00
                        </div>
                        <button type="button" id="btnCobrar" class="btn btn-accent btn-lg w-100 d-flex justify-content-center align-items-center gap-2" disabled>
                            <i data-lucide="check-circle" style="width:20px;height:20px;"></i>
                            REGISTRAR VENTA
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Template para resultados de búsqueda --}}
<template id="tplSearchResult">
    <div class="list-group-item list-group-item-action p-3 search-result-item border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-bold mb-1 product-name"></div>
                <div class="d-flex gap-2 align-items-center" style="font-size:12px;">
                    <span class="text-muted product-category"></span>
                    <span class="badge product-stock"></span>
                    <span class="badge bg-warning text-dark product-receta d-none"><i data-lucide="file-text" style="width:12px;height:12px;margin-right:2px;"></i>Receta</span>
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold" style="font-size:16px; font-family:'Outfit'">$<span class="product-price"></span></div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-1 btn-add-cart">Añadir</button>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    let carrito = [];
    let debounceTimer;
    
    // Elementos DOM
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const cartBody = document.getElementById('cartBody');
    const cartEmptyRow = document.getElementById('cartEmptyRow');
    const tplSearchResult = document.getElementById('tplSearchResult');
    const btnVaciar = document.getElementById('btnVaciarCarrito');
    const totalCobrarEl = document.getElementById('totalCobrar');
    const montoRecibidoEl = document.getElementById('montoRecibido');
    const cambioCalculadoEl = document.getElementById('cambioCalculado');
    const btnCobrar = document.getElementById('btnCobrar');
    const recetaSection = document.getElementById('recetaSection');
    const recetaInputsCont = document.getElementById('recetaInputsContainer');

    // Búsqueda AJAX
    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            searchResults.innerHTML = '<div class="p-4 text-center text-muted"><i data-lucide="package-search" style="width:40px;height:40px;opacity:0.5;" class="mb-2"></i><div>Busca un producto para empezar</div></div>';
            lucide.createIcons();
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/productos/buscar?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length === 0) {
                        searchResults.innerHTML = '<div class="p-4 text-center text-muted">No se encontraron productos en stock.</div>';
                        return;
                    }
                    
                    data.forEach(prod => {
                        const tpl = tplSearchResult.content.cloneNode(true);
                        tpl.querySelector('.product-name').textContent = prod.nombre;
                        tpl.querySelector('.product-category').textContent = prod.categoria?.nombre || '';
                        
                        const stockBadge = tpl.querySelector('.product-stock');
                        stockBadge.textContent = 'Stock: ' + prod.stock_total;
                        stockBadge.className = 'badge ' + (prod.stock_total > 5 ? 'bg-success' : 'bg-danger');
                        
                        tpl.querySelector('.product-price').textContent = parseFloat(prod.precio_venta).toFixed(2);
                        
                        if (prod.requiere_receta) {
                            tpl.querySelector('.product-receta').classList.remove('d-none');
                        }
                        
                        const btnAdd = tpl.querySelector('.btn-add-cart');
                        btnAdd.addEventListener('click', () => agregarAlCarrito(prod));
                        
                        searchResults.appendChild(tpl);
                    });
                    lucide.createIcons();
                });
        }, 300);
    });

    // Agregar al carrito
    function agregarAlCarrito(prod) {
        const itemExistente = carrito.find(i => i.id === prod.id);
        
        if (itemExistente) {
            if (itemExistente.cant < prod.stock_total) {
                itemExistente.cant++;
            } else {
                Swal.fire({
                    title: 'Stock máximo',
                    text: 'Se ha alcanzado el límite de stock para este producto.',
                    icon: 'warning',
                    confirmButtonColor: '#F59E0B'
                });
            }
        } else {
            carrito.push({
                ...prod,
                cant: 1,
                desc: 0,
                receta_folio: ''
            });
        }
        
        renderCarrito();
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input')); // Limpiar resultados
        searchInput.focus();
    }

    // Renderizar carrito
    function renderCarrito() {
        if (carrito.length === 0) {
            cartBody.innerHTML = '';
            cartBody.appendChild(cartEmptyRow);
            recetaSection.classList.add('d-none');
            recetaInputsCont.innerHTML = '';
            actualizarTotales();
            return;
        }

        cartBody.innerHTML = '';
        let requiereRecetaMain = false;
        recetaInputsCont.innerHTML = '';

        carrito.forEach((item, index) => {
            const tr = document.createElement('tr');
            
            const pUnit = parseFloat(item.precio_venta);
            const subtotal = (pUnit * item.cant) * (1 - item.desc/100);
            
            tr.innerHTML = `
                <td>
                    <div style="font-size:13px;font-weight:600">${item.nombre}</div>
                    ${item.requiere_receta ? '<span class="badge bg-warning text-dark" style="font-size:10px">RX</span>' : ''}
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-center cart-cant" value="${item.cant}" min="1" max="${item.stock_total}" data-idx="${index}">
                </td>
                <td class="text-end font-monospace" style="font-size:13px;">$${pUnit.toFixed(2)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm text-center cart-desc" value="${item.desc}" min="0" max="100" data-idx="${index}">
                </td>
                <td class="text-end fw-bold font-monospace" style="font-size:14px;">$${subtotal.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 cart-remove" data-idx="${index}">
                        <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
                    </button>
                </td>
            `;

            if (item.requiere_receta) {
                requiereRecetaMain = true;
                const recDiv = document.createElement('div');
                recDiv.className = 'mb-2';
                recDiv.innerHTML = `
                    <label class="form-label" style="font-size:12px; margin-bottom:2px;">Folio receta para: <strong>${item.nombre}</strong></label>
                    <input type="text" class="form-control form-control-sm receta-input" placeholder="REC-YYYYMMDD-0000" data-idx="${index}" value="${item.receta_folio}">
                `;
                recetaInputsCont.appendChild(recDiv);
            }

            cartBody.appendChild(tr);
        });

        if (requiereRecetaMain) {
            recetaSection.classList.remove('d-none');
        } else {
            recetaSection.classList.add('d-none');
        }

        // Attach listeners
        document.querySelectorAll('.cart-cant').forEach(el => {
            el.addEventListener('change', (e) => {
                let val = parseInt(e.target.value) || 1;
                const max = parseInt(e.target.max);
                if (val > max) val = max;
                if (val < 1) val = 1;
                carrito[e.target.dataset.idx].cant = val;
                renderCarrito();
            });
        });

        document.querySelectorAll('.cart-desc').forEach(el => {
            el.addEventListener('change', (e) => {
                let val = parseFloat(e.target.value) || 0;
                if (val > 100) val = 100;
                if (val < 0) val = 0;
                carrito[e.target.dataset.idx].desc = val;
                renderCarrito();
            });
        });

        document.querySelectorAll('.cart-remove').forEach(el => {
            el.addEventListener('click', (e) => {
                carrito.splice(e.currentTarget.dataset.idx, 1);
                renderCarrito();
            });
        });

        document.querySelectorAll('.receta-input').forEach(el => {
            el.addEventListener('input', (e) => {
                carrito[e.target.dataset.idx].receta_folio = e.target.value.trim();
                calcularCambio();
            });
        });

        lucide.createIcons();
        actualizarTotales();
    }

    // Calcular Totales
    function actualizarTotales() {
        let total = 0;
        carrito.forEach(item => {
            total += (item.precio_venta * item.cant) * (1 - item.desc/100);
        });

        totalCobrarEl.textContent = '$' + total.toFixed(2);
        totalCobrarEl.dataset.total = total;

        calcularCambio();
    }

    function calcularCambio() {
        const total = parseFloat(totalCobrarEl.dataset.total) || 0;
        const recibido = parseFloat(montoRecibidoEl.value) || 0;
        
        const faltanRecetas = carrito.some(i => i.requiere_receta && !i.receta_folio);
        
        btnCobrar.disabled = carrito.length === 0 || (total > 0 && recibido < total) || faltanRecetas;

        if (recibido >= total && total > 0) {
            cambioCalculadoEl.textContent = '$' + (recibido - total).toFixed(2);
            cambioCalculadoEl.classList.remove('text-danger');
            cambioCalculadoEl.classList.add('text-success');
        } else {
            cambioCalculadoEl.textContent = '$0.00';
            cambioCalculadoEl.classList.add('text-danger');
            cambioCalculadoEl.classList.remove('text-success');
        }
    }

    // Listeners generales
    montoRecibidoEl.addEventListener('input', calcularCambio);
    
    btnVaciar.addEventListener('click', () => {
        if(carrito.length === 0) return;
        
        Swal.fire({
            title: '¿Vaciar Carrito?',
            text: 'Se eliminarán todos los productos actuales.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F43F5E',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Sí, vaciar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                carrito = [];
                renderCarrito();
                montoRecibidoEl.value = '';
            }
        });
    });

    btnCobrar.addEventListener('click', () => {
        if (carrito.length === 0) return;

        // Validar recetas
        const faltanRecetas = carrito.some(i => i.requiere_receta && !i.receta_folio);
        if (faltanRecetas) {
            Swal.fire({
                title: 'Faltan recetas',
                text: 'Debes ingresar el folio de receta para los medicamentos controlados.',
                icon: 'error',
                confirmButtonColor: '#F43F5E'
            });
            return;
        }

        const data = {
            items: carrito,
            monto_recibido: parseFloat(montoRecibidoEl.value)
        };

        btnCobrar.disabled = true;
        btnCobrar.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Procesando...';
        lucide.createIcons();

        fetch('/ventas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                window.location.href = res.redirect;
            } else {
                Swal.fire({
                    title: 'Error al registrar',
                    text: res.message,
                    icon: 'error',
                    confirmButtonColor: '#F43F5E'
                });
                btnCobrar.disabled = false;
                btnCobrar.innerHTML = '<i data-lucide="check-circle"></i> REGISTRAR VENTA';
                lucide.createIcons();
            }
        })
        .catch(err => {
            Swal.fire({
                title: 'Error de conectividad',
                text: 'Hubo un problema al comunicarse con el servidor.',
                icon: 'error',
                confirmButtonColor: '#F43F5E'
            });
            console.error(err);
            btnCobrar.disabled = false;
            btnCobrar.innerHTML = '<i data-lucide="check-circle"></i> REGISTRAR VENTA';
            lucide.createIcons();
        });
    });

    // Iniciar
    lucide.createIcons();
});
</script>
<style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .spin { animation: spin 1s linear infinite; }
</style>
@endpush
