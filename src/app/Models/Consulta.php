<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa una consulta médica realizada a un paciente
// Concentra tanto los signos vitales del momento como el diagnóstico y tratamiento indicado
class Consulta extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'consulta';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'expediente_id', 'medico_id', 'cita_id',
        'motivo',              // Razón por la que el paciente acude a consulta
        'presion_arterial',    // Medición de la presión arterial (ej: 120/80)
        'temperatura',         // Temperatura corporal en grados
        'frecuencia_cardiaca', // Latidos por minuto
        'peso',                // Peso del paciente en kilogramos
        'talla',               // Estatura del paciente en metros
        'sintomas',            // Descripción de los síntomas reportados por el paciente
        'diagnostico',         // Conclusión médica tras la evaluación
        'estudios_solicitados', // Análisis o exámenes que el médico solicita (laboratorio, rayos X, etc.)
        'tratamiento',         // Indicaciones terapéuticas o medicamentos recetados
        'notas_evolucion',     // Observaciones del médico sobre el progreso del paciente
        'tipo_consulta',       // Clasifica la consulta (primera vez, seguimiento, urgencia, etc.)
        'costo', 'estado_pago', // Monto cobrado por la consulta y si ya fue pagado o no
        'proxima_cita',        // Fecha sugerida para la siguiente consulta
        'fecha_hora',          // Fecha y hora en que se realizó la consulta
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'fecha_hora'   => 'datetime',  // Se convierte a un objeto de fecha y hora
        'proxima_cita' => 'date',      // Se convierte a un objeto de fecha (sin hora)
        'temperatura'  => 'decimal:1', // Se muestra con 1 decimal (ej: 36.5)
        'peso'         => 'decimal:2', // Se muestra con 2 decimales (ej: 72.50)
        'talla'        => 'decimal:2', // Se muestra con 2 decimales (ej: 1.75)
        'costo'        => 'decimal:2', // Se muestra con 2 decimales
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Expediente clínico del paciente al que pertenece esta consulta
    public function expediente()
    {
        return $this->belongsTo(ExpedienteClinico::class, 'expediente_id');
    }

    // Médico que atendió la consulta
    public function medico()
    {
        return $this->belongsTo(Usuario::class, 'medico_id');
    }

    // Cita previa que dio origen a esta consulta (puede ser nula si fue consulta de urgencia)
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    // Recetas médicas generadas durante esta consulta
    public function recetas()
    {
        return $this->hasMany(Receta::class, 'consulta_id');
    }
}