<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Venta;
use App\Models\Consulta;
use App\Models\Cita;

class DashboardController extends Controller
{
    public function index()
    {
        $rol = auth()->user()->rol;

        // ── Alertas de inventario (RF-13, RF-14) ──────────────────
        // Los primeros 5 productos en stock crítico
        $stockCritico = Producto::stockCritico()
                                ->with('categoria')
                                ->orderBy('stock_total')
                                ->limit(5)
                                ->get();

        // Los 5 lotes más próximos a vencer (≤30 días)
        $lotesProximosAVencer = Lote::proximosAVencer(30)
                                    ->with('producto.categoria')
                                    ->orderBy('fecha_vencimiento')
                                    ->limit(5)
                                    ->get();

        $totalProductos = Producto::activos()->count();

        $totalAlertas = Producto::stockCritico()->count()
                      + Lote::proximosAVencer(30)->count();

        // ── Indicadores por rol (RF-12) ────────────────────────────
        $ventasHoy        = null;
        $transaccionesHoy = null;
        $citasHoy         = null;
        $consultasHoy     = null;

        if (in_array($rol, ['dueno', 'vendedor'])) {
            $ventasHoy        = Venta::whereDate('fecha_hora', today())
                                     ->where('estado', 'completada')
                                     ->sum('total');
            $transaccionesHoy = Venta::whereDate('fecha_hora', today())
                                     ->where('estado', 'completada')
                                     ->count();
        }

        if ($rol === 'medico') {
            $citasHoy     = Cita::where('medico_id', auth()->id())
                                ->whereDate('fecha', today())
                                ->where('estado', 'programada')
                                ->count();
            $consultasHoy = Consulta::where('medico_id', auth()->id())
                                    ->whereDate('fecha_hora', today())
                                    ->count();
        }

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
