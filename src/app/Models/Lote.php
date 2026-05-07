<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa un lote de un producto (una partida específica con fecha de vencimiento)
// Cada producto puede tener varios lotes, lo que permite rastrear existencias por fecha de caducidad
class Lote extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'lote';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'producto_id', 'numero_lote', 
        'cantidad',                   
        'fecha_vencimiento',          
        'fecha_ingreso',              
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'fecha_vencimiento' => 'date',     
        'fecha_ingreso'     => 'datetime', 
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Producto al que pertenece este lote
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Scopes: filtros reutilizables que se pueden encadenar en las consultas

    // Devuelve solo los lotes que aún tienen unidades disponibles
    public function scopeConStock($query)
    {
        return $query->where('cantidad', '>', 0);
    }

    // Devuelve lotes con stock disponible que vencerán dentro del número de días indicado
    // Por defecto revisa los próximos 30 días, útil para alertas de caducidad cercana
    public function scopeProximosAVencer($query, int $dias = 30)
    {
        return $query->conStock() // Solo considera lotes que aún tienen existencias
                     ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias)) // Que vencen antes de la fecha límite
                     ->whereDate('fecha_vencimiento', '>=', now());                // Pero que aún no han vencido
    }
}