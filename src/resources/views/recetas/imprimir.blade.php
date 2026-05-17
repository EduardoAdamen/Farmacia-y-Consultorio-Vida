<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receta Médica - {{ $receta->folio }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono&family=DM+Sans:wght@400;500;700&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; color: #0F172A; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
        .folio { font-family: 'DM Mono', monospace; font-size: 1.1rem; color: #0D9488; }
        .header-box { border-bottom: 2px solid #0D9488; padding-bottom: 15px; margin-bottom: 20px; }
        .footer-box { margin-top: 50px; text-align: center; }
        .firma-line { border-top: 1px solid #0F172A; width: 200px; margin: 0 auto; margin-top: 50px; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="p-4">
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary" style="background-color: #0D9488; border-color: #0D9488;">Imprimir Receta</button>
        <a href="{{ route('consultas.show', $receta->consulta_id) }}" class="btn btn-secondary">Volver</a>
    </div>

    <div class="header-box text-center">
        <h2 class="font-outfit mb-0">Farmacia y Consultorio Médico "Vida"</h2>
        <p class="mb-1 text-muted">Receta Médica</p>
        <div class="folio mt-2">{{ $receta->folio }}</div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <h5 class="font-outfit">Datos del Paciente</h5>
            <p class="mb-1"><strong>Nombre:</strong> {{ $receta->consulta->expediente->nombre_completo }}</p>
            <p class="mb-1"><strong>Edad:</strong> {{ $receta->consulta->expediente->edad }} años</p>
            <p class="mb-1"><strong>Fecha:</strong> {{ $receta->fecha->format('d/m/Y') }}</p>
        </div>
        <div class="col-6 text-end">
            <h5 class="font-outfit">Datos del Médico</h5>
            <p class="mb-1"><strong>Dr(a).</strong> {{ $receta->consulta->medico->nombre_completo }}</p>
        </div>
    </div>

    <h5 class="font-outfit mb-3">Prescripción</h5>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Medicamento</th>
                <th>Dosis</th>
                <th>Frecuencia</th>
                <th>Duración</th>
                <th>Indicaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->nombre_medicamento }}</td>
                <td>{{ $detalle->dosis }}</td>
                <td>{{ $detalle->frecuencia }}</td>
                <td>{{ $detalle->duracion }}</td>
                <td>{{ $detalle->indicaciones_especificas }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($receta->indicaciones)
    <div class="mt-4">
        <h6 class="font-outfit">Indicaciones Generales:</h6>
        <p>{{ $receta->indicaciones }}</p>
    </div>
    @endif

    <div class="footer-box">
        <div class="firma-line pt-2">
            Firma del Médico
        </div>
    </div>
</body>
</html>
