<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Consulta;
use App\Models\KardexProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    // RF-58: Reporte de ventas
    public function ventas(Request $request)
    {
        $request->validate([
            'periodo'      => 'nullable|in:dia,semana,mes,rango',
            'fecha_inicio' => 'required_if:periodo,rango|nullable|date',
            'fecha_fin'    => 'required_if:periodo,rango|nullable|date|after_or_equal:fecha_inicio',
        ]);

        if (!$request->filled('periodo')) {
            $request->merge(['periodo' => 'mes']);
        }

        [$fechaInicio, $fechaFin] = $this->calcularRango($request);

        $baseQuery = Venta::where('estado', 'completada')
                          ->whereBetween('fecha_hora', [
                              $fechaInicio->copy()->startOfDay(),
                              $fechaFin->copy()->endOfDay(),
                          ]);

        $ingresosTotales     = (clone $baseQuery)->sum('total');
        $numTransacciones    = (clone $baseQuery)->count();
        $promedioPorVenta    = $numTransacciones > 0 ? $ingresosTotales / $numTransacciones : 0;

        $mejorDia = Venta::where('estado', 'completada')
                         ->whereBetween('fecha_hora', [
                             $fechaInicio->copy()->startOfDay(),
                             $fechaFin->copy()->endOfDay(),
                         ])
                         ->select(DB::raw('DAYNAME(fecha_hora) as dia, SUM(total) as total'))
                         ->groupBy('dia')
                         ->orderByDesc('total')
                         ->first();

        // Top 5 productos más vendidos
        $top5Productos = DetalleVenta::select(
                             'producto_id',
                             DB::raw('SUM(cantidad) as unidades_vendidas'),
                             DB::raw('SUM(cantidad * precio_unitario * (1 - descuento_manual/100)) as ingresos')
                         )
                         ->whereHas('venta', fn($q) =>
                             $q->where('estado', 'completada')
                               ->whereBetween('fecha_hora', [
                                   $fechaInicio->copy()->startOfDay(),
                                   $fechaFin->copy()->endOfDay(),
                               ])
                         )
                         ->with('producto.categoria')
                         ->groupBy('producto_id')
                         ->orderByDesc('unidades_vendidas')
                         ->limit(5)
                         ->get();

        $data = compact(
            'ingresosTotales', 'numTransacciones', 'promedioPorVenta',
            'mejorDia', 'top5Productos', 'fechaInicio', 'fechaFin', 'request'
        );

        if ($request->filled('descargar')) {
            $pdf = Pdf::loadView('reportes.ventas-pdf', $data)->setPaper('letter');
            return $pdf->download('reporte-ventas-' . $fechaInicio->format('Y-m-d') . '.pdf');
        }

        return view('reportes.ventas', $data);
    }

    // RF-59: Reporte de consultas médicas
    public function consultas(Request $request)
    {
        $request->validate([
            'periodo'      => 'nullable|in:dia,semana,mes,rango',
            'fecha_inicio' => 'required_if:periodo,rango|nullable|date',
            'fecha_fin'    => 'required_if:periodo,rango|nullable|date|after_or_equal:fecha_inicio',
        ]);

        if (!$request->filled('periodo')) {
            $request->merge(['periodo' => 'mes']);
        }

        [$fechaInicio, $fechaFin] = $this->calcularRango($request);

        $consultas = Consulta::whereBetween('fecha_hora', [
                                  $fechaInicio->copy()->startOfDay(),
                                  $fechaFin->copy()->endOfDay(),
                              ])
                              ->with('expediente', 'medico');

        $totalPacientes     = (clone $consultas)->count();
        $primeraVez         = (clone $consultas)->where('tipo_consulta', 'primera_vez')->count();
        $seguimiento        = (clone $consultas)->where('tipo_consulta', 'seguimiento')->count();
        $urgencias          = (clone $consultas)->where('tipo_consulta', 'urgencia')->count();
        $ingresosTotales    = (clone $consultas)->where('estado_pago', 'pagado')->sum('costo');
        $listadoConsultas   = (clone $consultas)->orderByDesc('fecha_hora')->get();

        $data = compact(
            'totalPacientes', 'primeraVez', 'seguimiento', 'urgencias',
            'ingresosTotales', 'listadoConsultas', 'fechaInicio', 'fechaFin', 'request'
        );

        if ($request->filled('descargar')) {
            $pdf = Pdf::loadView('reportes.consultas-pdf', $data)->setPaper('letter');
            return $pdf->download('reporte-consultas-' . $fechaInicio->format('Y-m-d') . '.pdf');
        }

        return view('reportes.consultas', $data);
    }

    // RF-60: Reporte de inventario
    public function inventario(Request $request)
    {
        // Valoración total del stock
        $valoracionTotal = Producto::activos()
                                   ->select(DB::raw('SUM(stock_total * precio_compra) as total'))
                                   ->value('total') ?? 0;

        // Top 10 más vendidos (por unidades en kardex tipo venta)
        $masVendidos = KardexProducto::where('tipo', 'venta')
                                     ->select('producto_id', DB::raw('SUM(ABS(cantidad)) as total_vendido'))
                                     ->groupBy('producto_id')
                                     ->with('producto.categoria')
                                     ->orderByDesc('total_vendido')
                                     ->limit(10)
                                     ->get();

        // Top 10 menos vendidos
        // Un producto "menos vendido" es aquel que tiene menos movimientos de venta
        $menosVendidos = Producto::activos()
                                 ->with('categoria')
                                 ->orderBy('stock_total', 'desc')
                                 ->whereDoesntHave('kardex', fn($q) => $q->where('tipo', 'venta'))
                                 ->limit(10)
                                 ->get();
        
        // Completamos con los que tienen menos de 10 ventas si no llegamos a 10
        if ($menosVendidos->count() < 10) {
            $faltantes = 10 - $menosVendidos->count();
            $otros = Producto::activos()
                            ->with('categoria')
                            ->whereHas('kardex', fn($q) => $q->where('tipo', 'venta'))
                            ->withSum(['kardex as total_vendido' => fn($q) => $q->where('tipo', 'venta')], 'cantidad')
                            ->orderBy('total_vendido', 'asc') // Menor cantidad vendida primero (los valores son negativos, así que orderByDesc para más cercanos a 0)
                            ->orderByDesc('stock_total')
                            ->limit($faltantes)
                            ->get();
            $menosVendidos = $menosVendidos->concat($otros);
        }

        $data = compact('valoracionTotal', 'masVendidos', 'menosVendidos', 'request');

        if ($request->filled('descargar')) {
            $pdf = Pdf::loadView('reportes.inventario-pdf', $data)->setPaper('letter');
            return $pdf->download('reporte-inventario-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('reportes.inventario', $data);
    }

    private function calcularRango(Request $request): array
    {
        $hoy = now();
        return match ($request->periodo) {
            'dia'   => [$hoy->copy()->startOfDay(), $hoy->copy()->endOfDay()],
            'semana'=> [$hoy->copy()->startOfWeek(), $hoy->copy()->endOfWeek()],
            'mes'   => [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()],
            'rango' => [\Carbon\Carbon::parse($request->fecha_inicio)->startOfDay(), \Carbon\Carbon::parse($request->fecha_fin)->endOfDay()],
            default => [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()],
        };
    }
}
