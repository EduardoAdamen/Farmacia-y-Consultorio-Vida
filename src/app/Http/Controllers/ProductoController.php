<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Lote;
use App\Models\KardexProducto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // CU-06: Consultar catálogo de inventario
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'proveedor'])->activos();

        if ($request->filled('buscar')) {
            $termino = $request->buscar;
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhereHas('categoria', fn($c) => $c->where('nombre', 'like', "%{$termino}%"))
                  ->orWhereHas('proveedor', fn($p) => $p->where('nombre_empresa', 'like', "%{$termino}%"));
            });
        }

        if ($request->filtro === 'critico') {
            $query->whereColumn('stock_total', '<=', 'stock_minimo');
        }

        if ($request->filtro === 'vencer') {
            $query->whereHas('lotes', fn($q) =>
                $q->where('cantidad', '>', 0)
                  ->whereDate('fecha_vencimiento', '<=', now()->addDays(30))
                  ->whereDate('fecha_vencimiento', '>=', now())
            );
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $productos  = $query->orderBy('nombre')->paginate(20)->withQueryString();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'categorias'));
    }

    // CU-06: Detalle con desglose de lotes
    public function show(int $id)
    {
        $producto = Producto::with(['categoria', 'proveedor', 'lotes' => function ($q) {
            $q->where('cantidad', '>', 0)->orderBy('fecha_vencimiento');
        }])->findOrFail($id);

        $kardex = KardexProducto::with('usuario')
                                ->where('producto_id', $id)
                                ->orderByDesc('fecha_hora')
                                ->paginate(15);

        return view('productos.show', compact('producto', 'kardex'));
    }

    // CU-05: Nuevo producto
    public function create()
    {
        $categorias  = Categoria::orderBy('nombre')->get();
        $proveedores = Proveedor::activos()->orderBy('nombre_empresa')->get();
        return view('productos.create', compact('categorias', 'proveedores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:150|unique:producto,nombre',
            'sku'             => 'required|string|max:50|unique:producto,sku',
            'codigo_barras'   => 'nullable|string|max:50',
            'categoria_id'    => 'required|exists:categoria,id',
            'proveedor_id'    => 'required|exists:proveedor,id',
            'precio_compra'   => 'required|numeric|min:0',
            'precio_venta'    => 'required|numeric|min:0',
            'stock_minimo'    => 'required|integer|min:0',
            'requiere_receta' => 'boolean',
            // Lote inicial (RF-24, RF-25)
            'numero_lote'       => 'nullable|string|max:50',
            'cantidad_inicial'  => 'nullable|integer|min:0',
            'fecha_vencimiento' => 'nullable|date|after:today',
        ], [
            'sku.unique'    => 'El SKU ingresado ya existe en otro producto.',
            'sku.required'  => 'El SKU / Código es obligatorio.',
        ]);

        $producto = Producto::create([
            'nombre'          => $request->nombre,
            'sku'             => $request->sku,
            'codigo_barras'   => $request->codigo_barras,
            'categoria_id'    => $request->categoria_id,
            'proveedor_id'    => $request->proveedor_id,
            'precio_compra'   => $request->precio_compra,
            'precio_venta'    => $request->precio_venta,
            'stock_total'     => 0,
            'stock_minimo'    => $request->stock_minimo,
            'requiere_receta' => $request->boolean('requiere_receta'),
            'estado'          => 'activo',
        ]);

        // Crear lote inicial si se proporcionó
        if ($request->filled('numero_lote') && $request->cantidad_inicial > 0) {
            Lote::create([
                'producto_id'       => $producto->id,
                'numero_lote'       => $request->numero_lote,
                'cantidad'          => $request->cantidad_inicial,
                'fecha_vencimiento' => $request->fecha_vencimiento ?? now()->addYears(2),
                'fecha_ingreso'     => now(),
            ]);

            $producto->recalcularStock();

            KardexProducto::create([
                'producto_id'   => $producto->id,
                'usuario_id'    => auth()->id(),
                'tipo'          => 'entrada',
                'cantidad'      => $request->cantidad_inicial,
                'referencia_id' => null,
                'fecha_hora'    => now(),
            ]);
        }

        return redirect()->route('productos.index')
                         ->with('success', "Producto '{$producto->nombre}' registrado exitosamente.");
    }

    public function edit(int $id)
    {
        $producto    = Producto::findOrFail($id);
        $categorias  = Categoria::orderBy('nombre')->get();
        $proveedores = Proveedor::activos()->orderBy('nombre_empresa')->get();
        return view('productos.edit', compact('producto', 'categorias', 'proveedores'));
    }

    // CU-05 FA_002: Editar producto
    public function update(Request $request, int $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre'          => "required|string|max:150|unique:producto,nombre,{$id}",
            'sku'             => "required|string|max:50|unique:producto,sku,{$id}",
            'codigo_barras'   => 'nullable|string|max:50',
            'categoria_id'    => 'required|exists:categoria,id',
            'proveedor_id'    => 'required|exists:proveedor,id',
            'precio_compra'   => 'required|numeric|min:0',
            'precio_venta'    => 'required|numeric|min:0',
            'stock_minimo'    => 'required|integer|min:0',
            'requiere_receta' => 'boolean',
        ], [
            'sku.unique'   => 'El SKU ingresado ya existe en otro producto.',
            'sku.required' => 'El SKU / Código es obligatorio.',
        ]);

        $producto->update($request->only([
            'nombre','sku','codigo_barras','categoria_id','proveedor_id',
            'precio_compra','precio_venta','stock_minimo','requiere_receta',
        ]));

        return redirect()->route('productos.show', $id)
                         ->with('success', 'Producto actualizado exitosamente.');
    }

    // CU-05 FA_003: Dar de baja
    public function destroy(int $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update(['estado' => 'inactivo']);
        return redirect()->route('productos.index')
                         ->with('success', "Producto '{$producto->nombre}' dado de baja exitosamente.");
    }

    // Endpoint AJAX para búsqueda en ventas (retorna stock por lotes FEFO)
    public function buscarAjax(Request $request)
    {
        $termino = $request->get('q', '');
        $productos = Producto::activos()
                             ->where(function ($q) use ($termino) {
                                 $q->where('nombre', 'like', "%{$termino}%")
                                   ->orWhereHas('categoria', fn($c) =>
                                       $c->where('nombre', 'like', "%{$termino}%")
                                   );
                             })
                             ->where('stock_total', '>', 0)
                             ->with('categoria')
                             ->limit(10)
                             ->get(['id','nombre','precio_venta','stock_total','requiere_receta','categoria_id']);

        return response()->json($productos);
    }
}
