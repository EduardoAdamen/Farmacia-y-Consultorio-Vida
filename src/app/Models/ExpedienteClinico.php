<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa el expediente clínico de un paciente
// Concentra toda la información médica y de contacto relevante para sus consultas
class ExpedienteClinico extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'expediente_clinico';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'nombre_completo', 'fecha_nacimiento', 'sexo', 'tipo_sangre',
        'alergias',              
        'enfermedades_cronicas', 
        'medicamentos_actuales', 
        'antecedentes_familiares', 
        'telefono', 'correo',
        'estado',
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'fecha_nacimiento' => 'date', // Se convierte a un objeto de fecha (sin hora)
    ];

    // Scopes: filtros reutilizables que se pueden encadenar en las consultas

    // Devuelve solo los expedientes que están marcados como activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Citas médicas que tiene o ha tenido este paciente
    public function citas()
    {
        return $this->hasMany(Cita::class, 'expediente_id');
    }

    // Consultas médicas que se han realizado a este paciente
    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'expediente_id');
    }

    // Calcula la edad en años completos a partir de la fecha de nacimiento.
    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }
}