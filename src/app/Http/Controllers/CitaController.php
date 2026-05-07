<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Usuario;
use App\Models\ExpedienteClinico;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CitaController extends Controller
{


    public function index(Request $request)
    {
        // Weekly visual agenda
        $fechaReferencia = $request->filled('fecha') ? Carbon::parse($request->fecha) : now();
        $inicioSemana = $fechaReferencia->copy()->startOfWeek(); // Monday
        $finSemana = $fechaReferencia->copy()->endOfWeek(); // Sunday

        $query = Cita::with(['medico', 'expediente'])
            ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
            ->whereIn('estado', ['programada', 'reprogramada', 'completada']); // Hide cancelled? or show? 
            
        // Si el usuario es médico, idealmente solo ver sus citas, pero puede depender del rol
        if (auth()->user()->rol === 'medico') {
            $query->where('medico_id', auth()->id());
        } else if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->medico_id);
        }

        $citas = $query->get();

        $medicos = Usuario::medicos()->activos()->get();

        // Armar estructura para la vista
        $diasSemana = [];
        for ($i = 0; $i < 7; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $diasSemana[] = $dia;
        }

        $horas = [];
        for ($h = 8; $h <= 19; $h++) {
            $horas[] = sprintf("%02d:00", $h);
            $horas[] = sprintf("%02d:30", $h);
        }

        return view('citas.index', compact('citas', 'diasSemana', 'horas', 'fechaReferencia', 'medicos', 'inicioSemana', 'finSemana'));
    }

    public function create()
    {
        $medicos = Usuario::medicos()->activos()->get();
        // Si es médico, forzamos su ID
        $medicoId = auth()->user()->rol === 'medico' ? auth()->id() : null;
        return view('citas.create', compact('medicos', 'medicoId'));
    }

    public function verificarDisponibilidad(Request $request)
    {
        $request->validate([
            'medico_id' => 'required|integer',
            'fecha' => 'required|date',
            'hora' => 'required'
        ]);

        $horaStr = Carbon::parse($request->hora)->format('H:i:s');
        $fechaStr = Carbon::parse($request->fecha)->toDateString();

        $existe = Cita::where('medico_id', $request->medico_id)
            ->where('fecha', $fechaStr)
            ->where('hora', $horaStr)
            ->whereIn('estado', ['programada', 'reprogramada'])
            ->exists();

        if (!$existe) {
            return response()->json(['disponible' => true, 'proximos' => []]);
        }

        // Si no está disponible, buscar próximos horarios en el mismo día
        $proximos = [];
        $horaBase = Carbon::parse($request->hora);
        
        while (count($proximos) < 3 && $horaBase->hour < 20) {
            $horaBase->addMinutes(30);
            if ($horaBase->hour >= 20) break;
            
            $checkStr = $horaBase->format('H:i:s');
            $ocupado = Cita::where('medico_id', $request->medico_id)
                ->where('fecha', $fechaStr)
                ->where('hora', $checkStr)
                ->whereIn('estado', ['programada', 'reprogramada'])
                ->exists();
                
            if (!$ocupado) {
                $proximos[] = $horaBase->format('H:i');
            }
        }

        return response()->json(['disponible' => false, 'proximos' => $proximos]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'medico_id' => 'required|exists:usuario,id',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required',
            'motivo' => 'required|string',
            'expediente_id' => 'nullable|exists:expediente_clinico,id',
            'nombre_temporal' => 'nullable|string|max:150',
        ]);

        if (empty($request->expediente_id) && empty($request->nombre_temporal)) {
            return back()->withErrors(['paciente' => 'Debe seleccionar un expediente o ingresar un nombre temporal.'])->withInput();
        }

        $horaStr = Carbon::parse($request->hora)->format('H:i:s');
        $fechaStr = Carbon::parse($request->fecha)->toDateString();

        $existe = Cita::where('medico_id', $request->medico_id)
            ->where('fecha', $fechaStr)
            ->where('hora', $horaStr)
            ->whereIn('estado', ['programada', 'reprogramada'])
            ->exists();

        if ($existe) {
            return back()->withErrors(['hora' => 'El horario solicitado no está disponible.'])->withInput();
        }

        Cita::create([
            'medico_id' => $request->medico_id,
            'expediente_id' => $request->expediente_id,
            'fecha' => $fechaStr,
            'hora' => $horaStr,
            'motivo' => $request->motivo,
            'nombre_temporal' => $request->nombre_temporal,
            'estado' => 'programada',
        ]);

        return redirect()->route('citas.index', ['fecha' => $fechaStr])->with('success', 'Cita programada correctamente.');
    }

    public function show($id)
    {
        $cita = Cita::with(['medico', 'expediente'])->findOrFail($id);
        
        // Validar acceso si es médico
        if (auth()->user()->rol === 'medico' && $cita->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para ver esta cita.');
        }

        return view('citas.show', compact('cita'));
    }

    public function edit($id)
    {
        $cita = Cita::with(['medico', 'expediente'])->findOrFail($id);
        
        if (auth()->user()->rol === 'medico' && $cita->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar esta cita.');
        }

        $medicos = Usuario::medicos()->activos()->get();
        return view('citas.edit', compact('cita', 'medicos'));
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);
        
        if (auth()->user()->rol === 'medico' && $cita->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta cita.');
        }

        $request->validate([
            'estado' => 'required|in:programada,reprogramada,cancelada,completada',
            'motivo' => 'required_if:estado,reprogramada,cancelada|string|nullable',
            'fecha' => 'required_if:estado,reprogramada|date',
            'hora' => 'required_if:estado,reprogramada',
        ]);

        $datosActualizar = ['estado' => $request->estado];
        
        if ($request->filled('motivo')) {
            $datosActualizar['motivo'] = $request->motivo;
        }

        if ($request->estado === 'reprogramada') {
            $horaStr = Carbon::parse($request->hora)->format('H:i:s');
            $fechaStr = Carbon::parse($request->fecha)->toDateString();

            // Verificar traslape
            $existe = Cita::where('medico_id', $cita->medico_id)
                ->where('id', '!=', $cita->id)
                ->where('fecha', $fechaStr)
                ->where('hora', $horaStr)
                ->whereIn('estado', ['programada', 'reprogramada'])
                ->exists();

            if ($existe) {
                return back()->withErrors(['hora' => 'El horario solicitado no está disponible para reprogramar.'])->withInput();
            }

            $datosActualizar['fecha'] = $fechaStr;
            $datosActualizar['hora'] = $horaStr;
        }

        $cita->update($datosActualizar);

        return redirect()->route('citas.index', ['fecha' => $cita->fecha->toDateString()])
            ->with('success', 'Cita actualizada correctamente.');
    }
}
