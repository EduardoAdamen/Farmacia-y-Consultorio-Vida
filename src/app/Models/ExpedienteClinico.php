<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedienteClinico extends Model
{
    protected $table      = 'expediente_clinico';
    public    $timestamps = false;

    protected $fillable = [
        'nombre_completo', 'fecha_nacimiento', 'sexo', 'tipo_sangre',
        'alergias', 'enfermedades_cronicas', 'medicamentos_actuales',
        'antecedentes_familiares',
        'telefono', 'correo',
        'estado',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'expediente_id');
    }

    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'expediente_id');
    }

    /**
     * Calcula la edad en años completos a partir de la fecha de nacimiento.
     */
    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }
}
