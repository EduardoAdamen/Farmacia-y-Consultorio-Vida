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

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['proveedor', 'usuario'])->orderByDesc('id');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }

        $pedidos = $query->paginate(15);
        $proveedores = Proveedor::activos()->get();

        return view('pedidos.index', compact('pedidos', 'proveedores'));
    }

    public function create()
    {
        $proveedores = Proveedor::activos()->get();
        // Productos que están activos
        $productos = Producto::activos()->get();
        return view('pedidos.create', compact('proveedores', 'productos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id'   => 'required|exists:proveedor,id',
            'fecha_estimada' => 'nullable|date',
            'productos'      => 'required|array|min:1',
            'productos.*.id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0'
        ]);

        DB::transaction(function() use ($request) {
            $monto_total = 0;

            $pedido = Pedido::create([
                'proveedor_id'   => $request->proveedor_id,
                'usuario_id'     => auth()->id(),
                'folio'          => Pedido::generarFolio(),
                'fecha_estimada' => $request->fecha_estimada,
                'estado'         => 'pendiente',
                'monto_total'    => 0,
            ]);

            foreach ($request->productos as $prodReq) {
                $subtotal = $prodReq['cantidad'] * $prodReq['precio'];
                $monto_total += $subtotal;

                DetallePedido::create([
                    'pedido_id'           => $pedido->id,
                    'producto_id'         => $prodReq['id'],
                    'cantidad_solicitada' => $prodReq['cantidad']
                ]);
            }

            $pedido->update(['monto_total' => $monto_total]);
        });

        return redirect()->route('pedidos.index')->with('success', 'Pedido registrado exitosamente.');
    }

    public function show($id)
    {
        $pedido = Pedido::with(['proveedor', 'detalles.producto'])->findOrFail($id);
        return view('pedidos.show', compact('pedido'));
    }

    public function edit($id)
    {
        $pedido = Pedido::with('detalles.producto')->findOrFail($id);

        if ($pedido->estado !== 'pendiente') {
            return redirect()->route('pedidos.show', $id)
                             ->with('error', 'Solo se pueden editar pedidos en estado pendiente.');
        }

        $proveedores = Proveedor::activos()->get();
        $productos   = Producto::activos()->get();

        $detallesExistentes = $pedido->detalles->map(function ($d) {
            return [
                'producto_id' => $d->producto_id,
                'cantidad'    => $d->cantidad_solicitada,
                'precio'      => $d->producto->precio_compra ?? 0,
            ];
        })->values()->toArray();

        return view('pedidos.edit', compact('pedido', 'proveedores', 'productos', 'detallesExistentes'));
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::with('detalles')->findOrFail($id);

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
            $pedido->detalles()->delete();

            $monto_total = 0;

            foreach ($request->productos as $prodReq) {
                $monto_total += $prodReq['cantidad'] * $prodReq['precio'];

                DetallePedido::create([
                    'pedido_id'           => $pedido->id,
                    'producto_id'         => $prodReq['id'],
                    'cantidad_solicitada' => $prodReq['cantidad'],
                ]);
            }

            $pedido->update([
                'proveedor_id'   => $request->proveedor_id,
                'fecha_estimada' => $request->fecha_estimada,
                'monto_total'    => $monto_total,
            ]);
        });

        return redirect()->route('pedidos.show', $pedido->id)
                         ->with('success', 'Pedido actualizado correctamente.');
    }

    public function recibirPedido(Request $request, $id)
    {
        $pedido = Pedido::with('detalles.producto')->findOrFail($id);

        if ($pedido->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden recibir pedidos pendientes.');
        }

        $request->validate([
            'detalles' => 'required|array',
            'detalles.*.cantidad_recibida'  => 'required|integer|min:0',
            'detalles.*.precio_compra_real' => 'required|numeric|min:0',
            'detalles.*.numero_lote'        => 'required|string|max:50',
            'detalles.*.fecha_vencimiento'  => 'required|date|after:today',
        ]);

        DB::transaction(function() use ($request, $pedido) {
            foreach ($request->detalles as $detalleId => $data) {
                $detalle = DetallePedido::findOrFail($detalleId);
                
                if ($data['cantidad_recibida'] > 0) {
                    $detalle->update([
                        'cantidad_recibida'  => $data['cantidad_recibida'],
                        'precio_compra_real' => $data['precio_compra_real']
                    ]);

                    Lote::create([
                        'producto_id'       => $detalle->producto_id,
                        'numero_lote'       => $data['numero_lote'],
                        'cantidad'          => $data['cantidad_recibida'],
                        'fecha_vencimiento' => $data['fecha_vencimiento'],
                        'fecha_ingreso'     => now()
                    ]);

                    $producto = $detalle->producto;
                    $producto->recalcularStock();

                    if ($producto->precio_compra != $data['precio_compra_real']) {
                        $producto->update(['precio_compra' => $data['precio_compra_real']]);
                    }

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

            $pedido->update(['estado' => 'recibido']);
        });

        return back()->with('success', 'Pedido recibido y stock actualizado exitosamente.');
    }

    public function cancelarPedido($id)
    {
        $pedido = Pedido::findOrFail($id);

        if ($pedido->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden cancelar pedidos pendientes.');
        }

        $pedido->update(['estado' => 'cancelado']);

        return back()->with('success', 'Pedido cancelado correctamente.');
    }

    public function marcarPagado(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        if ($pedido->estado !== 'recibido') {
            return back()->with('error', 'Solo se pueden pagar pedidos recibidos.');
        }

        $request->validate([
            'fecha_pago' => 'required|date'
        ]);

        $pedido->update([
            'estado'     => 'pagado',
            'fecha_pago' => $request->fecha_pago
        ]);

        return back()->with('success', 'Pago de pedido registrado correctamente.');
    }
}
