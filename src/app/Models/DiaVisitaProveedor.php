<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa un día de visita registrado para un proveedor
// Permite saber qué días de la semana visita o hace entregas cada proveedor a la farmacia
class DiaVisitaProveedor extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'dia_visita_proveedor';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'proveedor_id',
        'dia_semana', // Día de la semana en que el proveedor visita la farmacia (lunes, martes, etc.)
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Proveedor al que pertenece este día de visita
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}