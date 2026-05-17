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

        <form action="{{ route('recetas.store', $consulta->id) }}" method="POST" id="recetaForm">
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
                                <input type="text" name="medicamentos[0][nombre_medicamento]" class="form-control input-medicamento" required placeholder="Ej. Paracetamol 500mg" autocomplete="off">
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

    btnAgregar.addEventListener('click', function() {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="position-relative">
                <input type="text" name="medicamentos[${medIndex}][nombre_medicamento]" class="form-control input-medicamento" required placeholder="Ej. Amoxicilina" autocomplete="off">
                <input type="hidden" name="medicamentos[${medIndex}][producto_id]" class="input-producto-id" value="">
                <div class="dropdown-productos list-group position-absolute w-100 d-none shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
            </td>
            <td><input type="text" name="medicamentos[${medIndex}][dosis]" class="form-control" required placeholder="Ej. 1 capsula"></td>
            <td><input type="text" name="medicamentos[${medIndex}][frecuencia]" class="form-control" required placeholder="Ej. Cada 12 horas"></td>
            <td><input type="text" name="medicamentos[${medIndex}][duracion]" class="form-control" required placeholder="Ej. 7 días"></td>
            <td><input type="text" name="medicamentos[${medIndex}][indicaciones_especificas]" class="form-control" placeholder="Opcional"></td>
            <td>
                <button type="button" class="btn btn-sm text-danger btn-eliminar-fila"><i data-lucide="trash-2"></i></button>
            </td>
        `;
        lista.appendChild(tr);
        if(typeof lucide !== 'undefined') lucide.createIcons();
        actualizarBotonesEliminar();
        medIndex++;
    });

    let debounceTimerProd;
    lista.addEventListener('input', function(e) {
        if(e.target.classList.contains('input-medicamento')) {
            clearTimeout(debounceTimerProd);
            const input = e.target;
            const query = input.value.trim();
            const td = input.closest('td');
            const dropdown = td.querySelector('.dropdown-productos');
            const idInput = td.querySelector('.input-producto-id');
            
            // Si el usuario edita el texto, reseteamos el producto_id
            idInput.value = '';

            if(query.length < 2) {
                dropdown.classList.add('d-none');
                return;
            }

            debounceTimerProd = setTimeout(() => {
                fetch(`/productos/buscar?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        dropdown.innerHTML = '';
                        if(data.length > 0) {
                            data.forEach(prod => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'list-group-item list-group-item-action p-2 text-start';
                                btn.innerHTML = `<div style="font-size:13px;font-weight:600">${prod.nombre}</div><div style="font-size:11px;color:var(--color-text-muted);">${prod.categoria?.nombre || 'General'}</div>`;
                                btn.addEventListener('click', () => {
                                    input.value = prod.nombre;
                                    idInput.value = prod.id;
                                    dropdown.classList.add('d-none');
                                });
                                dropdown.appendChild(btn);
                            });
                            dropdown.classList.remove('d-none');
                        } else {
                            dropdown.classList.add('d-none');
                        }
                    });
            }, 300);
        }
    });

    document.addEventListener('click', (e) => {
        if(!e.target.closest('td.position-relative')) {
            document.querySelectorAll('.dropdown-productos').forEach(d => d.classList.add('d-none'));
        }
    });

    lista.addEventListener('click', function(e) {
        if(e.target.closest('.btn-eliminar-fila')) {
            const tr = e.target.closest('tr');
            if(lista.children.length > 1) {
                tr.remove();
                actualizarBotonesEliminar();
            }
        }
    });

    function actualizarBotonesEliminar() {
        const botones = lista.querySelectorAll('.btn-eliminar-fila');
        if(botones.length === 1) {
            botones[0].disabled = true;
            botones[0].classList.add('btn-outline-danger');
            botones[0].classList.remove('text-danger');
        } else {
            botones.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('btn-outline-danger');
                btn.classList.add('text-danger');
            });
        }
    }
});
</script>
@endsection
