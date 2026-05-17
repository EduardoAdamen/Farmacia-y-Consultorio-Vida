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
                    <div class="text-muted" style="font-size: 12px; margin-bottom: 8px;">Seleccione un expediente existente o ingrese un nombre temporal si es paciente de primera vez sin expediente.</div>
                    
                    <div class="row g-2">
                        <div class="col-md-6 position-relative">
                            <input type="text" class="form-control" id="buscar_expediente" placeholder="Buscar expediente (ID o nombre)..." autocomplete="off">
                            <input type="hidden" name="expediente_id" id="expediente_id" value="{{ old('expediente_id') }}">
                            <div id="resultado_expediente" class="mt-2 text-success fw-bold" style="font-size: 13px;"></div>
                            
                            {{-- Contenedor de resultados flotante --}}
                            <div id="dropdown_resultados" class="list-group position-absolute w-100 d-none shadow-sm" style="z-index: 1000; max-height: 250px; overflow-y: auto; margin-top: 2px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="nombre_temporal" id="nombre_temporal" class="form-control @error('nombre_temporal') is-invalid @enderror" value="{{ old('nombre_temporal') }}" placeholder="Nombre temporal (si no hay expediente)">
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

        // Búsqueda AJAX de expedientes
        const inputBuscar = document.getElementById('buscar_expediente');
        const inputId = document.getElementById('expediente_id');
        const resExpediente = document.getElementById('resultado_expediente');
        const dropdownResultados = document.getElementById('dropdown_resultados');
        let debounceTimerExp;
        
        inputBuscar.addEventListener('input', function() {
            clearTimeout(debounceTimerExp);
            const query = this.value.trim();
            
            if (query.length < 2) {
                dropdownResultados.classList.add('d-none');
                dropdownResultados.innerHTML = '';
                inputId.value = '';
                resExpediente.textContent = '';
                return;
            }

            debounceTimerExp = setTimeout(() => {
                fetch(`{{ route('expedientes.buscar-ajax') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        dropdownResultados.innerHTML = '';
                        if (data.length === 0) {
                            dropdownResultados.innerHTML = '<div class="list-group-item text-muted" style="font-size:13px;">No se encontraron expedientes.</div>';
                        } else {
                            data.forEach(exp => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'list-group-item list-group-item-action p-2 text-start';
                                btn.innerHTML = `
                                    <div class="fw-bold" style="font-size:14px; color:var(--color-primary);">${exp.nombre_completo}</div>
                                    <div class="text-muted" style="font-size:12px;">ID: ${exp.id.toString().padStart(5, '0')} | Tel: ${exp.telefono || 'N/E'}</div>
                                `;
                                btn.addEventListener('click', () => {
                                    inputId.value = exp.id;
                                    inputBuscar.value = exp.nombre_completo;
                                    resExpediente.textContent = `Expediente seleccionado: ${exp.nombre_completo} (ID: ${exp.id.toString().padStart(5, '0')})`;
                                    dropdownResultados.classList.add('d-none');
                                });
                                dropdownResultados.appendChild(btn);
                            });
                        }
                        dropdownResultados.classList.remove('d-none');
                    });
            }, 300);
        });

        // Ocultar dropdown al hacer click fuera
        document.addEventListener('click', (e) => {
            if (!inputBuscar.contains(e.target) && !dropdownResultados.contains(e.target)) {
                dropdownResultados.classList.add('d-none');
            }
        });
    });
</script>
@endpush
@endsection
