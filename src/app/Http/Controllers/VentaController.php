<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    private VentaService $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
    }

    public function index()
    {
        return view('ventas.index');
    }

    public function historial(Request $request)
    {
        $query = Venta::with('vendedor')->orderByDesc('fecha_hora');
        if ($request->filled('fecha_inicio')) $query->whereDate('fecha_hora', '>=', $request->fecha_inicio);
        if ($request->filled('fecha_fin'))    $query->whereDate('fecha_hora', '<=', $request->fecha_fin);
        if ($request->filled('estado'))        $query->where('estado', $request->estado);
        $ventas = $query->paginate(20)->withQueryString();
        return view('ventas.historial', compact('ventas'));
    }

    public function show(int $id)
    {
        $venta = Venta::with(['detalles.producto', 'detalles.receta', 'vendedor'])->findOrFail($id);
        return view('ventas.show', compact('venta'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|exists:producto,id',
            'items.*.cant'      => 'required|integer|min:1',
            'items.*.desc'      => 'required|numeric|min:0|max:100',
            'monto_recibido'    => 'required|numeric|min:0',
        ]);

        try {
            $venta = $this->ventaService->registrar($request->items, $request->monto_recibido);
            return response()->json([
                'success'  => true,
                'mensaje'  => 'Venta registrada exitosamente.',
                'venta_id' => $venta->id,
                'redirect' => route('ventas.show', $venta->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancelar(int $id)
    {
        // Solo el dueño puede cancelar
        if (auth()->user()->rol !== 'dueno') {
            abort(403);
        }

        try {
            $venta = Venta::with('detalles.producto')->findOrFail($id);
            $this->ventaService->cancelar($venta);
            return back()->with('success', 'Venta cancelada y stock revertido correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
