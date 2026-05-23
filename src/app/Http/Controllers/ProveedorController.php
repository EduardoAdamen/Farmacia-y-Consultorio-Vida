<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\DiaVisitaProveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Controlador que maneja las operaciones para administrar los proveedores del sistema
class ProveedorController extends Controller
{
    // Muestra el listado de todos los proveedores activos junto con sus días de visita
    public function index()
    {
        $proveedores = Proveedor::activos()->with('diasVisita')->get();
        return view('proveedores.index', compact('proveedores'));
    }

    // Muestra el formulario para registrar un nuevo proveedor
    public function create()
    {
        return view('proveedores.create');
    }

    // Guarda un nuevo proveedor en la base de datos junto con sus días de visita
    public function store(Request $request)
    {
        // Valida los datos del formulario antes de guardar
        $request->validate([
            'nombre_empresa'     => 'required|string|max:150|unique:proveedor,nombre_empresa',
            'nombre_contacto'    => 'required|string|max:150',
            'telefono'           => 'required|string|max:20',
            'rfc'                => 'nullable|string|max:13',
            'correo_electronico' => 'nullable|email|max:100',
            'dias_visita'        => 'nullable|array',
            'dias_visita.*'      => 'string|in:lun,mar,mie,jue,vie,sab,dom', // Solo días válidos abreviados
        ], [
            // Mensajes de error personalizados para mostrar al usuario en caso de fallo
            'nombre_empresa.required'     => 'El nombre de la empresa es obligatorio.',
            'nombre_empresa.unique'       => 'Ya existe un proveedor registrado con este nombre de empresa.',
            'nombre_contacto.required'    => 'El nombre del contacto es obligatorio.',
            'telefono.required'           => 'El teléfono es obligatorio.',
            'correo_electronico.email'    => 'El correo electrónico no tiene un formato válido.',
            'dias_visita.*.in'            => 'Uno de los días de visita seleccionados no es válido.',
        ]);

        // Usa una transacción para que el proveedor y sus días de visita se guarden juntos
        // Si algo falla en el proceso, ningún dato queda guardado a medias
        DB::transaction(function() use ($request) {
            $proveedor = Proveedor::create([
                'nombre_empresa'     => $request->nombre_empresa,
                'nombre_contacto'    => $request->nombre_contacto,
                'telefono'           => $request->telefono,
                'rfc'                => $request->rfc,
                'correo_electronico' => $request->correo_electronico,
                'estado'             => 'activo'
            ]);

            // Si el usuario seleccionó días de visita, se crea un registro por cada día
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

    // Muestra el detalle de un proveedor activo con sus días de visita
    public function show($id)
    {
        $proveedor = Proveedor::activos()->findOrFail($id);
        // Obtiene los días de visita como un arreglo simple de abreviaciones, o vacío si no tiene
        $diasVisita = $proveedor->diasVisita->pluck('dia_semana')->toArray() ?: [];
        return view('proveedores.show', compact('proveedor', 'diasVisita'));
    }

    // Muestra el formulario para editar un proveedor activo existente
    public function edit($id)
    {
        $proveedor = Proveedor::activos()->findOrFail($id);
        // Extrae los días de visita actuales para preseleccionarlos en el formulario de edición
        $diasVisita = current($proveedor->diasVisita->pluck('dia_semana')->toArray()) ? $proveedor->diasVisita->pluck('dia_semana')->toArray() : [];
        return view('proveedores.edit', compact('proveedor', 'diasVisita'));
    }

    // Guarda los cambios realizados a un proveedor y actualiza sus días de visita
    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::activos()->findOrFail($id);

        // Valida los datos del formulario antes de actualizar
        $request->validate([
            'nombre_empresa'     => 'required|string|max:150|unique:proveedor,nombre_empresa,' . $id,
            'nombre_contacto'    => 'required|string|max:150',
            'telefono'           => 'required|string|max:20',
            'rfc'                => 'nullable|string|max:13',
            'correo_electronico' => 'nullable|email|max:100',
            'dias_visita'        => 'nullable|array',
            'dias_visita.*'      => 'string|in:lun,mar,mie,jue,vie,sab,dom', // Solo días válidos abreviados
        ], [
            // Mensajes de error personalizados para mostrar al usuario en caso de fallo
            'nombre_empresa.required'     => 'El nombre de la empresa es obligatorio.',
            'nombre_empresa.unique'       => 'Ya existe un proveedor registrado con este nombre de empresa.',
            'nombre_contacto.required'    => 'El nombre del contacto es obligatorio.',
            'telefono.required'           => 'El teléfono es obligatorio.',
            'correo_electronico.email'    => 'El correo electrónico no tiene un formato válido.',
            'dias_visita.*.in'            => 'Uno de los días de visita seleccionados no es válido.',
        ]);

        // Usa una transacción para que los datos del proveedor y sus días se actualicen juntos
        DB::transaction(function() use ($request, $proveedor) {
            $proveedor->update([
                'nombre_empresa'     => $request->nombre_empresa,
                'nombre_contacto'    => $request->nombre_contacto,
                'telefono'           => $request->telefono,
                'rfc'                => $request->rfc,
                'correo_electronico' => $request->correo_electronico,
            ]);

            // Elimina todos los días de visita anteriores para reemplazarlos con los nuevos
            // Es más simple borrar y volver a crear que comparar cuáles cambiaron
            DiaVisitaProveedor::where('proveedor_id', $proveedor->id)->delete();

            // Crea un nuevo registro por cada día de visita seleccionado en el formulario
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

    // Da de baja un proveedor marcándolo como inactivo en lugar de eliminarlo permanentemente
    public function destroy($id)
    {
        $proveedor = Proveedor::activos()->findOrFail($id);

        // Impide dar de baja al proveedor si tiene pedidos que aún no han sido atendidos
        // Esto evita dejar pedidos pendientes sin proveedor asignado
        if ($proveedor->pedidos()->where('estado', 'pendiente')->exists()) {
            return redirect()->route('proveedores.index')->with('error', 'No se puede eliminar el proveedor porque tiene pedidos pendientes.');
        }

        // Se usa baja lógica (estado = inactivo) para conservar el historial del proveedor en el sistema
        $proveedor->update(['estado' => 'inactivo']);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado exitosamente.');
    }
}