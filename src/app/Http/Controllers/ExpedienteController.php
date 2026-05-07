<?php

namespace App\Http\Controllers;

use App\Models\ExpedienteClinico;
use Illuminate\Http\Request;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpedienteClinico::with('consultas')->orderBy('nombre_completo');

        if ($request->filled('buscar')) {
            $termino = $request->buscar;
            $query->where(function ($q) use ($termino) {
                $q->where('nombre_completo', 'like', "%{$termino}%")
                  ->orWhere('telefono', 'like', "%{$termino}%");
            });
        }

        $expedientes = $query->paginate(20)->withQueryString();

        return view('expedientes.index', compact('expedientes'));
    }

    public function create()
    {
        return view('expedientes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:masculino,femenino,otro',
            'tipo_sangre' => 'nullable|string|max:10',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
            'alergias' => 'nullable|string',
            'enfermedades_cronicas' => 'nullable|string',
            'medicamentos_actuales' => 'nullable|string',
            'antecedentes_familiares' => 'nullable|string',
        ]);

        $expediente = ExpedienteClinico::create([
            'nombre_completo' => $request->nombre_completo,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo' => $request->sexo,
            'tipo_sangre' => $request->tipo_sangre,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
            'alergias' => $request->alergias,
            'enfermedades_cronicas' => $request->enfermedades_cronicas,
            'medicamentos_actuales' => $request->medicamentos_actuales,
            'antecedentes_familiares' => $request->antecedentes_familiares,
            'estado' => 'activo',
        ]);

        return redirect()->route('expedientes.show', $expediente->id)
            ->with('success', 'Expediente clínico creado exitosamente.');
    }

    public function show($id)
    {
        $expediente = ExpedienteClinico::findOrFail($id);
        
        $consultas = $expediente->consultas()->with('medico')->orderByDesc('fecha_hora')->paginate(10);
        
        // Asignamos las recetas (en la vista se usarán desde consultas, o bien cargamos directamente desde las consultas)
        // Aunque el CU dice: ficha completa + historial de consultas + lista de recetas. 
        // Si no hay tabla receta intermedia desde expediente, las traemos por consulta.

        return view('expedientes.show', compact('expediente', 'consultas'));
    }

    public function edit($id)
    {
        $expediente = ExpedienteClinico::findOrFail($id);
        return view('expedientes.edit', compact('expediente'));
    }

    public function update(Request $request, $id)
    {
        $expediente = ExpedienteClinico::findOrFail($id);

        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:masculino,femenino,otro',
            'tipo_sangre' => 'nullable|string|max:10',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
            'alergias' => 'nullable|string',
            'enfermedades_cronicas' => 'nullable|string',
            'medicamentos_actuales' => 'nullable|string',
            'antecedentes_familiares' => 'nullable|string',
        ]);

        $expediente->update($request->all());

        return redirect()->route('expedientes.show', $expediente->id)
            ->with('success', 'Expediente clínico actualizado exitosamente.');
    }

    public function archivar($id)
    {
        $expediente = ExpedienteClinico::findOrFail($id);
        $expediente->update(['estado' => 'archivado']);

        return back()->with('success', 'El expediente ha sido archivado.');
    }
}
