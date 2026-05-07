@extends('layouts.app')
@section('title', 'Módulo de Reportes')
@section('page-title', 'Reportes y Estadísticas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h5 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;font-weight:700;">Panel de Reportes</h5>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">Seleccione el tipo de reporte que desea consultar y exportar.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Reporte de Ventas -->
    <div class="col-md-4">
        <div class="card h-100 border-0" style="border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);transition: transform 0.2s;">
            <div class="card-body p-4 text-center">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 12px; background: #e0f2fe; color: var(--color-primary);">
                    <i data-lucide="bar-chart-2" style="width: 32px; height: 32px;"></i>
                </div>
                <h5 class="fw-bold mb-2">Reporte de Ventas</h5>
                <p class="text-muted" style="font-size: 13px;">Analice los ingresos generados, transacciones realizadas y los productos más vendidos en su farmacia.</p>
            </div>
            <div class="card-footer bg-white border-top-0 p-4 pt-0 text-center">
                <a href="{{ route('reportes.ventas') }}" class="btn btn-accent w-100">Consultar Reporte</a>
            </div>
        </div>
    </div>

    <!-- Reporte de Consultas -->
    <div class="col-md-4">
        <div class="card h-100 border-0" style="border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);transition: transform 0.2s;">
            <div class="card-body p-4 text-center">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 12px; background: #fef3c7; color: var(--color-warning);">
                    <i data-lucide="stethoscope" style="width: 32px; height: 32px;"></i>
                </div>
                <h5 class="fw-bold mb-2">Consultas Médicas</h5>
                <p class="text-muted" style="font-size: 13px;">Estadísticas de atenciones médicas, clasificación por tipos de consulta y resumen de ingresos por honorarios.</p>
            </div>
            <div class="card-footer bg-white border-top-0 p-4 pt-0 text-center">
                <a href="{{ route('reportes.consultas') }}" class="btn btn-warning text-white w-100">Consultar Reporte</a>
            </div>
        </div>
    </div>

    <!-- Reporte de Inventario -->
    <div class="col-md-4">
        <div class="card h-100 border-0" style="border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);transition: transform 0.2s;">
            <div class="card-body p-4 text-center">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 12px; background: #ecfdf5; color: var(--color-success);">
                    <i data-lucide="package-search" style="width: 32px; height: 32px;"></i>
                </div>
                <h5 class="fw-bold mb-2">Estado del Inventario</h5>
                <p class="text-muted" style="font-size: 13px;">Conozca la valoración actual de su stock y descubra cuáles son los productos de mayor y menor rotación.</p>
            </div>
            <div class="card-footer bg-white border-top-0 p-4 pt-0 text-center">
                <a href="{{ route('reportes.inventario') }}" class="btn btn-success text-white w-100" style="background-color: var(--color-success); border-color: var(--color-success);">Consultar Reporte</a>
            </div>
        </div>
    </div>
</div>
@endsection
