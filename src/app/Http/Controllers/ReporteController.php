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

// Controlador que genera los reportes de ventas, consultas médicas e inventario del sistema
class ReporteController extends Controller
{
    // Muestra la pantalla principal con las opciones de reportes disponibles
    public function index()
    {
        return view('reportes.index');
    }

    // RF-58: Reporte de ventas
    // Genera el reporte de ventas totales, ingresos, mejor día y productos más vendidos en un rango de fechas
    public function ventas(Request $request)
    {
        // Valida que el periodo o el rango de fechas personalizado sean válidos
        $request->validate([
            'periodo'      => 'nullable|in:dia,semana,mes,rango',
            'fecha_inicio' => 'required_if:periodo,rango|nullable|date',
            'fecha_fin'    => 'required_if:periodo,rango|nullable|date|after_or_equal:fecha_inicio',
        ]);

        // Si no se especifica un periodo, se asume el mes actual por defecto
        if (!$request->filled('periodo')) {
            $request->merge(['periodo' => 'mes']);
        }

        // Calcula las fechas de inicio y fin correspondientes al periodo seleccionado
        [$fechaInicio, $fechaFin] = $this->calcularRango($request);

        // Consulta base para obtener las ventas completadas dentro del rango de fechas
        $baseQuery = Venta::where('estado', 'completada')
                          ->whereBetween('fecha_hora', [
                              $fechaInicio->copy()->startOfDay(),
                              $fechaFin->copy()->endOfDay(),
                          ]);

        // Calcula estadísticas clave: ingresos totales, cantidad de ventas y promedio por venta
        $ingresosTotales     = (clone $baseQuery)->sum('total');
        $numTransacciones    = (clone $baseQuery)->count();
        $promedioPorVenta    = $numTransacciones > 0 ? $ingresosTotales / $numTransacciones : 0;

        // Obtiene el día de la semana con mayor volumen de ingresos
        $mejorDia = Venta::where('estado', 'completada')
                         ->whereBetween('fecha_hora', [
                             $fechaInicio->copy()->startOfDay(),
                             $fechaFin->copy()->endOfDay(),
                         ])
                         ->select(DB::raw('DAYNAME(fecha_hora) as dia, SUM(total) as total'))
                         ->groupBy('dia')
                         ->orderByDesc('total')
                         ->first();

        // Top 5 de productos más vendidos ordenados por cantidad de unidades entregadas
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

        // Agrupa las variables para enviarlas a la vista o al PDF
        $data = compact(
            'ingresosTotales', 'numTransacciones', 'promedioPorVenta',
            'mejorDia', 'top5Productos', 'fechaInicio', 'fechaFin', 'request'
        );

        // Si se solicita la descarga, genera el archivo PDF correspondiente
        if ($request->filled('descargar')) {
            $pdf = Pdf::loadView('reportes.ventas-pdf', $data)->setPaper('letter');
            return $pdf->download('reporte-ventas-' . $fechaInicio->format('Y-m-d') . '.pdf');
        }

        return view('reportes.ventas', $data);
    }

    // RF-59: Reporte de consultas médicas
    // Genera estadísticas de pacientes, tipo de consulta, ingresos y un listado detallado
    public function consultas(Request $request)
    {
        // Valida las reglas de periodo e intervalos de fechas requeridos
        $request->validate([
            'periodo'      => 'nullable|in:dia,semana,mes,rango',
            'fecha_inicio' => 'required_if:periodo,rango|nullable|date',
            'fecha_fin'    => 'required_if:periodo,rango|nullable|date|after_or_equal:fecha_inicio',
        ]);

        // Si no se define el periodo, por defecto se evalúa el mes actual
        if (!$request->filled('periodo')) {
            $request->merge(['periodo' => 'mes']);
        }

        // Obtiene las fechas correspondientes para filtrar las consultas
        [$fechaInicio, $fechaFin] = $this->calcularRango($request);

        // Obtiene todas las consultas registradas en el rango de fechas con su expediente y médico
        $consultas = Consulta::whereBetween('fecha_hora', [
                                  $fechaInicio->copy()->startOfDay(),
                                  $fechaFin->copy()->endOfDay(),
                              ])
                              ->with('expediente', 'medico');

        // Calcula totales y clasificaciones por tipo de consulta para los indicadores
        $totalPacientes     = (clone $consultas)->count();
        $primeraVez         = (clone $consultas)->where('tipo_consulta', 'primera_vez')->count();
        $seguimiento        = (clone $consultas)->where('tipo_consulta', 'seguimiento')->count();
        $urgencias          = (clone $consultas)->where('tipo_consulta', 'urgencia')->count();
        $ingresosTotales    = (clone $consultas)->where('estado_pago', 'pagado')->sum('costo');
        $listadoConsultas   = (clone $consultas)->orderByDesc('fecha_hora')->get();

        // Agrupa los datos y prepara la salida web o descarga PDF
        $data = compact(
            'totalPacientes', 'primeraVez', 'seguimiento', 'urgencias',
            'ingresosTotales', 'listadoConsultas', 'fechaInicio', 'fechaFin', 'request'
        );

        // Descarga el PDF con el formato configurado para carta
        if ($request->filled('descargar')) {
            $pdf = Pdf::loadView('reportes.consultas-pdf', $data)->setPaper('letter');
            return $pdf->download('reporte-consultas-' . $fechaInicio->format('Y-m-d') . '.pdf');
        }

        return view('reportes.consultas', $data);
    }

    // RF-60: Reporte de inventario
    // Muestra la valoración del inventario, los productos más vendidos y los de menor rotación
    public function inventario(Request $request)
    {
        // Valoración total del stock multiplicando existencias por precio de compra actual
        $valoracionTotal = Producto::activos()
                                   ->select(DB::raw('SUM(stock_total * precio_compra) as total'))
                                   ->value('total') ?? 0;

        // Top 10 de productos más vendidos basándose en los movimientos de salida del Kardex
        $masVendidos = KardexProducto::where('tipo', 'venta')
                                     ->select('producto_id', DB::raw('SUM(ABS(cantidad)) as total_vendido'))
                                     ->groupBy('producto_id')
                                     ->with('producto.categoria')
                                     ->orderByDesc('total_vendido')
                                     ->limit(10)
                                     ->get();

        // Top 10 de productos menos vendidos buscando artículos activos sin registros de venta en Kardex
        $menosVendidos = Producto::activos()
                                 ->with('categoria')
                                 ->orderBy('stock_total', 'desc')
                                 ->whereDoesntHave('kardex', fn($q) => $q->where('tipo', 'venta'))
                                 ->limit(10)
                                 ->get();
        
        // Si no se completa la lista de 10 con productos sin ventas, se agregan los de menores ventas registradas
        if ($menosVendidos->count() < 10) {
            $faltantes = 10 - $menosVendidos->count();
            $otros = Producto::activos()
                            ->with('categoria')
                            ->whereHas('kardex', fn($q) => $q->where('tipo', 'venta'))
                            ->withSum(['kardex as total_vendido' => fn($q) => $q->where('tipo', 'venta')], 'cantidad')
                            ->orderBy('total_vendido', 'asc') // Ordena por menor volumen de salida
                            ->orderByDesc('stock_total')
                            ->limit($faltantes)
                            ->get();
            $menosVendidos = $menosVendidos->concat($otros);
        }

        // Agrupa las variables de inventario
        $data = compact('valoracionTotal', 'masVendidos', 'menosVendidos', 'request');

        // Genera la descarga PDF del inventario actual
        if ($request->filled('descargar')) {
            $pdf = Pdf::loadView('reportes.inventario-pdf', $data)->setPaper('letter');
            return $pdf->download('reporte-inventario-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('reportes.inventario', $data);
    }

    // Calcula y devuelve las fechas exactas de inicio y fin para los diferentes filtros de periodos
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
