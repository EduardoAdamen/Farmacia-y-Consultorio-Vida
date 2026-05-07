<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa un producto específico dentro de un pedido de compra
// Cada registro es un renglón del pedido con las cantidades solicitadas y recibidas
class DetallePedido extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'detalle_pedido';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'pedido_id', 'producto_id',
        'cantidad_solicitada', 
        'cantidad_recibida',  
        'precio_compra_real',  
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'precio_compra_real' => 'decimal:2', // Se muestra con 2 decimales
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Pedido de compra al que pertenece esta línea de detalle
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // Producto del catálogo que se está solicitando en esta línea
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}