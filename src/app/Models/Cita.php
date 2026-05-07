<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa una cita médica agendada en el sistema
// Puede estar vinculada a un paciente registrado o solo guardar un nombre temporal
class Cita extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'cita';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'medico_id', 'expediente_id',
        'fecha', 'hora',          
        'motivo',                 
        'nombre_temporal',        
        'estado',                 
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'fecha' => 'date', // Se convierte a un objeto de fecha (sin hora)
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Médico asignado para atender esta cita
    public function medico()
    {
        return $this->belongsTo(Usuario::class, 'medico_id');
    }

    // Expediente clínico del paciente agendado 
    public function expediente()
    {
        return $this->belongsTo(ExpedienteClinico::class, 'expediente_id');
    }

    // Consulta que se generó a partir de esta cita (solo existe si la cita ya fue atendida)
    public function consulta()
    {
        return $this->hasOne(Consulta::class, 'cita_id');
    }
}