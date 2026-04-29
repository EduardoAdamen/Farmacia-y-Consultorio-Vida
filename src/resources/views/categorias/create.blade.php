@extends('layouts.app')
@section('title', 'Nueva Categoría')

@section('content')
<div class="mb-4">
    <a href="{{ route('categorias.index') }}" class="text-decoration-none" style="font-size:13px;color:var(--color-text-muted);display:inline-flex;align-items:center;gap:6px;font-weight:500;transition:all 0.2s;">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Volver a categorías
    </a>
</div>

<div class="card" style="border:1px solid var(--color-border);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);background:#fff;max-width:600px;">
    <div class="card-body p-4">
        <h5 class="mb-4" style="font-family:'Outfit',sans-serif;font-weight:700;">Registrar Categoría</h5>
        
        @if($errors->any())
            <div class="alert alert-danger" style="font-size:13px;border-radius:8px;">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('categorias.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Nombre de la Categoría <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required
                       style="font-size:14px;border-radius:8px;padding:10px 14px;border:1px solid var(--color-border);"
                       placeholder="Ej. Analgésicos">
            </div>
            
            <div class="mb-4">
                <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block;">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"
                          style="font-size:14px;border-radius:8px;padding:10px 14px;border:1px solid var(--color-border);"
                          placeholder="Breve descripción de los productos en esta categoría">{{ old('descripcion') }}</textarea>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('categorias.index') }}" class="btn btn-ghost" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-accent d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;border-radius:8px;padding:9px 16px;">
                    <i data-lucide="save" style="width:16px;height:16px;"></i> Guardar Categoría
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
