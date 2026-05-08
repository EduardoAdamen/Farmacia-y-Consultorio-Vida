<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\Request;

// Controlador que maneja las operaciones relacionadas con las ventas del sistema
// Delega la lógica compleja al servicio VentaService para mantener el controlador limpio
class VentaController extends Controller
{
    // Servicio que contiene la lógica de negocio para registrar y cancelar ventas
    private VentaService $ventaService;

    // Recibe el servicio automáticamente al crear el controlador (inyección de dependencias)
    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
    }

    // Muestra la pantalla principal del punto de venta donde se registran las ventas
    public function index()
    {
        return view('ventas.index');
    }

    // Muestra el historial de ventas con filtros opcionales por fecha y estado
    public function historial(Request $request)
    {
        // Carga las ventas con su vendedor, ordenadas de la más reciente a la más antigua
        $query = Venta::with('vendedor')->orderByDesc('fecha_hora');
        // Aplica los filtros solo si el usuario los proporcionó en el formulario
        if ($request->filled('fecha_inicio')) $query->whereDate('fecha_hora', '>=', $request->fecha_inicio);
        if ($request->filled('fecha_fin'))    $query->whereDate('fecha_hora', '<=', $request->fecha_fin);
        if ($request->filled('estado'))        $query->where('estado', $request->estado);
        // Pagina los resultados de 20 en 20 conservando los filtros activos en la URL
        $ventas = $query->paginate(20)->withQueryString();
        return view('ventas.historial', compact('ventas'));
    }

    // Muestra el detalle completo de una venta: productos, cantidades, precios y vendedor
    public function show(int $id)
    {
        // Carga la venta con todos sus datos relacionados para mostrarlos en la vista
        $venta = Venta::with(['detalles.producto', 'detalles.receta', 'vendedor'])->findOrFail($id);
        return view('ventas.show', compact('venta'));
    }

    // Registra una nueva venta enviada desde el punto de venta (responde en formato JSON)
    public function store(Request $request)
    {
        // Valida que la venta tenga al menos un producto con cantidad y descuento válidos
        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|exists:producto,id',   // Cada producto debe existir en el catálogo
            'items.*.cant'      => 'required|integer|min:1',         // La cantidad debe ser al menos 1
            'items.*.desc'      => 'required|numeric|min:0|max:100', // El descuento va de 0% a 100%
            'monto_recibido'    => 'required|numeric|min:0',         // Dinero entregado por el cliente
        ]);

        try {
            // El servicio se encarga de descontar stock, registrar el kardex y crear la venta
            $venta = $this->ventaService->registrar($request->items, $request->monto_recibido);
            // Si todo salió bien, devuelve los datos necesarios para redirigir al ticket de la venta
            return response()->json([
                'success'  => true,
                'mensaje'  => 'Venta registrada exitosamente.',
                'venta_id' => $venta->id,
                'redirect' => route('ventas.show', $venta->id),
            ]);
        } catch (\Exception $e) {
            // Si algo falló (stock insuficiente, producto inactivo, etc.) devuelve el error al cliente
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // Cancela una venta y revierte el stock de los productos involucrados
    public function cancelar(int $id)
    {
        // Solo el dueño puede cancelar
        // Bloquea el acceso si el usuario que intenta cancelar no tiene el rol de dueño
        if (auth()->user()->rol !== 'dueno') {
            abort(403); // Devuelve error de acceso denegado
        }

        try {
            // Carga la venta con sus detalles y productos para poder revertir el stock correctamente
            $venta = Venta::with('detalles.producto')->findOrFail($id);
            // El servicio se encarga de devolver el stock y marcar la venta como cancelada
            $this->ventaService->cancelar($venta);
            return back()->with('success', 'Venta cancelada y stock revertido correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}