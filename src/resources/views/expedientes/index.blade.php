@extends('layouts.app')
@section('title', 'Expedientes Clínicos')
@section('page-title', 'Expedientes Clínicos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0" style="font-family:'Outfit',sans-serif;font-weight:700;">Gestión de Expedientes</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">Administre los expedientes clínicos de los pacientes.</p>
    </div>
    <a href="{{ route('expedientes.create') }}" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
        <i data-lucide="plus" style="width:16px;height:16px;"></i> Nuevo Expediente
    </a>
</div>

<div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;overflow:hidden;">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <form action="{{ route('expedientes.index') }}" method="GET" class="d-flex gap-2" style="max-width:400px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i data-lucide="search" style="width:16px;height:16px;color:var(--color-text-muted);"></i></span>
                <input type="text" name="buscar" class="form-control border-start-0 ps-0" placeholder="Buscar por nombre o teléfono..." value="{{ request('buscar') }}">
            </div>
            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
            @if(request('buscar'))
                <a href="{{ route('expedientes.index') }}" class="btn btn-outline-danger" title="Limpiar"><i data-lucide="x" style="width:16px;height:16px;"></i></a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background:#F8FAFC;border-bottom:1px solid var(--color-border);font-size:12.5px;color:var(--color-text-muted);text-transform:uppercase;">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="py-3">Fecha de Nac. (Edad)</th>
                    <th class="py-3">Teléfono</th>
                    <th class="py-3">Última Consulta</th>
                    <th class="py-3">Estado</th>
                    <th class="py-3 text-end px-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expedientes as $exp)
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td class="px-4 py-3 align-middle">
                        <div style="font-weight:600;font-size:13.5px;">{{ $exp->nombre_completo }}</div>
                        <div style="font-size:12px;color:var(--color-text-muted);">ID: {{ str_pad($exp->id, 5, '0', STR_PAD_LEFT) }}</div>
                    </td>
                    <td class="py-3 align-middle" style="font-size:13.5px;color:var(--color-text-muted);">
                        {{ $exp->fecha_nacimiento->format('d/m/Y') }} <br>
                        <small>({{ $exp->edad }} años)</small>
                    </td>
                    <td class="py-3 align-middle" style="font-size:13.5px;">
                        {{ $exp->telefono ?? 'N/D' }}
                    </td>
                    <td class="py-3 align-middle">
                        @php
                            $ultimaConsulta = $exp->consultas->sortByDesc('fecha_hora')->first();
                        @endphp
                        @if($ultimaConsulta)
                            <div style="font-size:13px; font-weight: 500;">{{ $ultimaConsulta->fecha_hora->format('d/m/Y') }}</div>
                            <div style="font-size:11px;color:var(--color-text-muted);">{{ ucfirst(str_replace('_', ' ', $ultimaConsulta->tipo_consulta)) }}</div>
                        @else
                            <span class="text-muted" style="font-size:12px;">Sin consultas</span>
                        @endif
                    </td>
                    <td class="py-3 align-middle">
                        @if($exp->estado === 'activo')
                            <span class="badge badge-activo">Activo</span>
                        @else
                            <span class="badge badge-inactivo">Archivado</span>
                        @endif
                    </td>
                    <td class="py-3 text-end px-4 align-middle">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('expedientes.show', $exp->id) }}" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center" style="width:32px;height:32px;" title="Ver Detalle">
                                <i data-lucide="eye" style="width:16px;height:16px;"></i>
                            </a>
                            <a href="{{ route('expedientes.edit', $exp->id) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center" style="width:32px;height:32px;" title="Editar">
                                <i data-lucide="edit" style="width:16px;height:16px;"></i>
                            </a>
                            @if($exp->estado === 'activo')
                            <form action="{{ route('expedientes.archivar', $exp->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas archivar este expediente?');" class="mb-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center" style="width:32px;height:32px;" title="Archivar">
                                    <i data-lucide="archive" style="width:16px;height:16px;"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted" style="font-size:14px;">
                        <i data-lucide="folder-search" style="width:40px;height:40px;margin-bottom:10px;color:#CBD5E1;"></i><br>
                        No se encontraron expedientes clínicos.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expedientes->hasPages())
    <div class="card-footer bg-white border-top px-4 py-3">
        {{ $expedientes->links() }}
    </div>
    @endif
</div>
@endsection
