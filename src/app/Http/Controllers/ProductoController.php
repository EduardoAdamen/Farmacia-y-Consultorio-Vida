<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Lote;
use App\Models\KardexProducto;
use Illuminate\Http\Request;

// Controlador que maneja todas las operaciones relacionadas con los productos del catálogo
class ProductoController extends Controller
{
    // Muestra el listado de productos activos con opciones de búsqueda y filtrado
    public function index(Request $request)
    {
        // Carga los productos activos junto con su categoría y proveedor para evitar consultas extras
        $query = Producto::with(['categoria', 'proveedor'])->activos();

        // Si el usuario escribió algo en el buscador, filtra por nombre, categoría o proveedor
        if ($request->filled('buscar')) {
            $termino = $request->buscar;
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhereHas('categoria', fn($c) => $c->where('nombre', 'like', "%{$termino}%"))
                  ->orWhereHas('proveedor', fn($p) => $p->where('nombre_empresa', 'like', "%{$termino}%"));
            });
        }

        // Filtra solo productos cuyo stock actual es igual o menor al mínimo permitido
        if ($request->filtro === 'critico') {
            $query->whereColumn('stock_total', '<=', 'stock_minimo');
        }

        // Filtra productos que tienen al menos un lote con existencias próximo a vencer (30 días)
        if ($request->filtro === 'vencer') {
            $query->whereHas('lotes', fn($q) =>
                $q->where('cantidad', '>', 0)
                  ->whereDate('fecha_vencimiento', '<=', now()->addDays(30))
                  ->whereDate('fecha_vencimiento', '>=', now())
            );
        }

        // Filtra por categoría si el usuario seleccionó una en el formulario
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Ordena los resultados por nombre y los pagina de 20 en 20, conservando los filtros en la URL
        $productos  = $query->orderBy('nombre')->paginate(20)->withQueryString();
        // Carga todas las categorías para mostrarlas en el filtro desplegable de la vista
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'categorias'));
    }

    // Muestra el detalle completo de un producto, incluyendo sus lotes y movimientos de inventario
    public function show(int $id)
    {
        // Carga el producto con su categoría, proveedor y lotes que aún tienen existencias
        $producto = Producto::with(['categoria', 'proveedor', 'lotes' => function ($q) {
            $q->where('cantidad', '>', 0)->orderBy('fecha_vencimiento'); // Ordena lotes por FEFO
        }])->findOrFail($id);

        // Carga el historial de movimientos del producto ordenado del más reciente al más antiguo
        $kardex = KardexProducto::with('usuario')
                                ->where('producto_id', $id)
                                ->orderByDesc('fecha_hora')
                                ->paginate(15);

        return view('productos.show', compact('producto', 'kardex'));
    }

    // Muestra el formulario para registrar un nuevo producto
    public function create()
    {
        // Carga categorías y proveedores activos para los selectores del formulario
        $categorias  = Categoria::orderBy('nombre')->get();
        $proveedores = Proveedor::activos()->orderBy('nombre_empresa')->get();
        return view('productos.create', compact('categorias', 'proveedores'));
    }

    // Guarda un nuevo producto en la base de datos junto con su lote inicial si se proporcionó
    public function store(Request $request)
    {
        // Valida que los datos del formulario cumplan las reglas definidas antes de guardar
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
            'numero_lote'       => 'nullable|string|max:50',
            'cantidad_inicial'  => 'nullable|integer|min:0',
            'fecha_vencimiento' => 'nullable|date|after:today',
        ], [
            // Mensajes de error personalizados para mostrar al usuario en caso de fallo
            'nombre.required'         => 'El nombre del producto es obligatorio.',
            'nombre.unique'           => 'Este nombre de producto ya está registrado.',
            'sku.required'            => 'El SKU / Código es obligatorio.',
            'sku.unique'              => 'El SKU ingresado ya existe en otro producto.',
            'categoria_id.required'   => 'Debe seleccionar una categoría.',
            'categoria_id.exists'     => 'La categoría seleccionada no es válida.',
            'proveedor_id.required'   => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists'     => 'El proveedor seleccionado no es válido.',
            'precio_compra.required'  => 'El precio de compra es obligatorio.',
            'precio_compra.numeric'   => 'El precio de compra debe ser un número.',
            'precio_compra.min'       => 'El precio de compra no puede ser negativo.',
            'precio_venta.required'   => 'El precio de venta es obligatorio.',
            'precio_venta.numeric'    => 'El precio de venta debe ser un número.',
            'precio_venta.min'        => 'El precio de venta no puede ser negativo.',
            'stock_minimo.required'   => 'El stock mínimo es obligatorio.',
            'stock_minimo.min'        => 'El stock mínimo no puede ser negativo.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
        ]);

        // Crea el producto con stock inicial en cero; el stock real se calculará desde los lotes
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

        // Si se proporcionó un lote inicial con cantidad mayor a cero, se registra en el sistema
        if ($request->filled('numero_lote') && $request->cantidad_inicial > 0) {
            // Crea el lote con la información proporcionada; si no hay fecha de vencimiento, se asignan 2 años
            Lote::create([
                'producto_id'       => $producto->id,
                'numero_lote'       => $request->numero_lote,
                'cantidad'          => $request->cantidad_inicial,
                'fecha_vencimiento' => $request->fecha_vencimiento ?? now()->addYears(2),
                'fecha_ingreso'     => now(),
            ]);

            // Actualiza el stock_total del producto sumando la cantidad del lote recién creado
            $producto->recalcularStock();

            // Registra la entrada en el kardex para dejar trazabilidad del movimiento inicial
            KardexProducto::create([
                'producto_id'   => $producto->id,
                'usuario_id'    => auth()->id(), // Usuario que está realizando el registro
                'tipo'          => 'entrada',
                'cantidad'      => $request->cantidad_inicial,
                'referencia_id' => null, // Sin referencia porque es el ingreso inicial del producto
                'fecha_hora'    => now(),
            ]);
        }

        // Redirige al listado de productos mostrando un mensaje de éxito
        return redirect()->route('productos.index')
                         ->with('success', "Producto '{$producto->nombre}' registrado exitosamente.");
    }

    // Muestra el formulario para editar un producto existente
    public function edit(int $id)
    {
        $producto    = Producto::findOrFail($id);
        // Carga categorías y proveedores activos para los selectores del formulario
        $categorias  = Categoria::orderBy('nombre')->get();
        $proveedores = Proveedor::activos()->orderBy('nombre_empresa')->get();
        return view('productos.edit', compact('producto', 'categorias', 'proveedores'));
    }

    // Guarda los cambios realizados a un producto existente
    public function update(Request $request, int $id)
    {
        $producto = Producto::findOrFail($id);

        // Valida los datos del formulario; el unique ignora el registro actual para no marcarlo como duplicado
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
            // Mensajes de error personalizados para mostrar al usuario en caso de fallo
            'nombre.required'         => 'El nombre del producto es obligatorio.',
            'nombre.unique'           => 'Este nombre de producto ya está registrado.',
            'sku.required'            => 'El SKU / Código es obligatorio.',
            'sku.unique'              => 'El SKU ingresado ya existe en otro producto.',
            'categoria_id.required'   => 'Debe seleccionar una categoría.',
            'categoria_id.exists'     => 'La categoría seleccionada no es válida.',
            'proveedor_id.required'   => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists'     => 'El proveedor seleccionado no es válido.',
            'precio_compra.required'  => 'El precio de compra es obligatorio.',
            'precio_compra.numeric'   => 'El precio de compra debe ser un número.',
            'precio_compra.min'       => 'El precio de compra no puede ser negativo.',
            'precio_venta.required'   => 'El precio de venta es obligatorio.',
            'precio_venta.numeric'    => 'El precio de venta debe ser un número.',
            'precio_venta.min'        => 'El precio de venta no puede ser negativo.',
            'stock_minimo.required'   => 'El stock mínimo es obligatorio.',
            'stock_minimo.min'        => 'El stock mínimo no puede ser negativo.',
        ]);

        // Actualiza solo los campos permitidos; el stock_total no se toca aquí, se gestiona desde los lotes
        $producto->update($request->only([
            'nombre','sku','codigo_barras','categoria_id','proveedor_id',
            'precio_compra','precio_venta','stock_minimo','requiere_receta',
        ]));

        // Redirige al detalle del producto mostrando un mensaje de éxito
        return redirect()->route('productos.show', $id)
                         ->with('success', 'Producto actualizado exitosamente.');
    }

    // Da de baja un producto marcándolo como inactivo en lugar de eliminarlo permanentemente
    public function destroy(int $id)
    {
        $producto = Producto::findOrFail($id);
        // Se usa baja lógica (estado = inactivo) para conservar el historial del producto en el sistema
        $producto->update(['estado' => 'inactivo']);
        return redirect()->route('productos.index')
                         ->with('success', "Producto '{$producto->nombre}' dado de baja exitosamente.");
    }

    // Busca productos en tiempo real para autocompletar campos en otros formularios (ventas, recetas, etc.)
    // Se incluyen productos con stock 0 para que el vendedor los vea; la venta se bloquea en VentaService.
    // Con término vacío devuelve el catálogo completo para la lista rápida inicial del POS.
    public function buscarAjax(Request $request)
    {
        $termino = $request->get('q', '');
        $query   = Producto::activos()->with('categoria');

        if ($termino !== '') {
            // Con término: filtra por nombre o categoría
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhereHas('categoria', fn($c) =>
                      $c->where('nombre', 'like', "%{$termino}%")
                  );
            });
        }

        // Con stock primero, luego sin stock; dentro de cada grupo, orden alfabético
        $productos = $query
            ->orderByRaw('stock_total > 0 DESC') // productos con stock al inicio
            ->orderBy('nombre')
            ->limit($termino !== '' ? 10 : 20)   // más resultados en la lista inicial
            ->get(['id','nombre','precio_venta','stock_total','requiere_receta','categoria_id']);

        // Devuelve los resultados en formato JSON para ser consumidos por el componente de búsqueda
        return response()->json($productos);
    }
}