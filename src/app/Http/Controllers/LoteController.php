<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\KardexProducto;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    // Agregar lote manualmente a un producto
    public function store(Request $request, int $productoId)
    {
        $request->validate([
            'numero_lote'       => 'required|string|max:50',
            'cantidad'          => 'required|integer|min:1',
            'fecha_vencimiento' => 'required|date|after:today',
        ], [
            'numero_lote.required'       => 'El número de lote es obligatorio.',
            'cantidad.required'          => 'La cantidad es obligatoria.',
            'cantidad.min'               => 'La cantidad debe ser al menos 1.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.after'    => 'La fecha de vencimiento debe ser posterior a hoy.',
        ]);

        $producto = Producto::findOrFail($productoId);

        Lote::create([
            'producto_id'       => $productoId,
            'numero_lote'       => $request->numero_lote,
            'cantidad'          => $request->cantidad,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'fecha_ingreso'     => now(),
        ]);

        $producto->recalcularStock();

        KardexProducto::create([
            'producto_id'   => $productoId,
            'usuario_id'    => auth()->id(),
            'tipo'          => 'entrada',
            'cantidad'      => $request->cantidad,
            'referencia_id' => null,
            'fecha_hora'    => now(),
        ]);

        return back()->with('success', 'Lote registrado y stock actualizado.');
    }
}
