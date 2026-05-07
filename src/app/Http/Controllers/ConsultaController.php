<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Cita;
use App\Models\ExpedienteClinico;
use Illuminate\Http\Request;

class ConsultaController extends Controller
{
    public function create(Request $request)
    {
        $expedienteId = $request->expediente_id;
        $citaId = $request->cita_id;
        
        $expediente = null;
        if ($expedienteId) {
            $expediente = ExpedienteClinico::findOrFail($expedienteId);
        }

        $cita = null;
        if ($citaId) {
            $cita = Cita::findOrFail($citaId);
            if (!$expediente && $cita->expediente_id) {
                $expediente = $cita->expediente;
            }
        }

        // Si no se tiene un expediente, se deberá buscar en la vista de creación o redirigir
        return view('consultas.create', compact('expediente', 'cita'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expediente_id' => 'required|exists:expediente_clinico,id',
            'cita_id' => 'nullable|exists:cita,id',
            'presion_arterial' => 'nullable|string|max:20',
            'temperatura' => 'nullable|numeric',
            'frecuencia_cardiaca' => 'nullable|integer',
            'peso' => 'nullable|numeric',
            'talla' => 'nullable|numeric',
            'motivo' => 'required|string',
            'sintomas' => 'required|string',
            'diagnostico' => 'required|string',
            'tratamiento' => 'nullable|string',
            'estudios_solicitados' => 'nullable|string',
            'tipo_consulta' => 'required|in:primera_vez,seguimiento,urgencia',
            'costo' => 'required|numeric|min:0',
            'estado_pago' => 'required|in:pagado,pendiente,cortesia',
            'proxima_cita' => 'nullable|date|after:today',
        ]);

        $consulta = Consulta::create([
            'expediente_id' => $request->expediente_id,
            'medico_id' => auth()->id(),
            'cita_id' => $request->cita_id,
            'fecha_hora' => now(),
            'presion_arterial' => $request->presion_arterial,
            'temperatura' => $request->temperatura,
            'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
            'peso' => $request->peso,
            'talla' => $request->talla,
            'motivo' => $request->motivo,
            'sintomas' => $request->sintomas,
            'diagnostico' => $request->diagnostico,
            'tratamiento' => $request->tratamiento,
            'estudios_solicitados' => $request->estudios_solicitados,
            'notas_evolucion' => null,
            'tipo_consulta' => $request->tipo_consulta,
            'costo' => $request->costo,
            'estado_pago' => $request->estado_pago,
            'proxima_cita' => $request->proxima_cita,
        ]);

        if ($request->cita_id) {
            $cita = Cita::find($request->cita_id);
            if ($cita) {
                $cita->update(['estado' => 'completada']);
            }
        }

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Consulta registrada exitosamente.');
    }

    public function show($id)
    {
        $consulta = Consulta::with(['expediente', 'medico', 'recetas'])->findOrFail($id);
        return view('consultas.show', compact('consulta'));
    }

    public function edit($id)
    {
        $consulta = Consulta::with('expediente')->findOrFail($id);
        
        if (auth()->user()->rol === 'medico' && $consulta->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar esta consulta.');
        }

        return view('consultas.edit', compact('consulta'));
    }

    public function update(Request $request, $id)
    {
        $consulta = Consulta::findOrFail($id);
        
        if (auth()->user()->rol === 'medico' && $consulta->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta consulta.');
        }

        $request->validate([
            'presion_arterial' => 'nullable|string|max:20',
            'temperatura' => 'nullable|numeric',
            'frecuencia_cardiaca' => 'nullable|integer',
            'peso' => 'nullable|numeric',
            'talla' => 'nullable|numeric',
            'motivo' => 'required|string',
            'sintomas' => 'required|string',
            'diagnostico' => 'required|string',
            'tratamiento' => 'nullable|string',
            'estudios_solicitados' => 'nullable|string',
            'tipo_consulta' => 'required|in:primera_vez,seguimiento,urgencia',
            'costo' => 'required|numeric|min:0',
            'estado_pago' => 'required|in:pagado,pendiente,cortesia',
            'proxima_cita' => 'nullable|date|after:today',
        ]);

        $consulta->update($request->all());

        return redirect()->route('consultas.show', $consulta->id)
            ->with('success', 'Consulta actualizada correctamente.');
    }

    public function updateNotas(Request $request, $id)
    {
        $consulta = Consulta::findOrFail($id);

        if (auth()->user()->rol === 'medico' && $consulta->medico_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta consulta.');
        }

        $request->validate([
            'notas_evolucion' => 'required|string',
        ]);

        $consulta->update(['notas_evolucion' => $request->notas_evolucion]);

        return back()->with('success', 'Notas de evolución guardadas exitosamente.');
    }
}
