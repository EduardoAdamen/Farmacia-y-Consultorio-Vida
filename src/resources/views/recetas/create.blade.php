@extends('layouts.app')

@section('title', 'Generar Receta Médica')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0 font-outfit">Generar Receta Médica</h4>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <h5 class="text-accent">Datos de la Consulta</h5>
            <p><strong>Paciente:</strong> {{ $consulta->expediente->nombre_completo }}</p>
            <p><strong>Fecha:</strong> {{ $consulta->fecha_hora->format('d/m/Y H:i') }}</p>
            <p><strong>Diagnóstico:</strong> {{ $consulta->diagnostico }}</p>
        </div>

        <form action="{{ route('recetas.store', $consulta->id) }}" method="POST" id="recetaForm" novalidate>
            @csrf
            
            <h5 class="text-accent mb-3">Medicamentos</h5>
            <div class="table-responsive">
                <table class="table table-hover" id="tablaMedicamentos">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Dosis</th>
                            <th>Frecuencia</th>
                            <th>Duración</th>
                            <th>Indicaciones Esp.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="listaMedicamentos">
                        <tr>
                            <td class="position-relative">
                                <input type="text" name="medicamentos[0][nombre_medicamento]" class="form-control input-medicamento" required placeholder="Clic para ver catálogo de medicamentos..." autocomplete="off">
                                <input type="hidden" name="medicamentos[0][producto_id]" class="input-producto-id" value="">
                                <div class="dropdown-productos list-group position-absolute w-100 d-none shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
                            </td>
                            <td><input type="text" name="medicamentos[0][dosis]" class="form-control" required placeholder="Ej. 1 tableta"></td>
                            <td><input type="text" name="medicamentos[0][frecuencia]" class="form-control" required placeholder="Ej. Cada 8 horas"></td>
                            <td><input type="text" name="medicamentos[0][duracion]" class="form-control" required placeholder="Ej. 5 días"></td>
                            <td><input type="text" name="medicamentos[0][indicaciones_especificas]" class="form-control" placeholder="Opcional"></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-fila" disabled><i data-lucide="trash-2"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <button type="button" class="btn btn-sm btn-outline-secondary mb-4" id="btnAgregarMedicamento">
                <i data-lucide="plus"></i> Agregar Medicamento
            </button>

            <div class="mb-4">
                <label for="indicaciones" class="form-label">Indicaciones Generales</label>
                <textarea name="indicaciones" id="indicaciones" rows="3" class="form-control"></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('consultas.show', $consulta->id) }}" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-accent"><i data-lucide="save"></i> Generar Receta</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let medIndex = 1;
    const btnAgregar = document.getElementById('btnAgregarMedicamento');
    const lista = document.getElementById('listaMedicamentos');
    // URL del endpoint de búsqueda (con acceso de médico habilitado)
    const BUSCAR_URL = '{{ route("productos.buscar-ajax") }}';

    // ── Buscar y renderizar productos en el dropdown ──────────────────────
    function buscarProductos(input, query) {
        const td = input.closest('td');
        const dropdown = td.querySelector('.dropdown-productos');

        dropdown.innerHTML = `<div class="list-group-item text-muted d-flex align-items-center gap-2" style="font-size:13px;">
            <span class="spinner-border spinner-border-sm"></span> Buscando...
        </div>`;
        dropdown.classList.remove('d-none');

        fetch(`${BUSCAR_URL}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                dropdown.innerHTML = '';
                if (data.length === 0) {
                    dropdown.innerHTML = `<div class="list-group-item text-muted" style="font-size:13px;">
                        <i>No se encontraron productos. Puede escribir el nombre manualmente.</i>
                    </div>`;
                    return;
                }
                // Separador de sección si hay stock
                const conStock = data.filter(p => p.stock_total > 0);
                const sinStock = data.filter(p => p.stock_total <= 0);

                if (conStock.length > 0) {
                    const sep = document.createElement('div');
                    sep.className = 'list-group-item bg-light py-1';
                    sep.style.cssText = 'font-size:10px;font-weight:700;text-transform:uppercase;color:var(--color-text-muted);letter-spacing:.05em;';
                    sep.textContent = 'En Inventario';
                    dropdown.appendChild(sep);
                    conStock.forEach(prod => dropdown.appendChild(crearOpcion(input, prod)));
                }
                if (sinStock.length > 0) {
                    const sep = document.createElement('div');
                    sep.className = 'list-group-item bg-light py-1';
                    sep.style.cssText = 'font-size:10px;font-weight:700;text-transform:uppercase;color:var(--color-text-muted);letter-spacing:.05em;';
                    sep.textContent = 'Sin Stock';
                    dropdown.appendChild(sep);
                    sinStock.forEach(prod => dropdown.appendChild(crearOpcion(input, prod)));
                }

                dropdown.classList.remove('d-none');
            })
            .catch(() => {
                dropdown.innerHTML = `<div class="list-group-item text-danger" style="font-size:13px;">Error al cargar productos.</div>`;
            });
    }

    function crearOpcion(input, prod) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'list-group-item list-group-item-action p-2 text-start';
        const stockBadge = prod.stock_total > 5
            ? `<span class="badge bg-success ms-1">${prod.stock_total}</span>`
            : prod.stock_total > 0
            ? `<span class="badge bg-warning text-dark ms-1">${prod.stock_total}</span>`
            : `<span class="badge bg-secondary ms-1">Sin stock</span>`;
        btn.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size:13px;font-weight:600">${prod.nombre}</div>
                    <div style="font-size:11px;color:var(--color-text-muted);">${prod.categoria?.nombre || 'General'}</div>
                </div>
                ${stockBadge}
            </div>`;
        btn.addEventListener('click', () => {
            const td = input.closest('td');
            input.value = prod.nombre;
            td.querySelector('.input-producto-id').value = prod.id;
            // Indicador visual de que el medicamento fue vinculado al inventario
            input.style.borderColor = 'var(--color-accent)';
            td.querySelector('.dropdown-productos').classList.add('d-none');
        });
        return btn;
    }

    // ── Eventos de focus e input en la tabla (delegados) ──────────────────
    let debounceTimer;

    lista.addEventListener('focusin', function(e) {
        if (e.target.classList.contains('input-medicamento')) {
            // Muestra el catálogo completo al enfocar el campo
            buscarProductos(e.target, e.target.value.trim());
        }
    });

    lista.addEventListener('input', function(e) {
        if (e.target.classList.contains('input-medicamento')) {
            clearTimeout(debounceTimer);
            const input = e.target;
            const query = input.value.trim();
            const td = input.closest('td');

            // Si edita el texto, desvincula el producto_id anterior
            td.querySelector('.input-producto-id').value = '';
            input.style.borderColor = '';

            debounceTimer = setTimeout(() => buscarProductos(input, query), 300);
        }
    });

    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', (e) => {
        if (!e.target.closest('td.position-relative')) {
            document.querySelectorAll('.dropdown-productos').forEach(d => d.classList.add('d-none'));
        }
    });

    // ── Eliminar fila ─────────────────────────────────────────────────────
    lista.addEventListener('click', function(e) {
        if (e.target.closest('.btn-eliminar-fila')) {
            const tr = e.target.closest('tr');
            if (lista.children.length > 1) {
                tr.remove();
                actualizarBotonesEliminar();
            }
        }
    });

    // ── Agregar nueva fila de medicamento ─────────────────────────────────
    btnAgregar.addEventListener('click', function() {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="position-relative">
                <input type="text" name="medicamentos[${medIndex}][nombre_medicamento]" class="form-control input-medicamento" required placeholder="Clic para ver catálogo..." autocomplete="off">
                <input type="hidden" name="medicamentos[${medIndex}][producto_id]" class="input-producto-id" value="">
                <div class="dropdown-productos list-group position-absolute w-100 d-none shadow-sm" style="z-index:1000;max-height:220px;overflow-y:auto;"></div>
            </td>
            <td><input type="text" name="medicamentos[${medIndex}][dosis]" class="form-control" required placeholder="Ej. 1 tableta"></td>
            <td><input type="text" name="medicamentos[${medIndex}][frecuencia]" class="form-control" required placeholder="Ej. Cada 8 horas"></td>
            <td><input type="text" name="medicamentos[${medIndex}][duracion]" class="form-control" required placeholder="Ej. 5 días"></td>
            <td><input type="text" name="medicamentos[${medIndex}][indicaciones_especificas]" class="form-control" placeholder="Opcional"></td>
            <td>
                <button type="button" class="btn btn-sm text-danger btn-eliminar-fila"><i data-lucide="trash-2"></i></button>
            </td>`;
        lista.appendChild(tr);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        actualizarBotonesEliminar();
        // Enfocar automáticamente el nuevo campo y mostrar catálogo
        const newInput = tr.querySelector('.input-medicamento');
        newInput.focus();
        medIndex++;
    });

    function actualizarBotonesEliminar() {
        const botones = lista.querySelectorAll('.btn-eliminar-fila');
        botones.forEach((btn, i) => {
            btn.disabled = (botones.length === 1);
            btn.classList.toggle('btn-outline-danger', botones.length === 1);
            btn.classList.toggle('text-danger', botones.length > 1);
        });
    }

    // Validar manualmente (novalidate en el form deshabilita la validación nativa del navegador)
    const form = document.getElementById('recetaForm');
    form.addEventListener('submit', function(e) {
        const rows = lista.querySelectorAll('tr');
        let count = 0;
        let camposVacios = false;

        rows.forEach(row => {
            const medName = row.querySelector('.input-medicamento').value.trim();
            if (medName) {
                count++;
                // Verificar campos obligatorios de la fila (dosis, frecuencia, duración)
                const dosis     = row.querySelector('input[name*="[dosis]"]').value.trim();
                const frecuencia= row.querySelector('input[name*="[frecuencia]"]').value.trim();
                const duracion  = row.querySelector('input[name*="[duracion]"]').value.trim();
                if (!dosis || !frecuencia || !duracion) {
                    camposVacios = true;
                }
            }
        });

        if (count === 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Atención',
                text: 'Debe ingresar al menos un medicamento para generar la receta.',
                icon: 'warning',
                confirmButtonColor: '#0D9488',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        if (camposVacios) {
            e.preventDefault();
            Swal.fire({
                title: 'Atención',
                text: 'Complete los campos de Dosis, Frecuencia y Duración en todos los medicamentos.',
                icon: 'warning',
                confirmButtonColor: '#0D9488',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }
    });
});
</script>
@endsection

