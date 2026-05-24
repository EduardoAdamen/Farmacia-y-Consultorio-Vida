<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\DetalleReceta;
use App\Models\Consulta;
use Illuminate\Http\Request;

// Controlador que gestiona la creación e impresión de recetas médicas generadas durante una consulta
class RecetaController extends Controller
{
    // Muestra el formulario para generar una nueva receta médica asociada a una consulta
    public function create($consultaId)
    {
        // Carga la consulta con los datos del expediente clínico del paciente
        $consulta = Consulta::with('expediente')->findOrFail($consultaId);
        
        return view('recetas.create', compact('consulta'));
    }

    // Guarda una nueva receta médica junto con su listado de medicamentos en la base de datos
    public function store(Request $request, $consultaId)
    {
        // Obtiene la consulta original; si no existe, lanza un error 404
        $consulta = Consulta::findOrFail($consultaId);
        
        // Valida que los medicamentos y sus especificaciones de dosis y duración sean correctos
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

        // Crea el registro principal de la receta con un folio único autogenerado
        $receta = Receta::create([
            'consulta_id' => $consulta->id,
            'folio' => Receta::generarFolio(),
            'fecha' => now(),
            'estado_valida' => 'activa',
            'indicaciones' => $request->indicaciones
        ]);

        // Registra cada uno de los medicamentos indicados en el detalle de la receta
        foreach ($request->medicamentos as $med) {
            DetalleReceta::create([
                'receta_id' => $receta->id,
                'producto_id' => $med['producto_id'] ?? null, // Asocia un producto del catálogo si aplica
                'nombre_medicamento' => $med['nombre_medicamento'],
                'dosis' => $med['dosis'],
                'frecuencia' => $med['frecuencia'],
                'duracion' => $med['duracion'],
                'indicaciones_especificas' => $med['indicaciones_especificas'] ?? null,
            ]);
        }

        // Redirige al expediente o detalle de la consulta mostrando confirmación con el folio
        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Receta médica generada exitosamente con folio ' . $receta->folio);
    }

    // Muestra la vista de impresión de la receta con los detalles de la consulta y el médico
    public function imprimir($id)
    {
        // Carga la receta junto con el expediente, el médico que la expidió y los medicamentos
        $receta = Receta::with(['consulta.expediente', 'consulta.medico', 'detalles'])->findOrFail($id);
        return view('recetas.imprimir', compact('receta'));
    }
}
