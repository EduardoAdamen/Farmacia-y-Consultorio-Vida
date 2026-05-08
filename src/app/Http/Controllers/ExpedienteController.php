<?php

namespace App\Http\Controllers;

use App\Models\ExpedienteClinico;
use Illuminate\Http\Request;

// Controlador que maneja las operaciones sobre los expedientes clínicos de los pacientes
class ExpedienteController extends Controller
{
    // Muestra el listado de expedientes con opción de búsqueda por nombre o teléfono
    public function index(Request $request)
    {
        // Carga los expedientes con sus consultas ordenados alfabéticamente por nombre
        $query = ExpedienteClinico::with('consultas')->orderBy('nombre_completo');

        // Si el usuario escribió algo en el buscador, filtra por nombre completo o teléfono
        if ($request->filled('buscar')) {
            $termino = $request->buscar;
            $query->where(function ($q) use ($termino) {
                $q->where('nombre_completo', 'like', "%{$termino}%")
                  ->orWhere('telefono', 'like', "%{$termino}%");
            });
        }

        // Pagina los resultados de 20 en 20 conservando los filtros activos en la URL
        $expedientes = $query->paginate(20)->withQueryString();

        return view('expedientes.index', compact('expedientes'));
    }

    // Muestra el formulario para registrar un nuevo expediente clínico
    public function create()
    {
        return view('expedientes.create');
    }

    // Guarda un nuevo expediente clínico en la base de datos
    public function store(Request $request)
    {
        // Valida los datos personales y médicos del paciente antes de guardar
        $request->validate([
            'nombre_completo'         => 'required|string|max:150',
            'fecha_nacimiento'        => 'required|date|before:today', // La fecha debe ser en el pasado
            'sexo'                    => 'required|in:masculino,femenino,otro',
            'tipo_sangre'             => 'nullable|string|max:10',
            'telefono'                => 'nullable|string|max:20',
            'correo'                  => 'nullable|email|max:100',
            'alergias'                => 'nullable|string',
            'enfermedades_cronicas'   => 'nullable|string',
            'medicamentos_actuales'   => 'nullable|string',
            'antecedentes_familiares' => 'nullable|string',
        ]);

        // Crea el expediente marcándolo como activo desde el momento del registro
        $expediente = ExpedienteClinico::create([
            'nombre_completo'         => $request->nombre_completo,
            'fecha_nacimiento'        => $request->fecha_nacimiento,
            'sexo'                    => $request->sexo,
            'tipo_sangre'             => $request->tipo_sangre,
            'telefono'                => $request->telefono,
            'correo'                  => $request->correo,
            'alergias'                => $request->alergias,
            'enfermedades_cronicas'   => $request->enfermedades_cronicas,
            'medicamentos_actuales'   => $request->medicamentos_actuales,
            'antecedentes_familiares' => $request->antecedentes_familiares,
            'estado'                  => 'activo',
        ]);

        // Redirige directamente al detalle del expediente recién creado
        return redirect()->route('expedientes.show', $expediente->id)
            ->with('success', 'Expediente clínico creado exitosamente.');
    }

    // Muestra el detalle completo de un expediente: ficha del paciente e historial de consultas
    public function show($id)
    {
        $expediente = ExpedienteClinico::findOrFail($id);

        // Carga el historial de consultas del paciente con su médico, de la más reciente a la más antigua
        $consultas = $expediente->consultas()->with('medico')->orderByDesc('fecha_hora')->paginate(10);

        // Asignamos las recetas (en la vista se usarán desde consultas, o bien cargamos directamente desde las consultas)
        // Aunque el CU dice: ficha completa + historial de consultas + lista de recetas.
        // Si no hay tabla receta intermedia desde expediente, las traemos por consulta.

        return view('expedientes.show', compact('expediente', 'consultas'));
    }

    // Muestra el formulario para editar los datos de un expediente existente
    public function edit($id)
    {
        $expediente = ExpedienteClinico::findOrFail($id);
        return view('expedientes.edit', compact('expediente'));
    }

    // Guarda los cambios realizados al expediente clínico de un paciente
    public function update(Request $request, $id)
    {
        $expediente = ExpedienteClinico::findOrFail($id);

        // Valida los datos antes de actualizar; las reglas son las mismas que al crear
        $request->validate([
            'nombre_completo'         => 'required|string|max:150',
            'fecha_nacimiento'        => 'required|date|before:today',
            'sexo'                    => 'required|in:masculino,femenino,otro',
            'tipo_sangre'             => 'nullable|string|max:10',
            'telefono'                => 'nullable|string|max:20',
            'correo'                  => 'nullable|email|max:100',
            'alergias'                => 'nullable|string',
            'enfermedades_cronicas'   => 'nullable|string',
            'medicamentos_actuales'   => 'nullable|string',
            'antecedentes_familiares' => 'nullable|string',
        ]);

        // Actualiza todos los campos enviados desde el formulario
        $expediente->update($request->all());

        return redirect()->route('expedientes.show', $expediente->id)
            ->with('success', 'Expediente clínico actualizado exitosamente.');
    }

    // Archiva un expediente clínico cuando el paciente ya no es atendido activamente
    // A diferencia de eliminar, archivar conserva todo el historial médico del paciente
    public function archivar($id)
    {
        $expediente = ExpedienteClinico::findOrFail($id);
        // Cambia el estado a archivado en lugar de eliminar para preservar el historial clínico
        $expediente->update(['estado' => 'archivado']);

        return back()->with('success', 'El expediente ha sido archivado.');
    }
}