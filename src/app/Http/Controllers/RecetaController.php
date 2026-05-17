<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\DetalleReceta;
use App\Models\Consulta;
use Illuminate\Http\Request;

class RecetaController extends Controller
{
    public function create($consultaId)
    {
        $consulta = Consulta::with('expediente')->findOrFail($consultaId);
        
        return view('recetas.create', compact('consulta'));
    }

    public function store(Request $request, $consultaId)
    {
        $consulta = Consulta::findOrFail($consultaId);
        
        $request->validate([
            'medicamentos' => 'required|array|min:1',
            'medicamentos.*.nombre_medicamento' => 'required|string',
            'medicamentos.*.dosis' => 'required|string',
            'medicamentos.*.frecuencia' => 'required|string',
            'medicamentos.*.duracion' => 'required|string',
            'medicamentos.*.indicaciones_especificas' => 'nullable|string',
            'medicamentos.*.producto_id' => 'nullable|exists:producto,id',
            'indicaciones' => 'nullable|string'
        ]);

        $receta = Receta::create([
            'consulta_id' => $consulta->id,
            'folio' => Receta::generarFolio(),
            'fecha' => now(),
            'estado_valida' => 'activa',
            'indicaciones' => $request->indicaciones
        ]);

        foreach ($request->medicamentos as $med) {
            DetalleReceta::create([
                'receta_id' => $receta->id,
                'producto_id' => $med['producto_id'] ?? null,
                'nombre_medicamento' => $med['nombre_medicamento'],
                'dosis' => $med['dosis'],
                'frecuencia' => $med['frecuencia'],
                'duracion' => $med['duracion'],
                'indicaciones_especificas' => $med['indicaciones_especificas'] ?? null,
            ]);
        }

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Receta médica generada exitosamente con folio ' . $receta->folio);
    }

    public function imprimir($id)
    {
        $receta = Receta::with(['consulta.expediente', 'consulta.medico', 'detalles'])->findOrFail($id);
        return view('recetas.imprimir', compact('receta'));
    }
}
