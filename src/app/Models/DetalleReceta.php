<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa un medicamento específico dentro de una receta médica
// Cada registro es un renglón de la receta con las indicaciones para un medicamento en particular
class DetalleReceta extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'detalle_receta';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'receta_id', 'producto_id',
        'nombre_medicamento',       
        'dosis',                   
        'frecuencia',              
        'duracion',                
        'indicaciones_especificas', 
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Receta médica a la que pertenece este medicamento
    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    // Producto del catálogo que corresponde a este medicamento 
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}