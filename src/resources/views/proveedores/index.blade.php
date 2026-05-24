@extends('layouts.app')
@section('title', 'Proveedores')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;">Gestión de Proveedores</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">Administre el directorio de sus proveedores.</p>
    </div>
    <a href="{{ route('proveedores.create') }}" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
        <i data-lucide="plus" style="width:16px;height:16px;"></i> Nuevo Proveedor
    </a>
</div>

<form method="GET" action="{{ route('proveedores.index') }}" class="d-flex gap-2 align-items-center flex-wrap mb-4 justify-content-end">
    <select name="filtro" class="form-select w-auto"
            style="width:220px;background-color:#fff;border:1px solid var(--color-border);border-radius:10px;font-size:13px;padding:9px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <option value="activos" {{ $filtro === 'activos' ? 'selected' : '' }}>Activos</option>
        <option value="inactivos" {{ $filtro === 'inactivos' ? 'selected' : '' }}>Inactivos</option>
        <option value="todos" {{ $filtro === 'todos' ? 'selected' : '' }}>Todos</option>
    </select>

    <button type="submit" class="btn btn-accent"
            style="font-size:13px;border-radius:10px;padding:9px 18px;font-weight:600;">
        Filtrar
    </button>

    @if($filtro !== 'activos')
    <a href="{{ route('proveedores.index') }}" class="btn btn-ghost"
       style="border-radius:10px;padding:9px 14px;font-size:13px;">
        Limpiar
    </a>
    @endif
</form>

<div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background:#F8FAFC;border-bottom:1px solid var(--color-border);font-size:12.5px;color:var(--color-text-muted);text-transform:uppercase;">
                <tr>
                    <th class="px-4 py-3">Empresa</th>
                    <th class="py-3">Contacto</th>
                    <th class="py-3">Teléfono</th>
                    <th class="py-3">Email / RFC</th>
                    <th class="py-3">Días de Visita</th>
                    <th class="py-3 text-center">Estado</th>
                    <th class="py-3 text-end px-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proveedores as $prov)
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td class="px-4 py-3 align-middle">
                        <div style="font-weight:600;font-size:13.5px;">{{ $prov->nombre_empresa }}</div>
                    </td>
                    <td class="py-3 align-middle" style="font-size:13.5px;color:var(--color-text-muted);">
                        {{ $prov->nombre_contacto }}
                    </td>
                    <td class="py-3 align-middle" style="font-size:13.5px;">
                        {{ $prov->telefono }}
                    </td>
                    <td class="py-3 align-middle">
                        <div style="font-size:12px;color:var(--color-text-muted);">{{ $prov->correo_electronico ?? 'N/D' }}</div>
                        <div style="font-size:12px;">{{ $prov->rfc ?? 'N/D' }}</div>
                    </td>
                    <td class="py-3 align-middle">
                        @if($prov->diasVisita->count() > 0)
                            <div class="d-flex flex-wrap gap-1">
                            @foreach($prov->diasVisita as $dv)
                                <span class="badge bg-secondary text-capitalize">{{ $dv->dia_semana }}</span>
                            @endforeach
                            </div>
                        @else
                            <span class="text-muted" style="font-size:12px;">Sin días asignados</span>
                        @endif
                    </td>
                    <td class="py-3 align-middle text-center">
                        @if($prov->estado === 'activo')
                            <span style="font-size:12px;color:var(--color-accent);background:#E0F2FE;padding:4px 10px;border-radius:20px;font-weight:600;">Activo</span>
                        @else
                            <span style="font-size:12px;color:var(--color-text-muted);background:#F1F5F9;padding:4px 10px;border-radius:20px;font-weight:600;">Inactivo</span>
                        @endif
                    </td>
                    <td class="py-3 text-end px-4 align-middle">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('proveedores.edit', $prov->id) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center" style="width:32px;height:32px;" title="Editar">
                                <i data-lucide="edit" style="width:16px;height:16px;"></i>
                            </a>
                            @if($prov->estado === 'activo')
                                <form action="{{ route('proveedores.destroy', $prov->id) }}" method="POST" class="mb-0 form-delete" data-nombre="{{ $prov->nombre_empresa }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center btn-submit-delete" style="width:32px;height:32px;" title="Desactivar">
                                        <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('proveedores.activar', $prov->id) }}" method="POST" class="mb-0 form-activate" data-nombre="{{ $prov->nombre_empresa }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center btn-submit-activate" style="width:32px;height:32px;" title="Activar">
                                        <i data-lucide="rotate-ccw" style="width:16px;height:16px;"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted" style="font-size:14px;">
                        <i data-lucide="inbox" style="width:40px;height:40px;margin-bottom:10px;color:#CBD5E1;"></i><br>
                        No hay proveedores registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-submit-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const form = this.closest('form');
                const nombre = form.getAttribute('data-nombre');
                Swal.fire({
                    title: `¿Desactivar al proveedor ${nombre}?`,
                    text: 'El historial de pedidos y pagos será conservado.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        document.querySelectorAll('.btn-submit-activate').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const form = this.closest('form');
                const nombre = form.getAttribute('data-nombre');
                Swal.fire({
                    title: `¿Activar al proveedor ${nombre}?`,
                    text: 'El proveedor volverá a estar activo para registrar pedidos y productos.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10B981',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: 'Sí, activar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
