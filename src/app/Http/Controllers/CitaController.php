<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Usuario;
use App\Models\ExpedienteClinico;
use Illuminate\Http\Request;
use Carbon\Carbon;

// Controlador que maneja el agendado y seguimiento de citas médicas
class CitaController extends Controller
{
    // Muestra la agenda semanal de citas en formato visual de calendario
    public function index(Request $request)
    {
        // Usa la fecha enviada como referencia o la fecha actual si no se proporcionó ninguna
        // Weekly visual agenda
        $fechaReferencia = $request->filled('fecha') ? Carbon::parse($request->fecha) : now();
        $inicioSemana = $fechaReferencia->copy()->startOfWeek(); // Monday
        $finSemana    = $fechaReferencia->copy()->endOfWeek();   // Sunday

        // Carga las citas de la semana seleccionada con su médico y expediente del paciente
        $query = Cita::with(['medico', 'expediente'])
            ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
            ->whereIn('estado', ['programada', 'reprogramada', 'completada']); // Hide cancelled? or show?

        // Un médico solo ve sus propias citas; otros roles pueden filtrar por médico si lo desean
        if (auth()->user()->rol === 'medico') {
            $query->where('medico_id', auth()->id());
        } else if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->medico_id);
        }

        $citas = $query->get();

        // Carga la lista de médicos activos para el filtro desplegable de la vista
        $medicos = Usuario::medicos()->activos()->get();

        // Armar estructura para la vista
        // Genera un arreglo con los 7 días de la semana para construir las columnas del calendario
        $diasSemana = [];
        for ($i = 0; $i < 7; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $diasSemana[] = $dia;
        }

        // Genera los bloques de horario de 8:00 a 19:30 en intervalos de 30 minutos
        // para construir las filas del calendario
        $horas = [];
        for ($h = 8; $h <= 19; $h++) {
            $horas[] = sprintf("%02d:00", $h);
            $horas[] = sprintf("%02d:30", $h);
        }

        return view('citas.index', compact('citas', 'diasSemana', 'horas', 'fechaReferencia', 'medicos', 'inicioSemana', 'finSemana'));
    }

    // Muestra el formulario para agendar una nueva cita
    public function create()
    {
        $medicos = Usuario::medicos()->activos()->get();
        // Si el usuario es médico, se fuerza su propio ID para que no pueda agendar como otro médico
        $medicoId = auth()->user()->rol === 'medico' ? auth()->id() : null;
        return view('citas.create', compact('medicos', 'medicoId'));
    }

    // Verifica en tiempo real si un médico tiene disponibilidad en una fecha y hora específicas
    // Responde en formato JSON para ser usado por el formulario de agendado sin recargar la página
    public function verificarDisponibilidad(Request $request)
    {
        $request->validate([
            'medico_id' => 'required|integer',
            'fecha'     => 'required|date',
            'hora'      => 'required'
        ]);

        // Normaliza la fecha y hora al formato que usa la base de datos
        $horaStr  = Carbon::parse($request->hora)->format('H:i:s');
        $fechaStr = Carbon::parse($request->fecha)->toDateString();

        // Busca si ya existe una cita activa del médico en ese mismo horario
        $existe = Cita::where('medico_id', $request->medico_id)
            ->where('fecha', $fechaStr)
            ->where('hora', $horaStr)
            ->whereIn('estado', ['programada', 'reprogramada'])
            ->exists();

        // Si el horario está libre, responde directamente con disponibilidad confirmada
        if (!$existe) {
            return response()->json(['disponible' => true, 'proximos' => []]);
        }

        // Si el horario está ocupado, busca hasta 3 horarios libres disponibles en el mismo día
        // para sugerirlos como alternativas al usuario
        $proximos = [];
        $horaBase = Carbon::parse($request->hora);

        // Avanza de 30 en 30 minutos hasta encontrar 3 horarios libres o llegar a las 20:00
        while (count($proximos) < 3 && $horaBase->hour < 20) {
            $horaBase->addMinutes(30);
            if ($horaBase->hour >= 20) break;

            $checkStr = $horaBase->format('H:i:s');
            $ocupado = Cita::where('medico_id', $request->medico_id)
                ->where('fecha', $fechaStr)
                ->where('hora', $checkStr)
                ->whereIn('estado', ['programada', 'reprogramada'])
                ->exists();

            // Solo agrega el horario a las sugerencias si está disponible
            if (!$ocupado) {
                $proximos[] = $horaBase->format('H:i');
            }
        }

        return response()->json(['disponible' => false, 'proximos' => $proximos]);
    }

    // Guarda una nueva cita en la base de datos después de validar disponibilidad
    public function store(Request $request)
    {
        $request->validate([
            'medico_id'      => ['required', \Illuminate\Validation\Rule::exists('usuario', 'id')->where('rol', 'medico')],
            'fecha'          => 'required|date|after_or_equal:today', // No se pueden agendar citas en el pasado
            'hora'           => 'required',
            'motivo'         => 'required|string',
            'expediente_id'  => 'nullable|exists:expediente_clinico,id',
            'nombre_temporal' => 'nullable|string|max:150',
        ]);

        // El paciente debe identificarse con un expediente existente o al menos con un nombre temporal
        if (empty($request->expediente_id) && empty($request->nombre_temporal)) {
            return back()->withErrors(['paciente' => 'Debe seleccionar un expediente o ingresar un nombre temporal.'])->withInput();
        }

        // Normaliza la fecha y hora al formato que usa la base de datos
        $horaStr  = Carbon::parse($request->hora)->format('H:i:s');
        $fechaStr = Carbon::parse($request->fecha)->toDateString();

        // Verifica una vez más que el horario siga disponible antes de guardar
        // (puede haber cambiado desde que se verificó en el paso anterior)
        $existe = Cita::where('medico_id', $request->medico_id)
            ->where('fecha', $fechaStr)
            ->where('hora', $horaStr)
            ->whereIn('estado', ['programada', 'reprogramada'])
            ->exists();

        if ($existe) {
            return back()->withErrors(['hora' => 'El horario solicitado no está disponible.'])->withInput();
        }

        Cita::create([
            'medico_id'      => $request->medico_id,
            'expediente_id'  => $request->expediente_id,
            'fecha'          => $fechaStr,
            'hora'           => $horaStr,
            'motivo'         => $request->motivo,
            'nombre_temporal' => $request->nombre_temporal,
            'estado'         => 'programada',
        ]);

        // Redirige a la agenda de la semana correspondiente a la cita recién agendada
        return redirect()->route('citas.index', ['fecha' => $fechaStr])->with('success', 'Cita programada correctamente.');
    }

    // Muestra el detalle completo de una cita: médico, paciente y estado actual
    public function show($id)
    {
        $cita = Cita::with(['medico', 'expediente'])->findOrFail($id);

        // Validar acceso si es médico
        // Un médico solo puede ver los detalles de sus propias citas
        if (auth()->user()->rol === 'medico' && $cita->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para ver esta cita.');
        }

        return view('citas.show', compact('cita'));
    }

    // Muestra el formulario para modificar o reprogramar una cita existente
    public function edit($id)
    {
        $cita = Cita::with(['medico', 'expediente'])->findOrFail($id);

        // Un médico solo puede editar sus propias citas
        if (auth()->user()->rol === 'medico' && $cita->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar esta cita.');
        }

        $medicos = Usuario::medicos()->activos()->get();
        return view('citas.edit', compact('cita', 'medicos'));
    }

    // Guarda los cambios realizados a una cita: puede cambiar su estado, motivo o fecha y hora
    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);

        // Un médico solo puede modificar sus propias citas
        if (auth()->user()->rol === 'medico' && $cita->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta cita.');
        }

        // El motivo es obligatorio si se cancela o reprograma; la nueva fecha y hora solo si se reprograma
        $request->validate([
            'estado' => 'required|in:programada,reprogramada,cancelada,completada',
            'motivo' => 'required_if:estado,reprogramada,cancelada|string|nullable',
            'fecha'  => 'required_if:estado,reprogramada|date',
            'hora'   => 'required_if:estado,reprogramada',
        ]);

        // Solo actualiza el estado como base; los demás campos se agregan según corresponda
        $datosActualizar = ['estado' => $request->estado];

        // Actualiza el motivo solo si fue enviado en el formulario
        if ($request->filled('motivo')) {
            $datosActualizar['motivo'] = $request->motivo;
        }

        // Si se está reprogramando, valida que el nuevo horario esté disponible antes de asignarlo
        if ($request->estado === 'reprogramada') {
            $horaStr  = Carbon::parse($request->hora)->format('H:i:s');
            $fechaStr = Carbon::parse($request->fecha)->toDateString();

            // Verificar traslape
            // Excluye la cita actual de la verificación para no marcarla como ocupada por sí misma
            $existe = Cita::where('medico_id', $cita->medico_id)
                ->where('id', '!=', $cita->id)
                ->where('fecha', $fechaStr)
                ->where('hora', $horaStr)
                ->whereIn('estado', ['programada', 'reprogramada'])
                ->exists();

            if ($existe) {
                return back()->withErrors(['hora' => 'El horario solicitado no está disponible para reprogramar.'])->withInput();
            }

            // Si el horario está libre, agrega la nueva fecha y hora a los datos a actualizar
            $datosActualizar['fecha'] = $fechaStr;
            $datosActualizar['hora']  = $horaStr;
        }

        $cita->update($datosActualizar);

        // Redirige a la semana de la agenda donde quedó la cita actualizada
        return redirect()->route('citas.index', ['fecha' => $cita->fecha->toDateString()])
            ->with('success', 'Cita actualizada correctamente.');
    }
}