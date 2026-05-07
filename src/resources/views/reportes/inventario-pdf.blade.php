<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0F172A; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #0F172A; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #64748B; font-size: 11px; }
        .metrics { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .metrics td { padding: 15px; text-align: center; border: 1px solid #E2E8F0; background: #ecfdf5; color: #065f46; }
        .metric-title { font-size: 11px; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
        .metric-value { font-size: 22px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px; }
        table.data th { background-color: #0F172A; color: #fff; padding: 8px; text-align: left; font-size: 11px; text-transform: uppercase; }
        table.data td { padding: 8px; border-bottom: 1px solid #E2E8F0; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #94A3B8; }
        .title-section { color: #0F172A; font-size: 14px; margin-bottom: 10px; border-bottom: 1px solid #E2E8F0; padding-bottom: 5px; text-transform: uppercase; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Farmacia y Consultorio Médico Vida</h1>
        <p>REPORTE DE ESTADO DE INVENTARIO</p>
        <p>Generado al: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="metrics">
        <tr>
            <td>
                <div class="metric-title">Valoración Total del Stock (Costo)</div>
                <div class="metric-value">${{ number_format($valoracionTotal, 2) }}</div>
            </td>
        </tr>
    </table>

    <h3 class="title-section">TOP 10 PRODUCTOS MÁS VENDIDOS</h3>
    
    <table class="data">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="text-center">Stock Actual</th>
                <th class="text-right">Unidades Vendidas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($masVendidos as $prod)
            <tr>
                <td><strong>{{ $prod->producto->nombre }}</strong></td>
                <td>{{ $prod->producto->categoria->nombre }}</td>
                <td class="text-center">{{ $prod->producto->stock_total }}</td>
                <td class="text-right"><strong>{{ $prod->total_vendido }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No hay datos suficientes de ventas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h3 class="title-section" style="margin-top: 40px;">PRODUCTOS DE BAJA ROTACIÓN (MENOS VENDIDOS)</h3>
    
    <table class="data">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="text-center">Stock Actual</th>
                <th class="text-right">Valuación en Inventario</th>
            </tr>
        </thead>
        <tbody>
            @forelse($menosVendidos as $prod)
            <tr>
                <td><strong>{{ $prod->nombre }}</strong></td>
                <td>{{ $prod->categoria->nombre }}</td>
                <td class="text-center">{{ $prod->stock_total }}</td>
                <td class="text-right">${{ number_format($prod->stock_total * $prod->precio_compra, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No hay productos registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->nombre_completo }}
    </div>

</body>
</html>
