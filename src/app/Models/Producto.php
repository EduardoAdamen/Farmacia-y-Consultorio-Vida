<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table      = 'producto';
    public    $timestamps = false;

    protected $fillable = [
        'proveedor_id', 'categoria_id', 'nombre',
        'sku', 'codigo_barras',
        'precio_compra', 'precio_venta',
        'stock_total',
        'stock_minimo', 'requiere_receta', 'estado',
    ];

    protected $casts = [
        'requiere_receta' => 'boolean',
        'precio_compra'   => 'decimal:2',
        'precio_venta'    => 'decimal:2',
    ];

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeStockCritico($query)
    {
        return $query->whereColumn('stock_total', '<=', 'stock_minimo')
                     ->where('estado', 'activo');
    }

    // Productos con al menos un lote que vence en <= 30 días
    public function scopeConLotesProximosAVencer($query, int $dias = 30)
    {
        return $query->whereHas('lotes', function ($q) use ($dias) {
            $q->where('cantidad', '>', 0)
              ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias))
              ->whereDate('fecha_vencimiento', '>=', now());
        })->where('estado', 'activo');
    }

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'producto_id')
                    ->orderBy('fecha_vencimiento'); // orden FEFO por defecto
    }

    public function lotesFEFO()
    {
        // Lotes con stock disponible ordenados por fecha de vencimiento (FEFO)
        return $this->hasMany(Lote::class, 'producto_id')
                    ->where('cantidad', '>', 0)
                    ->orderBy('fecha_vencimiento');
    }

    public function kardex()
    {
        return $this->hasMany(KardexProducto::class, 'producto_id');
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    /**
     * Recalcula y actualiza stock_total sumando cantidades de todos los lotes.
     * Llama a este método siempre que se modifiquen los lotes.
     */
    public function recalcularStock(): void
    {
        $this->update([
            'stock_total' => $this->lotes()->sum('cantidad'),
        ]);
    }
}
