<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\KardexProducto;
use Illuminate\Support\Facades\DB;

// Controlador que maneja todo el ciclo de vida de un pedido de compra a proveedores:
// creación, edición, recepción de mercancía y registro de pago
class PedidoController extends Controller
{
    // Muestra el listado de pedidos con filtros opcionales por estado y proveedor
    public function index(Request $request)
    {
        // Carga los pedidos con su proveedor y el usuario que los generó, del más reciente al más antiguo
        $query = Pedido::with(['proveedor', 'usuario'])->orderByDesc('id');

        // Aplica los filtros solo si el usuario los proporcionó en el formulario
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }

        // Pagina los resultados de 15 en 15 y carga los proveedores para el filtro desplegable
        $pedidos = $query->paginate(15);
        $proveedores = Proveedor::activos()->get();

        return view('pedidos.index', compact('pedidos', 'proveedores'));
    }

    // Muestra el formulario para crear un nuevo pedido de compra
    public function create()
    {
        $proveedores = Proveedor::activos()->get();
        // Productos que están activos
        // Solo muestra productos activos para no pedir artículos dados de baja
        $productos = Producto::activos()->get();
        return view('pedidos.create', compact('proveedores', 'productos'));
    }

    // Guarda un nuevo pedido con sus productos solicitados en la base de datos
    public function store(Request $request)
    {
        // Valida que el pedido tenga al menos un producto con cantidad y precio válidos
        $request->validate([
            'proveedor_id'   => 'required|exists:proveedor,id',
            'fecha_estimada' => 'nullable|date',
            'productos'      => 'required|array|min:1',
            'productos.*.id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0'
        ]);

        // Usa una transacción para que el pedido y sus detalles se guarden juntos o no se guarde nada
        DB::transaction(function() use ($request) {
            $monto_total = 0;

            // Crea el pedido con monto inicial en cero; se actualizará al terminar de procesar los productos
            $pedido = Pedido::create([
                'proveedor_id'   => $request->proveedor_id,
                'usuario_id'     => auth()->id(), // Usuario que está generando el pedido
                'folio'          => Pedido::generarFolio(), // Genera el folio único del día
                'fecha_estimada' => $request->fecha_estimada,
                'estado'         => 'pendiente',
                'monto_total'    => 0,
            ]);

            // Recorre cada producto del pedido, acumula el monto total y crea su línea de detalle
            foreach ($request->productos as $prodReq) {
                $subtotal = $prodReq['cantidad'] * $prodReq['precio'];
                $monto_total += $subtotal;

                DetallePedido::create([
                    'pedido_id'           => $pedido->id,
                    'producto_id'         => $prodReq['id'],
                    'cantidad_solicitada' => $prodReq['cantidad']
                    // El precio_compra_real se registrará cuando llegue la mercancía
                ]);
            }

            // Actualiza el monto total del pedido una vez que se procesaron todos los productos
            $pedido->update(['monto_total' => $monto_total]);
        });

        return redirect()->route('pedidos.index')->with('success', 'Pedido registrado exitosamente.');
    }

    // Muestra el detalle completo de un pedido: proveedor, productos y cantidades solicitadas
    public function show($id)
    {
        $pedido = Pedido::with(['proveedor', 'detalles.producto'])->findOrFail($id);
        return view('pedidos.show', compact('pedido'));
    }

    // Muestra el formulario para editar un pedido, solo si aún está pendiente
    public function edit($id)
    {
        $pedido = Pedido::with('detalles.producto')->findOrFail($id);

        // Un pedido ya recibido, pagado o cancelado no se puede modificar
        if ($pedido->estado !== 'pendiente') {
            return redirect()->route('pedidos.show', $id)
                             ->with('error', 'Solo se pueden editar pedidos en estado pendiente.');
        }

        $proveedores = Proveedor::activos()->get();
        $productos   = Producto::activos()->get();

        // Prepara los detalles actuales del pedido para precargarlos en el formulario de edición
        $detallesExistentes = $pedido->detalles->map(function ($d) {
            return [
                'producto_id' => $d->producto_id,
                'cantidad'    => $d->cantidad_solicitada,
                'precio'      => $d->producto->precio_compra ?? 0, // Usa 0 si el precio no está definido
            ];
        })->values()->toArray();

        return view('pedidos.edit', compact('pedido', 'proveedores', 'productos', 'detallesExistentes'));
    }

    // Guarda los cambios realizados a un pedido pendiente
    public function update(Request $request, $id)
    {
        $pedido = Pedido::with('detalles')->findOrFail($id);

        // Verifica nuevamente que el pedido siga pendiente antes de actualizar
        if ($pedido->estado !== 'pendiente') {
            return redirect()->route('pedidos.show', $id)
                             ->with('error', 'Solo se pueden editar pedidos en estado pendiente.');
        }

        $request->validate([
            'proveedor_id'         => 'required|exists:proveedor,id',
            'fecha_estimada'       => 'nullable|date',
            'productos'            => 'required|array|min:1',
            'productos.*.id'       => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio'   => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $pedido) {
            // Eliminar detalles anteriores y recrear
            // Es más simple borrar y volver a crear que comparar línea por línea cuáles cambiaron
            $pedido->detalles()->delete();

            $monto_total = 0;

            // Recorre los productos nuevos, acumula el monto y crea las líneas de detalle
            foreach ($request->productos as $prodReq) {
                $monto_total += $prodReq['cantidad'] * $prodReq['precio'];

                DetallePedido::create([
                    'pedido_id'           => $pedido->id,
                    'producto_id'         => $prodReq['id'],
                    'cantidad_solicitada' => $prodReq['cantidad'],
                ]);
            }

            // Actualiza el pedido con el proveedor, fecha y monto total recalculado
            $pedido->update([
                'proveedor_id'   => $request->proveedor_id,
                'fecha_estimada' => $request->fecha_estimada,
                'monto_total'    => $monto_total,
            ]);
        });

        return redirect()->route('pedidos.show', $pedido->id)
                         ->with('success', 'Pedido actualizado correctamente.');
    }

    // Registra la recepción física de la mercancía de un pedido pendiente
    // Por cada producto recibido: crea el lote, actualiza el stock, actualiza el precio de compra y registra en el kardex
    public function recibirPedido(Request $request, $id)
    {
        $pedido = Pedido::with('detalles.producto')->findOrFail($id);

        // Solo se puede recibir un pedido que esté en estado pendiente
        if ($pedido->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden recibir pedidos pendientes.');
        }

        // Valida que cada línea tenga la información necesaria para crear el lote correctamente
        $request->validate([
            'detalles' => 'required|array',
            'detalles.*.cantidad_recibida'  => 'required|integer|min:0',
            'detalles.*.precio_compra_real' => 'required|numeric|min:0',
            'detalles.*.numero_lote'        => 'required|string|max:50',
            'detalles.*.fecha_vencimiento'  => 'required|date|after:today',
        ]);

        // Usa una transacción para que todos los lotes, stocks y kardex se actualicen juntos
        DB::transaction(function() use ($request, $pedido) {
            foreach ($request->detalles as $detalleId => $data) {
                $detalle = DetallePedido::findOrFail($detalleId);

                // Solo procesa los productos que realmente llegaron (cantidad mayor a cero)
                if ($data['cantidad_recibida'] > 0) {
                    // Actualiza la línea del pedido con la cantidad real recibida y el precio confirmado
                    $detalle->update([
                        'cantidad_recibida'  => $data['cantidad_recibida'],
                        'precio_compra_real' => $data['precio_compra_real']
                    ]);

                    // Crea el lote con las unidades recibidas y su fecha de vencimiento
                    Lote::create([
                        'producto_id'       => $detalle->producto_id,
                        'numero_lote'       => $data['numero_lote'],
                        'cantidad'          => $data['cantidad_recibida'],
                        'fecha_vencimiento' => $data['fecha_vencimiento'],
                        'fecha_ingreso'     => now()
                    ]);

                    $producto = $detalle->producto;
                    // Recalcula el stock total del producto sumando todos sus lotes actuales
                    $producto->recalcularStock();

                    // Si el precio de compra del proveedor cambió, se actualiza en el catálogo del producto
                    if ($producto->precio_compra != $data['precio_compra_real']) {
                        $producto->update(['precio_compra' => $data['precio_compra_real']]);
                    }

                    // Registra la entrada en el kardex vinculada al pedido como referencia
                    KardexProducto::create([
                        'producto_id'   => $producto->id,
                        'usuario_id'    => auth()->id(),
                        'tipo'          => 'entrada',
                        'cantidad'      => $data['cantidad_recibida'],
                        'referencia_id' => $pedido->id, 
                        'fecha_hora'    => now()
                    ]);
                }
            }

            // Marca el pedido como recibido una vez que se procesaron todos los productos
            $pedido->update(['estado' => 'recibido']);
        });

        return back()->with('success', 'Pedido recibido y stock actualizado exitosamente.');
    }

    // Cancela un pedido que aún no ha sido recibido ni pagado
    public function cancelarPedido($id)
    {
        $pedido = Pedido::findOrFail($id);

        // Solo se puede cancelar un pedido que siga en estado pendiente
        if ($pedido->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden cancelar pedidos pendientes.');
        }

        // Cambia el estado a cancelado; no se toca el stock porque el pedido no había sido recibido
        $pedido->update(['estado' => 'cancelado']);

        return back()->with('success', 'Pedido cancelado correctamente.');
    }

    // Registra el pago de un pedido que ya fue recibido
    public function marcarPagado(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        // Solo se puede pagar un pedido que ya fue recibido físicamente
        if ($pedido->estado !== 'recibido') {
            return back()->with('error', 'Solo se pueden pagar pedidos recibidos.');
        }

        $request->validate([
            'fecha_pago' => 'required|date' // La fecha de pago es obligatoria para el registro contable
        ]);

        // Actualiza el estado y guarda la fecha exacta en que se realizó el pago
        $pedido->update([
            'estado'     => 'pagado',
            'fecha_pago' => $request->fecha_pago
        ]);

        return back()->with('success', 'Pago de pedido registrado correctamente.');
    }
}