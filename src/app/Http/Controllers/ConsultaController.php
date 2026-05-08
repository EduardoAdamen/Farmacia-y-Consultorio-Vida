<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Cita;
use App\Models\ExpedienteClinico;
use Illuminate\Http\Request;

// Controlador que maneja el registro y seguimiento de las consultas médicas
class ConsultaController extends Controller
{
    // Muestra el formulario para registrar una nueva consulta
    // Puede recibir opcionalmente un expediente o una cita desde la que se origina la consulta
    public function create(Request $request)
    {
        $expedienteId = $request->expediente_id;
        $citaId = $request->cita_id;

        // Carga el expediente si se proporcionó su ID directamente
        $expediente = null;
        if ($expedienteId) {
            $expediente = ExpedienteClinico::findOrFail($expedienteId);
        }

        // Carga la cita si se proporcionó, y obtiene el expediente desde ella si aún no se tiene
        $cita = null;
        if ($citaId) {
            $cita = Cita::findOrFail($citaId);
            // Si la cita tiene un expediente vinculado y no se cargó uno antes, se usa el de la cita
            if (!$expediente && $cita->expediente_id) {
                $expediente = $cita->expediente;
            }
        }

        // Si no se tiene un expediente, se deberá buscar en la vista de creación o redirigir
        return view('consultas.create', compact('expediente', 'cita'));
    }

    // Guarda una nueva consulta médica en la base de datos
    public function store(Request $request)
    {
        // Valida que los datos clínicos y administrativos sean correctos antes de guardar
        $request->validate([
            'expediente_id'       => 'required|exists:expediente_clinico,id',
            'cita_id'             => 'nullable|exists:cita,id',
            'presion_arterial'    => 'nullable|string|max:20',
            'temperatura'         => 'nullable|numeric',
            'frecuencia_cardiaca' => 'nullable|integer',
            'peso'                => 'nullable|numeric',
            'talla'               => 'nullable|numeric',
            'motivo'              => 'required|string',
            'sintomas'            => 'required|string',
            'diagnostico'         => 'required|string',
            'tratamiento'         => 'nullable|string',
            'estudios_solicitados' => 'nullable|string',
            'tipo_consulta'       => 'required|in:primera_vez,seguimiento,urgencia',
            'costo'               => 'required|numeric|min:0',
            'estado_pago'         => 'required|in:pagado,pendiente,cortesia',
            'proxima_cita'        => 'nullable|date|after:today', // La próxima cita debe ser una fecha futura
        ]);

        // Crea el registro de la consulta asignando automáticamente el médico y la fecha actual
        $consulta = Consulta::create([
            'expediente_id'        => $request->expediente_id,
            'medico_id'            => auth()->id(), // El médico que atiende es el usuario en sesión
            'cita_id'              => $request->cita_id,
            'fecha_hora'           => now(),
            'presion_arterial'     => $request->presion_arterial,
            'temperatura'          => $request->temperatura,
            'frecuencia_cardiaca'  => $request->frecuencia_cardiaca,
            'peso'                 => $request->peso,
            'talla'                => $request->talla,
            'motivo'               => $request->motivo,
            'sintomas'             => $request->sintomas,
            'diagnostico'          => $request->diagnostico,
            'tratamiento'          => $request->tratamiento,
            'estudios_solicitados' => $request->estudios_solicitados,
            'notas_evolucion'      => null, // Las notas de evolución se agregan después de la consulta
            'tipo_consulta'        => $request->tipo_consulta,
            'costo'                => $request->costo,
            'estado_pago'          => $request->estado_pago,
            'proxima_cita'         => $request->proxima_cita,
        ]);

        // Si la consulta provino de una cita agendada, se marca esa cita como completada
        if ($request->cita_id) {
            $cita = Cita::find($request->cita_id);
            if ($cita) {
                $cita->update(['estado' => 'completada']);
            }
        }

        // Redirige al detalle de la consulta recién creada con un mensaje de confirmación
        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Consulta registrada exitosamente.');
    }

    // Muestra el detalle completo de una consulta: datos clínicos, médico y recetas generadas
    public function show($id)
    {
        $consulta = Consulta::with(['expediente', 'medico', 'recetas'])->findOrFail($id);
        return view('consultas.show', compact('consulta'));
    }

    // Muestra el formulario para editar una consulta existente
    public function edit($id)
    {
        $consulta = Consulta::with('expediente')->findOrFail($id);

        // Un médico solo puede editar sus propias consultas; el administrador puede editar cualquiera
        if (auth()->user()->rol === 'medico' && $consulta->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar esta consulta.');
        }

        return view('consultas.edit', compact('consulta'));
    }

    // Guarda los cambios realizados a una consulta existente
    public function update(Request $request, $id)
    {
        $consulta = Consulta::findOrFail($id);

        // Verifica nuevamente que el médico solo pueda modificar sus propias consultas
        if (auth()->user()->rol === 'medico' && $consulta->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta consulta.');
        }

        // Valida los datos clínicos y administrativos antes de actualizar
        $request->validate([
            'presion_arterial'     => 'nullable|string|max:20',
            'temperatura'          => 'nullable|numeric',
            'frecuencia_cardiaca'  => 'nullable|integer',
            'peso'                 => 'nullable|numeric',
            'talla'                => 'nullable|numeric',
            'motivo'               => 'required|string',
            'sintomas'             => 'required|string',
            'diagnostico'          => 'required|string',
            'tratamiento'          => 'nullable|string',
            'estudios_solicitados' => 'nullable|string',
            'tipo_consulta'        => 'required|in:primera_vez,seguimiento,urgencia',
            'costo'                => 'required|numeric|min:0',
            'estado_pago'          => 'required|in:pagado,pendiente,cortesia',
            'proxima_cita'         => 'nullable|date|after:today',
        ]);

        // Actualiza todos los campos enviados desde el formulario
        $consulta->update($request->all());

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Consulta actualizada correctamente.');
    }

    // Guarda o actualiza las notas de evolución de una consulta ya existente
    // Las notas de evolución son observaciones que el médico agrega después de la consulta inicial
    public function updateNotas(Request $request, $id)
    {
        $consulta = Consulta::findOrFail($id);

        // Un médico solo puede agregar notas a sus propias consultas
        if (auth()->user()->rol === 'medico' && $consulta->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta consulta.');
        }

        $request->validate([
            'notas_evolucion' => 'required|string',
        ]);

        // Actualiza únicamente el campo de notas sin tocar el resto de la consulta
        $consulta->update(['notas_evolucion' => $request->notas_evolucion]);

        return back()->with('success', 'Notas de evolución guardadas exitosamente.');
    }
}