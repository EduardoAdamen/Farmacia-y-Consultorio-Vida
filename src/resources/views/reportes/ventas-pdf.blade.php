<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0F172A; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #0F172A; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #64748B; font-size: 11px; }
        .metrics { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .metrics td { width: 25%; padding: 15px; text-align: center; border: 1px solid #E2E8F0; background: #F8FAFC; }
        .metric-title { font-size: 10px; text-transform: uppercase; color: #64748B; font-weight: bold; margin-bottom: 5px; }
        .metric-value { font-size: 18px; font-weight: bold; color: #0F172A; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th { background-color: #0F172A; color: #fff; padding: 8px; text-align: left; font-size: 11px; text-transform: uppercase; }
        table.data td { padding: 8px; border-bottom: 1px solid #E2E8F0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #94A3B8; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Farmacia y Consultorio Médico Vida</h1>
        <p>REPORTE DE VENTAS</p>
        <p>Período: {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</p>
    </div>

    <table class="metrics">
        <tr>
            <td>
                <div class="metric-title">Ingresos Totales</div>
                <div class="metric-value">${{ number_format($ingresosTotales, 2) }}</div>
            </td>
            <td>
                <div class="metric-title">Transacciones</div>
                <div class="metric-value">{{ number_format($numTransacciones) }}</div>
            </td>
            <td>
                <div class="metric-title">Ticket Promedio</div>
                <div class="metric-value">${{ number_format($promedioPorVenta, 2) }}</div>
            </td>
            <td>
                <div class="metric-title">Mejor Día</div>
                <div class="metric-value">
                    @if($mejorDia)
                        {{ ucfirst($mejorDia->dia) }}
                    @else
                        N/A
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <h3 style="color: #0F172A; font-size: 14px; margin-bottom: 10px; border-bottom: 1px solid #E2E8F0; padding-bottom: 5px;">TOP 5 PRODUCTOS MÁS VENDIDOS</h3>
    
    <table class="data">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="text-center">Unidades</th>
                <th class="text-right">Ingresos Generados</th>
            </tr>
        </thead>
        <tbody>
            @forelse($top5Productos as $prod)
            <tr>
                <td><strong>{{ $prod->producto->nombre }}</strong></td>
                <td>{{ $prod->producto->categoria->nombre }}</td>
                <td class="text-center">{{ $prod->unidades_vendidas }}</td>
                <td class="text-right"><strong>${{ number_format($prod->ingresos, 2) }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No se registraron ventas en este período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->nombre_completo }}
    </div>

</body>
</html>
