<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa una línea de detalle dentro de una venta
// Cada registro es un producto vendido con su cantidad, precio y descuento aplicado
class DetalleVenta extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'detalle_venta';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'venta_id', 'producto_id',
        'receta_id',          
        'cantidad',           
        'precio_unitario',    
        'descuento_manual',   
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'precio_unitario'  => 'decimal:2', 
        'descuento_manual' => 'decimal:2', 
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Venta a la que pertenece esta línea de detalle
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    // Producto que fue vendido en esta línea
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Receta asociada a este producto, si aplica (puede ser nula si no requiere receta)
    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    /**
     * Subtotal considerando descuento manual (porcentaje).
     */
    // Calcula el importe final de esta línea aplicando el descuento sobre el precio total
    // Fórmula: cantidad × precio_unitario × (1 - descuento/100)
    public function getSubtotalAttribute(): float
    {
        $descuento = $this->descuento_manual / 100; 
        return $this->cantidad * $this->precio_unitario * (1 - $descuento);
    }
}