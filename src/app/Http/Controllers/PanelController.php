<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Venta;
use App\Models\Consulta;
use App\Models\Cita;

// Controlador que genera los datos para el panel principal
// Muestra información diferente dependiendo del rol del usuario que inició sesión
class PanelController extends Controller
{
    // Prepara y envía todos los datos necesarios para mostrar el dashboard
    public function index()
    {
        // Obtiene el rol del usuario actual para decidir qué información mostrarle
        $rol = auth()->user()->rol;

        // Los primeros 5 productos en stock crítico
        // Muestra los productos más urgentes: aquellos con el stock más bajo por debajo del mínimo
        $stockCritico = Producto::stockCritico()
                                ->with('categoria')
                                ->orderBy('stock_total') // Ordena del más crítico al menos crítico
                                ->limit(5)
                                ->get();

        // Los 5 lotes más próximos a vencer (≤30 días)
        // Muestra los lotes con existencias que caducan más pronto para actuar a tiempo
        $lotesProximosAVencer = Lote::proximosAVencer(30)
                                    ->with('producto.categoria')
                                    ->orderBy('fecha_vencimiento') // Ordena del más urgente al menos urgente
                                    ->limit(5)
                                    ->get();

        // Total de productos activos en el catálogo para mostrarlo como indicador general
        $totalProductos = Producto::activos()->count();

        // Suma el total de alertas activas: productos en stock crítico + lotes próximos a vencer
        // Este número se usa para mostrar una señal de advertencia visible en el panel
        $totalAlertas = Producto::stockCritico()->count()
                      + Lote::proximosAVencer(30)->count();

        // Se inicializan en null para que la vista pueda detectar qué bloques mostrar u ocultar
        $ventasHoy        = null;
        $transaccionesHoy = null;
        $citasHoy         = null;
        $consultasHoy     = null;

        // Los indicadores de ventas solo se calculan para dueños y vendedores
        if (in_array($rol, ['dueno', 'vendedor'])) {
            // Total en dinero de las ventas completadas en el día actual
            $ventasHoy        = Venta::whereDate('fecha_hora', today())
                                     ->where('estado', 'completada')
                                     ->sum('total');
            // Número de transacciones de venta completadas en el día actual
            $transaccionesHoy = Venta::whereDate('fecha_hora', today())
                                     ->where('estado', 'completada')
                                     ->count();
        }

        // Los indicadores médicos solo se calculan para usuarios con rol de médico
        if ($rol === 'medico') {
            // Citas programadas para hoy asignadas al médico que inició sesión
            $citasHoy     = Cita::where('medico_id', auth()->id())
                                ->whereDate('fecha', today())
                                ->where('estado', 'programada')
                                ->count();
            // Consultas realizadas hoy por el médico que inició sesión
            $consultasHoy = Consulta::where('medico_id', auth()->id())
                                    ->whereDate('fecha_hora', today())
                                    ->count();
        }

        // Envía todas las variables a la vista del panel para que las muestre según corresponda
        return view('dashboard', compact(
            'stockCritico',
            'lotesProximosAVencer',
            'totalProductos',
            'totalAlertas',
            'ventasHoy',
            'transaccionesHoy',
            'citasHoy',
            'consultasHoy'
        ));
    }
}