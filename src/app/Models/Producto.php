<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa un producto dentro del sistema
class Producto extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'producto';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'proveedor_id', 'categoria_id', 'nombre',
        'sku', 'codigo_barras',
        'precio_compra', 'precio_venta',
        'stock_total',
        'stock_minimo', 'requiere_receta', 'estado',
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'requiere_receta' => 'boolean',  // Se convierte a verdadero/falso
        'precio_compra'   => 'decimal:2', // Se muestra con 2 decimales
        'precio_venta'    => 'decimal:2', // Se muestra con 2 decimales
    ];

    // Devuelve solo los productos que están marcados como activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    // Devuelve productos activos cuyo stock actual es igual o menor al stock mínimo permitido
    public function scopeStockCritico($query)
    {
        return $query->whereColumn('stock_total', '<=', 'stock_minimo')
                     ->where('estado', 'activo');
    }

    // Productos con al menos un lote que vence en <= 30 días
    // Devuelve productos activos que tienen lotes con existencias próximos a vencer
    public function scopeConLotesProximosAVencer($query, int $dias = 30)
    {
        return $query->whereHas('lotes', function ($q) use ($dias) {
            // Solo considera lotes con cantidad disponible y dentro del rango de fechas indicado
            $q->where('cantidad', '>', 0)
              ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias))
              ->whereDate('fecha_vencimiento', '>=', now());
        })->where('estado', 'activo');
    }

    // Relaciones: conexiones de este modelo con otras tablas del sistema
    // Relación con el proveedor que suministra este producto
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    // Relación con la categoría a la que pertenece el producto
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    // Todos los lotes asociados al producto, ordenados por fecha de vencimiento (el más próximo primero)
    public function lotes()
    {
        return $this->hasMany(Lote::class, 'producto_id')
                    ->orderBy('fecha_vencimiento'); // orden FEFO por defecto
    }

    // Lotes con stock disponible ordenados por fecha de vencimiento (FEFO)
    public function lotesFEFO()
    {
        return $this->hasMany(Lote::class, 'producto_id')
                    ->where('cantidad', '>', 0) // Solo lotes con existencias
                    ->orderBy('fecha_vencimiento');
    }

    // Historial de movimientos de inventario (entradas y salidas) de este producto
    public function kardex()
    {
        return $this->hasMany(KardexProducto::class, 'producto_id');
    }

    // Líneas de venta en las que ha aparecido este producto
    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    /**
     * Recalcula y actualiza stock_total sumando cantidades de todos los lotes.
     * Llama a este método siempre que se modifiquen los lotes.
     */
    // Suma las cantidades de todos los lotes del producto y actualiza el campo stock_total
    public function recalcularStock(): void
    {
        $this->update([
            'stock_total' => $this->lotes()->sum('cantidad'),
        ]);
    }
}