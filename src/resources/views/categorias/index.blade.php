@extends('layouts.app')
@section('title', 'Gestión de Categorías')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1" style="font-family:'Outfit',sans-serif;font-weight:700;">Categorías del Inventario</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">Administra las familias de productos.</p>
    </div>
    @if(auth()->user()->rol === 'dueno')
    <a href="{{ route('categorias.create') }}" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        Nueva Categoría
    </a>
    @endif
</div>

<div class="card mb-4" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="margin-bottom:0;">
            <thead style="background:#F8FAFC;border-bottom:1px solid var(--color-border);font-size:12.5px;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:0.5px;">
                <tr>
                    <th class="px-4 py-3" style="font-weight:600;border:none;">Nombre</th>
                    <th class="py-3" style="font-weight:600;border:none;">Descripción</th>
                    @if(auth()->user()->rol === 'dueno')
                    <th class="py-3 text-end px-4" style="font-weight:600;border:none;">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($categorias as $cat)
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td class="px-4 py-3" style="border:none;">
                        <div class="d-flex align-items-center gap-3">
                            <div style="font-weight:600;font-size:13.5px;">{{ $cat->nombre }}</div>
                        </div>
                    </td>
                    <td class="py-3" style="font-size:13.5px;color:var(--color-text-muted);border:none;">
                        {{ $cat->descripcion ?? '---' }}
                    </td>
                    @if(auth()->user()->rol === 'dueno')
                    <td class="py-3 px-4 text-end" style="border:none;">
                        <a href="{{ route('categorias.edit', $cat->id) }}"
                           class="btn btn-ghost btn-sm d-inline-flex align-items-center gap-1"
                           style="font-size:12px;border-radius:6px;padding:5px 10px;">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i> Editar
                        </a>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-5" style="color:var(--color-text-muted);font-size:13px;border:none;">
                        <i data-lucide="tags" style="width:32px;height:32px;display:block;margin:0 auto 8px;opacity:0.5;"></i>
                        No hay categorías registradas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-end mt-3">
    {{ $categorias->links() }}
</div>
@endsection
