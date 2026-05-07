<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa el kardex de inventario, es decir, el historial completo de
// entradas y salidas de cada producto (compras, ventas, ajustes, etc.)
class KardexProducto extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'kardex_producto';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'producto_id', 'usuario_id',
        'tipo',         
        'cantidad',     
        'referencia_id', 
        'fecha_hora',
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'fecha_hora' => 'datetime', 
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Producto al que corresponde este movimiento de inventario
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Usuario que registró o generó este movimiento en el sistema
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}