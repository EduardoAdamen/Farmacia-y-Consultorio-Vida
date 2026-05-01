<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\DiaVisitaProveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::activos()->with('diasVisita')->get();
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_empresa'     => 'required|string|max:150',
            'nombre_contacto'    => 'required|string|max:150',
            'telefono'           => 'required|string|max:20',
            'rfc'                => 'nullable|string|max:13',
            'correo_electronico' => 'nullable|email|max:100',
            'dias_visita'        => 'nullable|array',
            'dias_visita.*'      => 'string|in:lun,mar,mie,jue,vie,sab,dom',
        ], [
            'nombre_empresa.required'     => 'El nombre de la empresa es obligatorio.',
            'nombre_contacto.required'    => 'El nombre del contacto es obligatorio.',
            'telefono.required'           => 'El teléfono es obligatorio.',
            'correo_electronico.email'    => 'El correo electrónico no tiene un formato válido.',
            'dias_visita.*.in'            => 'Uno de los días de visita seleccionados no es válido.',
        ]);

        DB::transaction(function() use ($request) {
            $proveedor = Proveedor::create([
                'nombre_empresa'     => $request->nombre_empresa,
                'nombre_contacto'    => $request->nombre_contacto,
                'telefono'           => $request->telefono,
                'rfc'                => $request->rfc,
                'correo_electronico' => $request->correo_electronico,
                'estado'             => 'activo'
            ]);

            if ($request->filled('dias_visita')) {
                foreach ($request->dias_visita as $dia) {
                    DiaVisitaProveedor::create([
                        'proveedor_id' => $proveedor->id,
                        'dia_semana'   => $dia
                    ]);
                }
            }
        });

        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado exitosamente.');
    }
    
    public function show($id)
    {
        $proveedor = Proveedor::activos()->findOrFail($id);
        $diasVisita = $proveedor->diasVisita->pluck('dia_semana')->toArray() ?: [];
        return view('proveedores.show', compact('proveedor', 'diasVisita'));
    }

    public function edit($id)
    {
        $proveedor = Proveedor::activos()->findOrFail($id);
        $diasVisita = current($proveedor->diasVisita->pluck('dia_semana')->toArray()) ? $proveedor->diasVisita->pluck('dia_semana')->toArray() : [];
        return view('proveedores.edit', compact('proveedor', 'diasVisita'));
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::activos()->findOrFail($id);

        $request->validate([
            'nombre_empresa'     => 'required|string|max:150',
            'nombre_contacto'    => 'required|string|max:150',
            'telefono'           => 'required|string|max:20',
            'rfc'                => 'nullable|string|max:13',
            'correo_electronico' => 'nullable|email|max:100',
            'dias_visita'        => 'nullable|array',
            'dias_visita.*'      => 'string|in:lun,mar,mie,jue,vie,sab,dom',
        ], [
            'nombre_empresa.required'     => 'El nombre de la empresa es obligatorio.',
            'nombre_contacto.required'    => 'El nombre del contacto es obligatorio.',
            'telefono.required'           => 'El teléfono es obligatorio.',
            'correo_electronico.email'    => 'El correo electrónico no tiene un formato válido.',
            'dias_visita.*.in'            => 'Uno de los días de visita seleccionados no es válido.',
        ]);

        DB::transaction(function() use ($request, $proveedor) {
            $proveedor->update([
                'nombre_empresa'     => $request->nombre_empresa,
                'nombre_contacto'    => $request->nombre_contacto,
                'telefono'           => $request->telefono,
                'rfc'                => $request->rfc,
                'correo_electronico' => $request->correo_electronico,
            ]);

            DiaVisitaProveedor::where('proveedor_id', $proveedor->id)->delete();

            if ($request->filled('dias_visita')) {
                foreach ($request->dias_visita as $dia) {
                    DiaVisitaProveedor::create([
                        'proveedor_id' => $proveedor->id,
                        'dia_semana'   => $dia
                    ]);
                }
            }
        });

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::activos()->findOrFail($id);

        if ($proveedor->pedidos()->where('estado', 'pendiente')->exists()) {
            return redirect()->route('proveedores.index')->with('error', 'No se puede eliminar el proveedor porque tiene pedidos pendientes.');
        }

        $proveedor->update(['estado' => 'inactivo']);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado exitosamente.');
    }
}
