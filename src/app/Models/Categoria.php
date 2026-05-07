<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa una categoría para clasificar los productos del catálogo
// Permite agrupar productos por tipo (ej: antibióticos, vitaminas, material de curación, etc.)
class Categoria extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'categoria';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'nombre',      
        'descripcion', 
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Productos que pertenecen a esta categoría
    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}