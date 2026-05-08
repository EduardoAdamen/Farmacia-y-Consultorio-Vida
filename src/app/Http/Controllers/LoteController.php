<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\KardexProducto;
use Illuminate\Http\Request;

// Controlador que maneja el registro manual de lotes de productos en el inventario
class LoteController extends Controller
{
    // Agregar lote manualmente a un producto
    // Registra un nuevo lote, actualiza el stock del producto y deja constancia en el kardex
    public function store(Request $request, int $productoId)
    {
        // Valida que los datos del lote sean correctos antes de guardarlo
        $request->validate([
            'numero_lote'       => 'required|string|max:50',
            'cantidad'          => 'required|integer|min:1',
            'fecha_vencimiento' => 'required|date|after:today', 
        ], [
            // Mensajes de error personalizados para mostrar al usuario en caso de fallo
            'numero_lote.required'       => 'El número de lote es obligatorio.',
            'cantidad.required'          => 'La cantidad es obligatoria.',
            'cantidad.min'               => 'La cantidad debe ser al menos 1.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.after'    => 'La fecha de vencimiento debe ser posterior a hoy.',
        ]);

        // Busca el producto al que pertenecerá el lote; si no existe lanza un error 404
        $producto = Producto::findOrFail($productoId);

        // Crea el nuevo lote asociado al producto con la fecha de ingreso del momento actual
        Lote::create([
            'producto_id'       => $productoId,
            'numero_lote'       => $request->numero_lote,
            'cantidad'          => $request->cantidad,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'fecha_ingreso'     => now(),
        ]);

        // Recalcula el stock total del producto sumando las cantidades de todos sus lotes
        // Es importante hacer esto después de crear el lote para que el total quede actualizado
        $producto->recalcularStock();

        // Registra la entrada en el kardex para mantener trazabilidad del movimiento de inventario
        KardexProducto::create([
            'producto_id'   => $productoId,
            'usuario_id'    => auth()->id(), // Usuario que está registrando el lote
            'tipo'          => 'entrada',
            'cantidad'      => $request->cantidad,
            'referencia_id' => null, // Sin referencia porque es un ingreso manual, no viene de un pedido
            'fecha_hora'    => now(),
        ]);

        // Regresa a la página anterior mostrando un mensaje de confirmación
        return back()->with('success', 'Lote registrado y stock actualizado.');
    }
}