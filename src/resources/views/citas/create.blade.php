@extends('layouts.app')
@section('title', 'Nueva Cita')
@section('page-title', 'Programar Cita')

@section('content')
<div class="card max-w-2xl mx-auto" style="max-width: 800px;">
    <div class="card-header py-3 px-4">
        <h5 class="mb-0 fw-bold">Detalles de la Cita</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('citas.store') }}" method="POST" id="form-cita">
            @csrf
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Médico <span class="text-danger">*</span></label>
                    <select name="medico_id" id="medico_id" class="form-select @error('medico_id') is-invalid @enderror" required {{ $medicoId ? 'disabled' : '' }}>
                        <option value="">Seleccione un médico</option>
                        @foreach($medicos as $m)
                            <option value="{{ $m->id }}" {{ (old('medico_id', $medicoId) == $m->id) ? 'selected' : '' }}>
                                {{ $m->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                    @if($medicoId)
                        <input type="hidden" name="medico_id" value="{{ $medicoId }}">
                    @endif
                    @error('medico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Fecha <span class="text-danger">*</span></label>
                    <input type="date" name="fecha" id="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', now()->toDateString()) }}" required min="{{ now()->toDateString() }}">
                    @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Hora <span class="text-danger">*</span></label>
                    <input type="time" name="hora" id="hora" class="form-control @error('hora') is-invalid @enderror" value="{{ old('hora') }}" required step="1800">
                    @error('hora') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Alerta de disponibilidad AJAX --}}
            <div id="alerta-disponibilidad" class="alert alert-warning d-none mb-4">
                <div class="d-flex align-items-center gap-2 fw-bold text-danger mb-2">
                    <i data-lucide="alert-triangle" style="width:18px;height:18px;"></i>
                    El horario solicitado no está disponible.
                </div>
                <div style="font-size: 13px;">
                    Próximos horarios disponibles para este médico el mismo día:
                    <ul id="lista-proximos" class="mb-0 mt-1"></ul>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Paciente</label>
                    <div class="text-muted" style="font-size: 12px; margin-bottom: 8px;">
                        Busque el expediente escribiendo el nombre o ID del paciente. Si es primera vez, use el campo de nombre temporal.
                    </div>

                    {{-- Tarjeta del expediente seleccionado --}}
                    <div id="expediente_seleccionado" class="d-none mb-3 p-3 rounded-3 d-flex justify-content-between align-items-center"
                         style="background:#f0fdf4;border:1px solid #86efac;">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:40px;height:40px;border-radius:10px;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i data-lucide="user-check" style="width:20px;height:20px;color:#fff;"></i>
                            </div>
                            <div>
                                <div id="exp_nombre" class="fw-bold" style="font-size:15px;color:#15803d;"></div>
                                <div id="exp_detalle" class="text-muted" style="font-size:12px;"></div>
                            </div>
                        </div>
                        <button type="button" id="btn_limpiar_exp" class="btn btn-sm btn-outline-danger" title="Quitar expediente">
                            <i data-lucide="x" style="width:14px;height:14px;"></i> Quitar
                        </button>
                    </div>

                    <div id="fila_busqueda">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="input-group position-relative">
                                    <span class="input-group-text bg-white" id="icono_buscar" style="border-right:none;">
                                        <i data-lucide="search" style="width:15px;height:15px;color:var(--color-text-muted);"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="buscar_expediente"
                                           placeholder="Clic para buscar por nombre o ID..." autocomplete="off">
                                    <input type="hidden" name="expediente_id" id="expediente_id" value="{{ old('expediente_id') }}">
                                    <div id="dropdown_resultados" class="list-group position-absolute shadow-sm d-none"
                                         style="z-index:1050;max-height:260px;overflow-y:auto;top:100%;left:0;right:0;margin-top:2px;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="nombre_temporal" id="nombre_temporal"
                                       class="form-control @error('nombre_temporal') is-invalid @enderror"
                                       value="{{ old('nombre_temporal') }}"
                                       placeholder="O escriba nombre si no tiene expediente">
                                <div class="form-text">Solo si no existe expediente registrado.</div>
                            </div>
                        </div>
                    </div>
                    @error('paciente') <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Motivo de la Cita <span class="text-danger">*</span></label>
                <textarea name="motivo" class="form-control @error('motivo') is-invalid @enderror" rows="3" required>{{ old('motivo') }}</textarea>
                @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('citas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-accent d-flex align-items-center gap-2" id="btn-guardar">
                    <i data-lucide="save" style="width:18px;height:18px;"></i> Programar Cita
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectMedico = document.getElementById('medico_id');
        const inputFecha = document.getElementById('fecha');
        const inputHora = document.getElementById('hora');
        const alertaDisp = document.getElementById('alerta-disponibilidad');
        const listaProximos = document.getElementById('lista-proximos');
        const btnGuardar = document.getElementById('btn-guardar');

        function verificarDisponibilidad() {
            const medicoId = selectMedico.value;
            const fecha = inputFecha.value;
            const hora = inputHora.value;

            if (!medicoId || !fecha || !hora) return;

            // Simple validation to ensure it's not a past time if today
            const now = new Date();
            const selectedDate = new Date(fecha + 'T' + hora);
            
            if (selectedDate < now && fecha === now.toISOString().split('T')[0]) {
                // ignoring for now, let server validate if needed, or just show warning
            }

            fetch(`{{ route('citas.verificar-disponibilidad') }}?medico_id=${medicoId}&fecha=${fecha}&hora=${hora}`)
                .then(response => response.json())
                .then(data => {
                    if (data.disponible) {
                        alertaDisp.classList.add('d-none');
                        btnGuardar.disabled = false;
                    } else {
                        alertaDisp.classList.remove('d-none');
                        btnGuardar.disabled = true;
                        listaProximos.innerHTML = '';
                        if (data.proximos && data.proximos.length > 0) {
                            data.proximos.forEach(h => {
                                const li = document.createElement('li');
                                li.innerHTML = `<a href="#" class="text-decoration-none use-time" data-time="${h}">${h}</a>`;
                                listaProximos.appendChild(li);
                            });
                        } else {
                            listaProximos.innerHTML = '<li>No hay horarios disponibles para el resto del día.</li>';
                        }
                    }
                });
        }

        selectMedico.addEventListener('change', verificarDisponibilidad);
        inputFecha.addEventListener('change', verificarDisponibilidad);
        inputHora.addEventListener('change', verificarDisponibilidad);

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('use-time')) {
                e.preventDefault();
                inputHora.value = e.target.getAttribute('data-time');
                verificarDisponibilidad();
            }
        });

        // ── Búsqueda de expedientes ───────────────────────────────────────
        const inputBuscar      = document.getElementById('buscar_expediente');
        const inputId          = document.getElementById('expediente_id');
        const dropdownResultados = document.getElementById('dropdown_resultados');
        const cardSeleccionado = document.getElementById('expediente_seleccionado');
        const expNombre        = document.getElementById('exp_nombre');
        const expDetalle       = document.getElementById('exp_detalle');
        const filaBusqueda     = document.getElementById('fila_busqueda');
        const btnLimpiar       = document.getElementById('btn_limpiar_exp');
        const iconoBuscar      = document.getElementById('icono_buscar');
        const BUSCAR_EXP_URL   = '{{ route("expedientes.buscar-ajax") }}';
        let debounceTimerExp;

        function buscarExpedientes(query) {
            iconoBuscar.innerHTML = '<span class="spinner-border spinner-border-sm text-muted"></span>';
            dropdownResultados.innerHTML = '';
            dropdownResultados.classList.remove('d-none');

            fetch(`${BUSCAR_EXP_URL}?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    iconoBuscar.innerHTML = '<i data-lucide="search" style="width:15px;height:15px;color:var(--color-text-muted);"></i>';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    dropdownResultados.innerHTML = '';

                    if (data.length === 0) {
                        dropdownResultados.innerHTML = `<div class="list-group-item text-muted py-3 text-center" style="font-size:13px;">
                            <i>No se encontraron expedientes.</i><br><small>Puede usar el nombre temporal.</small>
                        </div>`;
                        return;
                    }

                    const header = document.createElement('div');
                    header.className = 'list-group-item bg-light py-1';
                    header.style.cssText = 'font-size:10px;font-weight:700;text-transform:uppercase;color:var(--color-text-muted);letter-spacing:.05em;';
                    header.textContent = `${data.length} expediente(s) encontrado(s)`;
                    dropdownResultados.appendChild(header);

                    data.forEach(exp => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action p-3 text-start';
                        btn.innerHTML = `
                            <div class="d-flex align-items-center gap-3">
                                <div style="width:36px;height:36px;border-radius:8px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i data-lucide="user" style="width:18px;height:18px;color:#0ea5e9;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:14px;">${exp.nombre_completo}</div>
                                    <div class="text-muted" style="font-size:11px;">ID: ${String(exp.id).padStart(5,'0')}${exp.telefono ? ' · ' + exp.telefono : ''}</div>
                                </div>
                            </div>`;
                        btn.addEventListener('click', () => seleccionarExpediente(exp));
                        dropdownResultados.appendChild(btn);
                    });

                    if (typeof lucide !== 'undefined') lucide.createIcons();
                })
                .catch(() => {
                    iconoBuscar.innerHTML = '<i data-lucide="search" style="width:15px;height:15px;"></i>';
                    dropdownResultados.innerHTML = `<div class="list-group-item text-danger" style="font-size:13px;">Error al buscar expedientes.</div>`;
                });
        }

        function seleccionarExpediente(exp) {
            inputId.value = exp.id;
            expNombre.textContent = exp.nombre_completo;
            expDetalle.textContent = `ID: ${String(exp.id).padStart(5,'0')}${exp.telefono ? ' · Tel: ' + exp.telefono : ''}`;
            cardSeleccionado.classList.remove('d-none');
            filaBusqueda.classList.add('d-none');
            dropdownResultados.classList.add('d-none');
            document.getElementById('nombre_temporal').value = '';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function limpiarSeleccion() {
            inputId.value = '';
            inputBuscar.value = '';
            cardSeleccionado.classList.add('d-none');
            filaBusqueda.classList.remove('d-none');
            dropdownResultados.classList.add('d-none');
            setTimeout(() => inputBuscar.focus(), 50);
        }

        // Cargar catálogo al hacer focus
        inputBuscar.addEventListener('focus', () => buscarExpedientes(inputBuscar.value.trim()));

        // Filtrar mientras escribe
        inputBuscar.addEventListener('input', function() {
            clearTimeout(debounceTimerExp);
            debounceTimerExp = setTimeout(() => buscarExpedientes(this.value.trim()), 300);
        });

        btnLimpiar.addEventListener('click', limpiarSeleccion);

        // Cerrar dropdown al hacer click fuera
        document.addEventListener('click', (e) => {
            if (!inputBuscar.contains(e.target) && !dropdownResultados.contains(e.target)) {
                dropdownResultados.classList.add('d-none');
            }
        });
    });
</script>
@endpush
@endsection
