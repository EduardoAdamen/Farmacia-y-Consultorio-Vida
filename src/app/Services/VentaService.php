<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\KardexProducto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Receta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VentaService
{
    /**
     * Registra la venta completa usando método FEFO para el descuento de lotes.
     * RF-22: descuenta del lote con fecha de vencimiento más próxima primero.
     */
    public function registrar(array $items, float $montoRecibido): Venta
    {
        return DB::transaction(function () use ($items, $montoRecibido) {
            $total  = 0;
            $lineas = [];
            $recetasUsadas = [];

            foreach ($items as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item['id']);

                // RF-17: Verificar stock total
                if ($producto->stock_total < $item['cant']) {
                    throw new \Exception("Stock insuficiente para: {$producto->nombre}. Disponible: {$producto->stock_total} unidades.");
                }

                // RF-16: Verificar receta
                $recetaId = null;
                if ($producto->requiere_receta) {
                    if (empty($item['receta_folio'])) {
                        throw new \Exception("El producto '{$producto->nombre}' requiere receta médica válida.");
                    }
                    
                    $folio = strtoupper(trim($item['receta_folio']));
                    
                    // Si el folio empieza con REC- (nuestro formato interno), lo validamos en DB
                    if (str_starts_with($folio, 'REC-')) {
                        if (isset($recetasUsadas[$folio])) {
                            $recetaId = $recetasUsadas[$folio]->id;
                        } else {
                            $receta = Receta::where('folio', $folio)
                                            ->where('estado_valida', 'activa')
                                            ->first();
                            if (!$receta) {
                                throw new \Exception("La receta interna '{$folio}' no es válida o ya fue utilizada.");
                            }
                            $recetaId = $receta->id;
                            $recetasUsadas[$folio] = $receta;
                        }
                    } else {
                        // Receta externa: la aceptamos sin validación estricta en base de datos.
                        // Solo se guardará en DetalleVenta.receta_id como null (ya que no tenemos su ID),
                        // pero para trazabilidad podríamos guardar el folio en un campo nuevo, 
                        // o dejar receta_id null e indicar que fue receta externa.
                        // Actualmente DetalleVenta solo tiene receta_id, así que lo dejamos nulo.
                        $recetaId = null; 
                    }
                }

                $precioUnitario = $producto->precio_venta;
                $descuento      = $item['desc'] ?? 0;
                $subtotal       = $precioUnitario * $item['cant'] * (1 - $descuento / 100);
                $total         += $subtotal;

                $lineas[] = [
                    'producto'         => $producto,
                    'receta_id'        => $recetaId,
                    'cantidad'         => $item['cant'],
                    'precio_unitario'  => $precioUnitario,
                    'descuento_manual' => $descuento,
                ];
            }

            // Marcar recetas como usadas
            foreach ($recetasUsadas as $receta) {
                $receta->update(['estado_valida' => 'usada']);
            }

            // Crear la venta
            $venta = Venta::create([
                'vendedor_id' => Auth::id(),
                'folio'       => $this->generarFolio(),
                'fecha_hora'  => now(),
                'total'       => $total,
                'estado'      => 'completada',
            ]);

            // Asignar venta_id a las recetas
            foreach ($recetasUsadas as $receta) {
                $receta->update(['venta_id' => $venta->id]);
            }

            // Procesar cada línea con FEFO
            foreach ($lineas as $linea) {
                $producto         = $linea['producto'];
                $cantidadRestante = $linea['cantidad'];

                // FEFO: ordenar lotes por fecha_vencimiento ASC
                $lotes = Lote::where('producto_id', $producto->id)
                             ->where('cantidad', '>', 0)
                             ->orderBy('fecha_vencimiento')
                             ->lockForUpdate()
                             ->get();

                foreach ($lotes as $lote) {
                    if ($cantidadRestante <= 0) break;

                    $descontar = min($lote->cantidad, $cantidadRestante);
                    $lote->decrement('cantidad', $descontar);
                    $cantidadRestante -= $descontar;
                }

                // Recalcular stock_total del producto
                $producto->recalcularStock();

                // Registrar en kardex
                KardexProducto::create([
                    'producto_id'   => $producto->id,
                    'usuario_id'    => Auth::id(),
                    'tipo'          => 'venta',
                    'cantidad'      => -$linea['cantidad'],
                    'referencia_id' => $venta->id,
                    'fecha_hora'    => now(),
                ]);

                // Crear detalle de venta
                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $producto->id,
                    'receta_id'       => $linea['receta_id'],
                    'cantidad'        => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'descuento_manual'=> $linea['descuento_manual'],
                ]);
            }

            return $venta;
        });
    }

    /**
     * Cancela una venta y restaura el stock.
     * NOTA: Al cancelar, el stock se devuelve como ajuste en kardex
     * pero NO se puede reconstruir exactamente qué lote se afectó.
     * Se agrega la cantidad al lote más reciente del producto.
     */
    public function cancelar(Venta $venta): void
    {
        DB::transaction(function () use ($venta) {
            if ($venta->estado === 'cancelada') {
                throw new \Exception('Esta venta ya fue cancelada.');
            }

            foreach ($venta->detalles as $detalle) {
                // Devolver al lote más reciente (o crear ajuste en kardex)
                $producto = $detalle->producto;
                $ultimoLote = Lote::where('producto_id', $producto->id)
                                  ->orderByDesc('fecha_ingreso')
                                  ->first();

                if ($ultimoLote) {
                    $ultimoLote->increment('cantidad', $detalle->cantidad);
                }

                $producto->recalcularStock();

                KardexProducto::create([
                    'producto_id'   => $detalle->producto_id,
                    'usuario_id'    => auth()->id(),
                    'tipo'          => 'devolucion',
                    'cantidad'      => $detalle->cantidad,
                    'referencia_id' => $venta->id,
                    'fecha_hora'    => now(),
                ]);
            }

            $venta->update(['estado' => 'cancelada']);
        });
    }

    private function generarFolio(): string
    {
        $fecha  = now()->format('Ymd');
        $ultimo = Venta::whereDate('fecha_hora', today())->count();
        return 'VTA-' . $fecha . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
