<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table      = 'detalle_venta';
    public    $timestamps = false;

    protected $fillable = [
        'venta_id', 'producto_id', 'receta_id',
        'cantidad', 'precio_unitario', 'descuento_manual',
    ];

    protected $casts = [
        'precio_unitario'  => 'decimal:2',
        'descuento_manual' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    /**
     * Subtotal considerando descuento manual (porcentaje).
     */
    public function getSubtotalAttribute(): float
    {
        $descuento = $this->descuento_manual / 100;
        return $this->cantidad * $this->precio_unitario * (1 - $descuento);
    }
}
